#!/bin/sh
# Sets up small helpers on the server itself, once. Run by the updater (inside the host's
# namespaces, see installer/updater/updater.sh) after every update, so installations that were
# set up before a helper existed get it through the normal auto-update.
#
#   - Hourly RAM cache clear: "sync" first (pending writes reach the disk), then drop the page cache.
#
# It never touches anything that already exists: if the timer is there (even switched off by hand
# with "systemctl disable --now recoded-ptero-dropcache.timer"), it stays as it is. Opt out for good
# with the file <install dir>/state/no-dropcache (the installer creates it for MC_DROP_CACHES=0).
#
# HOST_EXTRAS_ROOT is only for tests: it prefixes /etc and skips systemctl and the kernel check.

ROOT="${HOST_EXTRAS_ROOT:-}"
INSTALL_DIR="${INSTALL_DIR:-/opt/recoded-ptero}"
UNIT_DIR="$ROOT/etc/systemd/system"
CRON_FILE="$ROOT/etc/cron.d/recoded-ptero-dropcache"

setup_cache_drop() {
    [ -e "$INSTALL_DIR/state/no-dropcache" ] && return 0
    [ -e "$UNIT_DIR/recoded-ptero-dropcache.timer" ] && return 0
    [ -e "$CRON_FILE" ] && return 0
    [ -n "$ROOT" ] || [ -w /proc/sys/vm/drop_caches ] || return 0

    if { [ -n "$ROOT" ] || command -v systemctl >/dev/null 2>&1; } && [ -d "$UNIT_DIR" ]; then
        cat > "$UNIT_DIR/recoded-ptero-dropcache.service" <<'EOF'
[Unit]
Description=Recoded Ptero: empty the RAM cache

[Service]
Type=oneshot
ExecStart=/bin/sh -c 'sync && echo 1 > /proc/sys/vm/drop_caches'
EOF
        cat > "$UNIT_DIR/recoded-ptero-dropcache.timer" <<'EOF'
[Unit]
Description=Recoded Ptero: empty the RAM cache every hour

[Timer]
OnCalendar=hourly
RandomizedDelaySec=120

[Install]
WantedBy=timers.target
EOF
        if [ -z "$ROOT" ]; then
            systemctl daemon-reload >/dev/null 2>&1
            systemctl enable --now recoded-ptero-dropcache.timer >/dev/null 2>&1 || return 0
        fi
    elif [ -d "$ROOT/etc/cron.d" ]; then
        echo "0 * * * * root sync && echo 1 > /proc/sys/vm/drop_caches" > "$CRON_FILE"
    else
        return 0
    fi
    echo "Set up the hourly RAM cache clear."
}

setup_cache_drop
