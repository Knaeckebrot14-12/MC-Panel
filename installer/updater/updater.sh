#!/usr/bin/env bash
# Recoded Ptero updater.
#
#   updater.sh daemon              watch state/request.json (used by the "updater" container)
#   updater.sh run [id] [auto] [force]   apply an update right now (used by `recoded-ptero update`)
#
# An update = fetch the repository, back up the database, build the new image next to the
# running one, swap the container, verify it answers, and roll back if it does not.
# Progress is written to state/status.json and state/update.log for the panel to display.

set -uo pipefail

SELF="$(readlink -f "${BASH_SOURCE[0]}")"
INSTALL_DIR="${INSTALL_DIR:-$(cd "$(dirname "$SELF")/../.." && pwd)}"
STATE_DIR="${STATE_DIR:-$INSTALL_DIR/state}"
BACKUP_DIR="$INSTALL_DIR/backups"
BRANCH="${MC_PANEL_BRANCH:-main}"
COMPOSE_FILE="$INSTALL_DIR/${MC_COMPOSE_FILE:-docker-compose.prod.yml}"
# Image name of the panel service (the "image:" entry in the compose file).
IMAGE="${MC_PANEL_IMAGE:-recodedptero-panel}"
LOCK="$INSTALL_DIR/.update.lock"
STATUS_FILE="$STATE_DIR/status.json"
LOG_FILE="$STATE_DIR/update.log"
KEEP_BACKUPS=7
HEALTH_TIMEOUT="${HEALTH_TIMEOUT:-300}"

RUN_ID=""
RUN_AUTO="false"
RUN_FROM=""
RUN_TO=""
RUN_STARTED=""

dc() {
    docker compose -f "$COMPOSE_FILE" --project-directory "$INSTALL_DIR" "$@"
}

now() { date -u +%Y-%m-%dT%H:%M:%SZ; }

# Run from the server (recoded-ptero update): one coloured line per step, like the installer.
# Inside the updater container the plain log lines go to "docker logs" instead.
HOST_MODE=1
[ -f /.dockerenv ] && HOST_MODE=0
if [ -t 1 ]; then
    C_RESET=$'\033[0m'; C_RED=$'\033[31m'; C_GREEN=$'\033[32m'; C_BLUE=$'\033[36m'
else
    C_RESET=""; C_RED=""; C_GREEN=""; C_BLUE=""
fi
UI_STEP=""

step_label() {
    case "$1:$2" in
        fetch:run) echo "Downloading the new version" ;;       fetch:done) echo "New version downloaded" ;;
        backup:run) echo "Backing up the database" ;;          backup:done) echo "Database backed up" ;;
        apply:run) echo "Applying the new files" ;;            apply:done) echo "New files applied" ;;
        build:run) echo "Building the new version (a few minutes)" ;; build:done) echo "New version built" ;;
        swap:run) echo "Restarting the panel" ;;               swap:done) echo "Panel restarted" ;;
        health:run) echo "Checking the new version" ;;         health:done) echo "New version is running" ;;
        cleanup:run) echo "Cleaning up" ;;                     cleanup:done) echo "Cleaned up" ;;
        rollback:run) echo "Restoring the previous version" ;; rollback:done) echo "Previous version restored" ;;
        *) echo "$1" ;;
    esac
}

# Prints the step lines on the server screen; called from write_status.
ui_status() {
    local state="$1" step="$2" message="$3"
    [ "$HOST_MODE" = "1" ] || return 0
    if [ "$state" = "running" ] && [ "$step" != "$UI_STEP" ]; then
        [ -n "$UI_STEP" ] && printf '%s ✔%s %s\n' "$C_GREEN" "$C_RESET" "$(step_label "$UI_STEP" done)"
        printf '%s==>%s %s\n' "$C_BLUE" "$C_RESET" "$(step_label "$step" run)"
        UI_STEP="$step"
    elif [ "$state" = "success" ]; then
        [ -n "$UI_STEP" ] && printf '%s ✔%s %s\n' "$C_GREEN" "$C_RESET" "$(step_label "$UI_STEP" done)"
        printf '%s ✔%s %s\n' "$C_GREEN" "$C_RESET" "${message:-Update finished: now running ${RUN_TO:0:7}}"
        UI_STEP=""
    elif [ "$state" = "failed" ] || [ "$state" = "rolled_back" ]; then
        printf '%s ✘ %s%s\n' "$C_RED" "$message" "$C_RESET"
        printf '   Details: %s\n' "$LOG_FILE"
        UI_STEP=""
    fi
}

log() {
    mkdir -p "$STATE_DIR"
    printf '[%s] %s\n' "$(date -u +%H:%M:%S)" "$*" >> "$LOG_FILE"
    [ "$HOST_MODE" = "1" ] || printf '[%s] %s\n' "$(date -u +%H:%M:%S)" "$*"
}

jesc() {
    printf '%s' "$1" | tr '\n\r\t' '   ' | sed -e 's/\\/\\\\/g' -e 's/"/\\"/g'
}

# write_status <state> <step> [message] [finished]
write_status() {
    local state="$1" step="$2" message="${3:-}" finished="${4:-}"
    mkdir -p "$STATE_DIR"
    printf '{"id":"%s","state":"%s","step":"%s","message":"%s","from":"%s","to":"%s","auto":%s,"started_at":"%s","finished_at":%s}\n' \
        "$(jesc "$RUN_ID")" "$state" "$step" "$(jesc "$message")" "${RUN_FROM:0:7}" "${RUN_TO:0:7}" "$RUN_AUTO" \
        "$RUN_STARTED" "$([ -n "$finished" ] && printf '"%s"' "$finished" || printf 'null')" \
        > "$STATUS_FILE.tmp" && mv -f "$STATUS_FILE.tmp" "$STATUS_FILE"
    chmod 644 "$STATUS_FILE" 2>/dev/null || true
    ui_status "$state" "$step" "$message"
}

acquire_lock() {
    if [ -f "$LOCK" ]; then
        local pid age
        pid="$(cat "$LOCK" 2>/dev/null || true)"
        age=$(( $(date +%s) - $(stat -c %Y "$LOCK" 2>/dev/null || echo 0) ))
        # A lock is stale when its process is gone or it is older than two hours.
        if [ -n "$pid" ] && kill -0 "$pid" 2>/dev/null && [ "$age" -lt 7200 ]; then
            return 1
        fi
    fi
    echo $$ > "$LOCK"
}

release_lock() { rm -f "$LOCK"; }

installed_commit() {
    dc exec -T panel cat /app/.mc-commit 2>/dev/null | tr -d '[:space:]' || true
}

backup_data() {
    mkdir -p "$BACKUP_DIR"
    local ts db var
    ts="$(date -u +%Y%m%d-%H%M%S)"
    db="$BACKUP_DIR/db-$ts.sql.gz"
    var="$BACKUP_DIR/panel-var-$ts.tar.gz"

    if dc exec -T database sh -c 'mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines panel' 2>>"$LOG_FILE" | gzip > "$db" && [ "$(stat -c %s "$db")" -gt 200 ]; then
        log "Database backup saved: $(basename "$db")"
    else
        rm -f "$db"
        log "WARNING: the database backup failed, continuing without it."
    fi

    # panel_var holds the APP_KEY, which is needed to decrypt stored data.
    if dc exec -T panel tar czf - -C /app/var . 2>>"$LOG_FILE" > "$var" && [ -s "$var" ]; then
        log "Panel settings backup saved: $(basename "$var")"
    else
        rm -f "$var"
    fi

    # shellcheck disable=SC2012
    ls -1t "$BACKUP_DIR"/db-*.sql.gz 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f
    # shellcheck disable=SC2012
    ls -1t "$BACKUP_DIR"/panel-var-*.tar.gz 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f
}

# Waits until the freshly started panel container runs the expected commit and serves pages.
wait_healthy() {
    local expected="$1" deadline ok=0 cid code commit
    deadline=$(( $(date +%s) + HEALTH_TIMEOUT ))
    while [ "$(date +%s)" -lt "$deadline" ]; do
        cid="$(dc ps -q panel 2>/dev/null | head -n1)"
        if [ -n "$cid" ] && [ "$(docker inspect -f '{{.State.Running}}' "$cid" 2>/dev/null)" = "true" ]; then
            code="$(docker exec "$cid" curl -s -o /dev/null -w '%{http_code}' --max-time 8 http://127.0.0.1/ 2>/dev/null || true)"
            commit="$(docker exec "$cid" cat /app/.mc-commit 2>/dev/null | tr -d '[:space:]' || true)"
            if [ -n "$code" ] && [ "$code" != "000" ] && [ "$code" -lt 500 ] 2>/dev/null && [ "$commit" = "$expected" ]; then
                ok=$((ok + 1))
                [ "$ok" -ge 2 ] && return 0
            else
                ok=0
            fi
        else
            ok=0
        fi
        sleep 4
    done
    return 1
}

rollback() {
    local old_sha="$1"
    write_status running rollback
    log "Rolling back to ${old_sha:0:7}..."
    git -C "$INSTALL_DIR" reset --hard "$old_sha" >>"$LOG_FILE" 2>&1
    if docker image inspect "$IMAGE:rollback" >/dev/null 2>&1; then
        docker tag "$IMAGE:rollback" "$IMAGE:latest"
        dc up -d --no-deps --no-build panel >>"$LOG_FILE" 2>&1
        if wait_healthy "$old_sha"; then
            log "The previous version is running again."
            return 0
        fi
    fi
    log "WARNING: could not confirm the previous version is running. Check: docker compose logs panel"
    return 1
}

do_update() {
    RUN_ID="${1:-manual-$(date +%s)}"
    RUN_AUTO="${2:-false}"
    local force="${3:-0}"
    RUN_STARTED="$(now)"
    : > "$LOG_FILE"

    if ! acquire_lock; then
        RUN_FROM=""; RUN_TO=""
        write_status failed fetch "Another update is already running." "$(now)"
        log "Another update is already running."
        return 1
    fi
    trap release_lock EXIT

    cd "$INSTALL_DIR" || return 1
    # Only content counts as a local change: making the scripts executable (chmod +x) must not
    # block updates. Older installs lacked this setting.
    git config core.fileMode false
    local old_sha new_sha current
    old_sha="$(git rev-parse HEAD)"
    RUN_FROM="$old_sha"

    write_status running fetch
    log "Checking for the newest version on origin/$BRANCH..."
    if ! git fetch --quiet origin "$BRANCH" >>"$LOG_FILE" 2>&1; then
        write_status failed fetch "Could not download the new version from GitHub." "$(now)"
        log "ERROR: git fetch failed."
        return 1
    fi
    new_sha="$(git rev-parse "origin/$BRANCH")"
    RUN_TO="$new_sha"

    current="$(installed_commit)"
    if [ "$current" = "$new_sha" ] && [ "$force" != "1" ]; then
        RUN_FROM="$new_sha"
        write_status success done "Already up to date." "$(now)"
        log "Already up to date (${new_sha:0:7})."
        return 0
    fi
    [ -n "$current" ] && RUN_FROM="$current"

    # Never throw away work: refuse when files were edited by hand or commits exist that GitHub doesn't have.
    if [ -n "$(git -c core.fileMode=false status --porcelain --untracked-files=no 2>/dev/null)" ]; then
        write_status failed fetch "The install directory has local changes. Commit or discard them first." "$(now)"
        log "ERROR: local changes in $INSTALL_DIR:"
        git status --short --untracked-files=no | head -n 20 >> "$LOG_FILE"
        return 1
    fi
    if ! git merge-base --is-ancestor HEAD "origin/$BRANCH" 2>/dev/null; then
        write_status failed fetch "The install directory has commits that are not on GitHub. Push them first." "$(now)"
        log "ERROR: HEAD ${old_sha:0:7} is not part of origin/$BRANCH (local commits that were not pushed?)."
        return 1
    fi

    log "Updating ${RUN_FROM:0:7} -> ${new_sha:0:7}"

    write_status running backup
    log "Backing up..."
    backup_data

    write_status running apply
    if git reset --hard "origin/$BRANCH" >>"$LOG_FILE" 2>&1; then
        chmod +x install.sh installer/recoded-ptero installer/updater/updater.sh 2>/dev/null || true
    else
        write_status failed apply "Could not apply the new files." "$(now)"
        log "ERROR: git reset failed."
        git reset --hard "$old_sha" >>"$LOG_FILE" 2>&1
        return 1
    fi

    # Keep the running image around so a failed update can be undone.
    docker tag "$IMAGE:latest" "$IMAGE:rollback" 2>/dev/null || true

    write_status running build
    log "Building the new version. This takes a few minutes and the panel keeps running meanwhile (details: $LOG_FILE)..."
    # The build output only goes to the log file; the screen keeps showing one line per step.
    if ! BUILDKIT_PROGRESS=plain dc build --build-arg "MC_COMMIT=$new_sha" panel >> "$LOG_FILE" 2>&1; then
        log "ERROR: the build failed. Nothing was changed."
        git reset --hard "$old_sha" >>"$LOG_FILE" 2>&1
        write_status failed build "The new version could not be built. Nothing was changed." "$(now)"
        return 1
    fi

    write_status running swap
    log "Restarting the panel..."
    if ! dc up -d --no-deps --remove-orphans database cache panel >>"$LOG_FILE" 2>&1; then
        log "ERROR: could not start the new version."
        if rollback "$old_sha"; then
            write_status rolled_back swap "The new version could not be started." "$(now)"
        else
            write_status failed swap "The new version could not be started and the rollback needs attention." "$(now)"
        fi
        return 1
    fi

    write_status running health
    log "Waiting for the new version to come up (database migrations run now)..."
    if ! wait_healthy "$new_sha"; then
        log "ERROR: the new version did not become healthy in ${HEALTH_TIMEOUT}s."
        dc logs --tail 30 panel >>"$LOG_FILE" 2>&1
        if rollback "$old_sha"; then
            write_status rolled_back health "The new version did not start correctly." "$(now)"
        else
            write_status failed health "The new version did not start and the rollback needs attention." "$(now)"
        fi
        return 1
    fi

    write_status running cleanup
    docker image prune -f >/dev/null 2>&1 || true

    # Run from the host (not from the updater container): also refresh the updater itself.
    if [ ! -f /.dockerenv ]; then
        dc up -d --build updater >>"$LOG_FILE" 2>&1 || true
    fi

    write_status success done "" "$(now)"
    log "Update finished: now running ${new_sha:0:7}."
    return 0
}

# Small helpers that belong on the server itself (installer/host-extras.sh, e.g. the hourly RAM cache
# clear) are set up from here, so existing installations get them through the normal update. The
# updater container can reach the host's namespaces through the Docker socket it already has.
# Only for the production stack; never fails an update.
host_extras() {
    [ -f /.dockerenv ] || return 0
    [ "$(basename "$COMPOSE_FILE")" = "docker-compose.prod.yml" ] || return 0
    [ -f "$INSTALL_DIR/installer/host-extras.sh" ] || return 0

    local image
    image="$(docker inspect --format '{{.Image}}' "$(hostname)" 2>/dev/null)" || image="docker:cli"
    local out
    out="$(timeout 90 docker run --rm --privileged --pid=host --network none -e INSTALL_DIR="$INSTALL_DIR" "$image" \
        nsenter -t 1 -m -u -i -n -p -- sh "$INSTALL_DIR/installer/host-extras.sh" 2>&1)" || log "Host extras: $out"
    [ -n "$out" ] && log "Host extras: $out"
    return 0
}

daemon() {
    mkdir -p "$STATE_DIR"

    # An update that was cut off (for example by a restart of this container) can't be resumed.
    if grep -q '"state":"running"' "$STATUS_FILE" 2>/dev/null; then
        RUN_ID="interrupted"; RUN_STARTED="$(now)"
        write_status failed fetch "The update was interrupted." "$(now)"
    fi
    release_lock

    # Right after an update the daemon starts again with the new script, so this also runs then.
    host_extras

    log "Updater ready (watching $STATE_DIR)."
    while true; do
        touch "$STATE_DIR/heartbeat"

        if [ -f "$STATE_DIR/request.json" ]; then
            local id auto
            id="$(sed -n 's/.*"id":"\([^"]*\)".*/\1/p' "$STATE_DIR/request.json" | head -n1)"
            auto="$(sed -nE 's/.*"auto":(true|false).*/\1/p' "$STATE_DIR/request.json" | head -n1)"
            rm -f "$STATE_DIR/request.json"

            # Run the update in a child so the heartbeat keeps beating during the long build.
            bash "$SELF" run "${id:-request}" "${auto:-false}" 0 >/dev/null 2>&1 &
            local pid=$!
            while kill -0 "$pid" 2>/dev/null; do
                touch "$STATE_DIR/heartbeat"
                sleep 3
            done
            wait "$pid" 2>/dev/null

            # The update may have replaced this very script; start over with the new one.
            exec bash "$SELF" daemon
        fi

        sleep 3
    done
}

main() {
    case "${1:-}" in
        daemon) daemon ;;
        run)
            shift
            do_update "$@"
            ;;
        *)
            echo "Usage: $0 daemon | run [id] [auto] [force]" >&2
            return 2
            ;;
    esac
}

# Everything above is defined before this single line runs, so replacing this file during
# an update (git reset) can't confuse the shell that is still executing it.
main "$@"; exit $?
