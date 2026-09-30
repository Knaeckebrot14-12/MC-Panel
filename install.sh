#!/usr/bin/env bash
# Recoded Ptero installer.
#
#   bash <(curl -sSL https://raw.githubusercontent.com/Knaeckebrot14-12/Recoded-Ptero/main/install.sh)
#
# Installs the panel (Docker based, with one-click and automatic updates) and/or Wings, the
# daemon that runs the game servers. Run it as root on a fresh Debian/Ubuntu/RHEL-family server.
#
# Unattended use: set MC_NONINTERACTIVE=1 plus the MC_* variables used by the questions below,
# for example MC_ACTION=panel MC_MODE=1 MC_HOST=1.2.3.4 MC_ADMIN_EMAIL=me@example.com.

set -uo pipefail

GITHUB_REPO="${MC_PANEL_REPO:-Knaeckebrot14-12/Recoded-Ptero}"
GITHUB_BRANCH="${MC_PANEL_BRANCH:-main}"
INSTALL_DIR="${INSTALL_DIR:-/opt/recoded-ptero}"
COMPOSE_FILE="$INSTALL_DIR/docker-compose.prod.yml"

if [ -t 1 ]; then
    C_RESET=$'\033[0m'; C_BOLD=$'\033[1m'; C_RED=$'\033[31m'; C_GREEN=$'\033[32m'; C_YELLOW=$'\033[33m'; C_BLUE=$'\033[36m'
else
    C_RESET=""; C_BOLD=""; C_RED=""; C_GREEN=""; C_YELLOW=""; C_BLUE=""
fi

info() { printf '%s==>%s %s\n' "$C_BLUE" "$C_RESET" "$*"; }
ok() { printf '%s ✔%s %s\n' "$C_GREEN" "$C_RESET" "$*"; }
warn() { printf '%s !%s %s\n' "$C_YELLOW" "$C_RESET" "$*"; }
die() { printf '%s ✘ %s%s\n' "$C_RED" "$*" "$C_RESET" >&2; exit 1; }

# ---------------------------------------------------------------- prompts

# ask VAR "Question" [default]  — the answer ends up in $VAR. MC_<VAR> in the environment answers it silently.
ask() {
    local var="$1" question="$2" default="${3:-}" preset="MC_$1" answer=""
    if [ -n "${!preset:-}" ]; then printf -v "$var" '%s' "${!preset}"; return; fi
    if [ "${MC_NONINTERACTIVE:-0}" = "1" ]; then printf -v "$var" '%s' "$default"; return; fi
    if [ -n "$default" ]; then
        read -r -p "$question [$default]: " answer </dev/tty
    else
        read -r -p "$question: " answer </dev/tty
    fi
    printf -v "$var" '%s' "${answer:-$default}"
}

# ask_secret VAR "Question"  — like ask, but without echo and without a default.
ask_secret() {
    local var="$1" question="$2" preset="MC_$1" answer=""
    if [ -n "${!preset:-}" ]; then printf -v "$var" '%s' "${!preset}"; return; fi
    if [ "${MC_NONINTERACTIVE:-0}" = "1" ]; then printf -v "$var" '%s' ""; return; fi
    read -r -s -p "$question: " answer </dev/tty
    echo
    printf -v "$var" '%s' "$answer"
}

# confirm "Question" y|n  — returns success for yes. Non-interactive runs take the default.
confirm() {
    local question="$1" default="${2:-n}" answer="" hint="y/N"
    [ "$default" = "y" ] && hint="Y/n"
    if [ "${MC_NONINTERACTIVE:-0}" = "1" ]; then [ "$default" = "y" ]; return; fi
    read -r -p "$question [$hint]: " answer </dev/tty
    answer="${answer:-$default}"
    [[ "$answer" =~ ^[Yy] ]]
}

random_string() {
    local length="${1:-32}"
    openssl rand -base64 96 | tr -dc 'A-Za-z0-9' | head -c "$length"
}

# ---------------------------------------------------------------- system checks

require_root() {
    [ "$(id -u)" -eq 0 ] || die "Please run this installer as root (for example: sudo bash <(curl -sSL ...))."
}

detect_system() {
    [ "$(uname -s)" = "Linux" ] || die "This installer only supports Linux."
    case "$(uname -m)" in
        x86_64|amd64) ARCH="amd64" ;;
        aarch64|arm64) ARCH="arm64" ;;
        *) die "Unsupported CPU architecture: $(uname -m)" ;;
    esac

    if command -v apt-get >/dev/null 2>&1; then PKG="apt"
    elif command -v dnf >/dev/null 2>&1; then PKG="dnf"
    elif command -v yum >/dev/null 2>&1; then PKG="yum"
    else die "Unsupported distribution: no apt, dnf or yum found. Debian, Ubuntu, Rocky, Alma and CentOS are supported."
    fi
    command -v systemctl >/dev/null 2>&1 || warn "systemd was not found, some steps (starting Docker/Wings on boot) may not work."
}

install_packages() {
    info "Installing required packages..."
    case "$PKG" in
        apt)
            export DEBIAN_FRONTEND=noninteractive
            apt-get update -y >/dev/null && apt-get install -y curl git ca-certificates openssl tar >/dev/null || die "Could not install packages."
            ;;
        dnf) dnf install -y curl git ca-certificates openssl tar >/dev/null || die "Could not install packages." ;;
        yum) yum install -y curl git ca-certificates openssl tar >/dev/null || die "Could not install packages." ;;
    esac
    ok "Packages ready"
}

install_docker() {
    if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
        ok "Docker $(docker --version | awk '{print $3}' | tr -d ,) is already installed"
    else
        info "Installing Docker (this uses the official get.docker.com script)..."
        curl -fsSL https://get.docker.com | sh >/dev/null 2>&1 || die "Docker installation failed. Install Docker manually and run this installer again."
        docker compose version >/dev/null 2>&1 || die "Docker was installed but the 'docker compose' plugin is missing."
        ok "Docker installed"
    fi
    if command -v systemctl >/dev/null 2>&1; then
        systemctl enable --now docker >/dev/null 2>&1 || true
    fi
    docker info >/dev/null 2>&1 || die "The Docker daemon is not running."
}

# Building the panel needs a few GB of memory; offer swap on small servers.
ensure_swap() {
    [ -f /.dockerenv ] && return 0
    local mem_kb swap_kb
    mem_kb="$(awk '/MemTotal/ {print $2}' /proc/meminfo)"
    swap_kb="$(awk '/SwapTotal/ {print $2}' /proc/meminfo)"
    if [ "$mem_kb" -lt 3500000 ] && [ "$swap_kb" -lt 2000000 ]; then
        warn "This server has less than 4 GB of memory. Building the panel needs a lot, so a swap file is recommended."
        if confirm "Create a 4 GB swap file at /swapfile?" y; then
            if [ ! -f /swapfile ]; then
                (fallocate -l 4G /swapfile 2>/dev/null || dd if=/dev/zero of=/swapfile bs=1M count=4096 status=none) \
                    && chmod 600 /swapfile && mkswap /swapfile >/dev/null && swapon /swapfile \
                    && { grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab; } \
                    && ok "Swap enabled" || warn "Could not create the swap file, continuing without it."
            fi
        fi
    fi
}

public_ip() {
    curl -4 -fsS --max-time 6 https://api.ipify.org 2>/dev/null \
        || curl -4 -fsS --max-time 6 https://ifconfig.me 2>/dev/null \
        || hostname -I 2>/dev/null | awk '{print $1}'
}

port_in_use() {
    command -v ss >/dev/null 2>&1 || return 1
    ss -ltnH "sport = :$1" 2>/dev/null | grep -q .
}

system_timezone() {
    timedatectl show -p Timezone --value 2>/dev/null || cat /etc/timezone 2>/dev/null || echo UTC
}

# Opens the ports the panel and Wings need (web, daemon, SFTP) when ufw is active.
# Game server ports are never opened automatically.
open_firewall_ports() {
    if command -v ufw >/dev/null 2>&1 && ufw status 2>/dev/null | grep -q "Status: active"; then
        local port
        for port in "$@"; do ufw allow "$port" >/dev/null 2>&1; done
        ok "Opened ports in ufw: $*"
    fi
}

dc() {
    docker compose -f "$COMPOSE_FILE" --project-directory "$INSTALL_DIR" "$@"
}

# ---------------------------------------------------------------- panel

install_panel() {
    if [ -f "$INSTALL_DIR/.env" ]; then
        die "A panel is already installed in $INSTALL_DIR. Use the update option instead (or run: recoded-ptero update)."
    fi
    if [ -e "$INSTALL_DIR" ] && [ -n "$(ls -A "$INSTALL_DIR" 2>/dev/null)" ]; then
        die "$INSTALL_DIR already exists and is not empty. Remove it or set INSTALL_DIR to another location."
    fi

    install_packages
    install_docker
    ensure_swap

    echo
    printf '%s%s%s\n' "$C_BOLD" "How should the panel be reachable?" "$C_RESET"
    echo "  [1] HTTP only, by IP address or domain (no encryption, quickest)"
    echo "  [2] HTTPS with a free Let's Encrypt certificate (needs a domain pointing at this server)"
    echo "  [3] I already run a reverse proxy (Nginx, Caddy, Cloudflare Tunnel...) that handles HTTPS"
    ask MODE "Choose 1, 2 or 3" "1"

    local server_ip domain port
    server_ip="$(public_ip)"
    HTTP_BIND="0.0.0.0"; HTTP_PORT="${MC_HTTP_PORT:-80}"; HTTPS_PORT="${MC_HTTPS_PORT:-443}"; LE_EMAIL=""; TRUSTED_PROXIES=""

    case "$MODE" in
        1)
            ask HOST "Server IP address or domain" "$server_ip"
            [ -n "$HOST" ] || die "A host is required."
            if port_in_use "$HTTP_PORT"; then
                warn "Port 80 is already in use on this server."
                ask HTTP_PORT "Port for the panel" "8085"
            fi
            APP_URL="http://$HOST"
            [ "$HTTP_PORT" != "80" ] && APP_URL="$APP_URL:$HTTP_PORT"
            ;;
        2)
            ask DOMAIN "Domain of the panel (for example panel.example.com)" ""
            [ -n "$DOMAIN" ] || die "A domain is required for HTTPS."
            ask LE_EMAIL "E-mail address for Let's Encrypt (expiry notices)" ""
            [ -n "$LE_EMAIL" ] || die "An e-mail address is required for Let's Encrypt."
            port_in_use 80 && die "Port 80 is in use. Let's Encrypt needs ports 80 and 443; stop the service using them (for example Apache or Nginx) first."
            port_in_use 443 && die "Port 443 is in use. Let's Encrypt needs ports 80 and 443; stop the service using them first."
            domain_ip="$(getent hosts "$DOMAIN" | awk '{print $1; exit}')"
            if [ -z "$domain_ip" ] || { [ -n "$server_ip" ] && [ "$domain_ip" != "$server_ip" ]; }; then
                warn "$DOMAIN resolves to '${domain_ip:-nothing}', but this server's address is '$server_ip'."
                warn "Let's Encrypt will fail until the DNS record points here."
                confirm "Continue anyway?" n || die "Aborted. Fix the DNS record and run the installer again."
            fi
            APP_URL="https://$DOMAIN"
            ;;
        3)
            ask APP_URL "Public URL of the panel (for example https://panel.example.com)" ""
            [[ "$APP_URL" =~ ^https?:// ]] || die "Please enter a full URL starting with http:// or https://"
            ask HTTP_PORT "Local port your reverse proxy should forward to" "8085"
            HTTP_BIND="127.0.0.1"; HTTPS_PORT=8443; TRUSTED_PROXIES="*"
            ;;
        *) die "Please choose 1, 2 or 3." ;;
    esac
    APP_URL="${APP_URL%/}"

    echo
    printf '%s%s%s\n' "$C_BOLD" "Owner account (full access)" "$C_RESET"
    ask ADMIN_EMAIL "E-mail" ""
    [[ "$ADMIN_EMAIL" == *@*.* ]] || die "Please enter a valid e-mail address."
    ask ADMIN_USER "Username" "admin"
    ask ADMIN_FIRST "First name" "Admin"
    ask ADMIN_LAST "Last name" "User"
    ask_secret ADMIN_PASS "Password (leave empty to generate one)"
    GENERATED_PASS=0
    if [ -z "$ADMIN_PASS" ]; then ADMIN_PASS="$(random_string 20)"; GENERATED_PASS=1; fi
    [ "${#ADMIN_PASS}" -ge 8 ] || die "The password needs at least 8 characters."

    echo
    AUTO_UPDATE=0
    confirm "Update the panel automatically whenever a new version is published?" n && AUTO_UPDATE=1

    info "Downloading the panel from github.com/$GITHUB_REPO ..."
    git clone --quiet --branch "$GITHUB_BRANCH" "https://github.com/$GITHUB_REPO.git" "$INSTALL_DIR" \
        || die "Could not download the repository. Check the internet connection and that github.com/$GITHUB_REPO exists."
    local commit
    commit="$(git -C "$INSTALL_DIR" rev-parse HEAD)"
    ok "Downloaded version $(cat "$INSTALL_DIR/VERSION" 2>/dev/null) (${commit:0:7})"

    info "Writing configuration..."
    (
        umask 077
        cat > "$INSTALL_DIR/.env" <<EOF
# Written by install.sh. Contains secrets, keep it private. Changes apply after: recoded-ptero restart
INSTALL_DIR=$INSTALL_DIR
COMPOSE_PROJECT_NAME=recodedptero
MC_PANEL_REPO=$GITHUB_REPO
MC_PANEL_BRANCH=$GITHUB_BRANCH
APP_URL=$APP_URL
APP_TIMEZONE=$(system_timezone)
APP_SERVICE_AUTHOR=$ADMIN_EMAIL
DB_PASSWORD=$(random_string 32)
DB_ROOT_PASSWORD=$(random_string 32)
HTTP_BIND=$HTTP_BIND
HTTP_PORT=$HTTP_PORT
HTTPS_PORT=$HTTPS_PORT
LE_EMAIL=$LE_EMAIL
TRUSTED_PROXIES=$TRUSTED_PROXIES
EOF
    )
    mkdir -p "$INSTALL_DIR/state" "$INSTALL_DIR/backups"
    ln -sf "$INSTALL_DIR/installer/recoded-ptero" /usr/local/bin/recoded-ptero
    chmod +x "$INSTALL_DIR/installer/recoded-ptero" "$INSTALL_DIR/installer/updater/updater.sh"

    info "Building the panel. This takes 5-15 minutes on the first run, please be patient..."
    if ! dc build --build-arg "MC_COMMIT=$commit" panel; then
        die "The build failed. Scroll up for the reason (a common one is too little memory: 4 GB or swap is recommended)."
    fi
    ok "Panel built"

    info "Starting the services..."
    dc up -d || die "Could not start the services. Check: docker compose -f $COMPOSE_FILE logs"

    info "Waiting for the panel to come up (first start sets up the database)..."
    local waited=0 code
    while [ "$waited" -lt 420 ]; do
        code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 5 "http://${MC_WAIT_HOST:-127.0.0.1}:$HTTP_PORT/auth/login" 2>/dev/null || true)"
        if [ -n "$code" ] && [ "$code" != "000" ] && [ "$code" -lt 500 ]; then break; fi
        sleep 5; waited=$((waited + 5))
    done
    [ "$waited" -lt 420 ] || die "The panel did not come up in time. Check: recoded-ptero logs panel"
    ok "Panel is running"

    info "Creating the owner account..."
    local tries=0
    until dc exec -T panel php artisan p:user:make --email="$ADMIN_EMAIL" --username="$ADMIN_USER" \
        --name-first="$ADMIN_FIRST" --name-last="$ADMIN_LAST" --password="$ADMIN_PASS" --admin=1 --role=owner >/dev/null 2>&1; do
        tries=$((tries + 1))
        [ "$tries" -ge 5 ] && die "Could not create the owner account. Try: recoded-ptero artisan p:user:make --role=owner"
        sleep 5
    done
    ok "Owner account created"

    [ "$AUTO_UPDATE" = "1" ] && dc exec -T panel php artisan p:update:auto on >/dev/null 2>&1 && ok "Automatic updates enabled"

    case "$MODE" in
        1) open_firewall_ports "$HTTP_PORT/tcp" ;;
        2) open_firewall_ports 80/tcp 443/tcp ;;
    esac

    echo
    printf '%s%s%s\n' "$C_GREEN" "======================================================" "$C_RESET"
    printf '%s%s%s\n' "$C_GREEN$C_BOLD" " Recoded Ptero is installed" "$C_RESET"
    printf '%s%s%s\n' "$C_GREEN" "======================================================" "$C_RESET"
    echo " URL:       $APP_URL"
    echo " E-mail:    $ADMIN_EMAIL"
    echo " Username:  $ADMIN_USER"
    if [ "$GENERATED_PASS" = "1" ]; then
        echo " Password:  $ADMIN_PASS   (generated, change it after logging in)"
    else
        echo " Password:  the one you entered"
    fi
    echo
    echo " Next steps:"
    echo "  1. Log in and set up mail under Admin > Settings > Mail."
    echo "  2. Updates: Admin > Settings > Updates (button + automatic updates)."
    if [ "${WITH_WINGS:-0}" = "1" ]; then
        echo "  3. Wings is installed next and connected to the panel automatically."
    else
        echo "  3. Game servers need Wings: run this installer again and choose the Wings option."
        echo "     On this machine it creates the node and certificate by itself; on another"
        echo "     machine it guides you through connecting it."
    fi
    echo
    echo " Handy commands:  recoded-ptero status | update | logs | backup | restart"
    echo " Files:           $INSTALL_DIR  (secrets in $INSTALL_DIR/.env)"
    [ "$MODE" = "3" ] && echo " Reverse proxy:   forward $APP_URL to http://127.0.0.1:$HTTP_PORT"
    echo
}

update_panel() {
    [ -f "$INSTALL_DIR/.env" ] || die "No panel installation found in $INSTALL_DIR."
    info "Updating the panel..."
    exec bash "$INSTALL_DIR/installer/updater/updater.sh" run "installer-$(date +%s)" false 0
}

uninstall_panel() {
    [ -f "$INSTALL_DIR/.env" ] || die "No panel installation found in $INSTALL_DIR."
    warn "This stops the panel and removes the containers."
    if confirm "Also DELETE ALL DATA (database, settings, uploads) and $INSTALL_DIR?" n; then
        local answer=""
        if [ "${MC_NONINTERACTIVE:-0}" = "1" ]; then answer="DELETE"; else read -r -p "Type DELETE to confirm: " answer </dev/tty; fi
        [ "$answer" = "DELETE" ] || die "Aborted, nothing was removed."
        dc down -v --remove-orphans
        docker image rm recodedptero-panel:latest recodedptero-panel:rollback recodedptero-updater:latest >/dev/null 2>&1 || true
        rm -f /usr/local/bin/recoded-ptero
        rm -rf "$INSTALL_DIR"
        ok "Panel and all its data were removed."
    else
        dc down --remove-orphans
        ok "Panel stopped. Data is kept; start it again with: docker compose -f $COMPOSE_FILE up -d"
    fi
}

# ---------------------------------------------------------------- certificates

is_ip() { [[ "$1" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; }

# Warns when DOMAIN doesn't point at this server, since Let's Encrypt would then fail.
check_dns() {
    local domain="$1" server_ip domain_ip
    server_ip="$(public_ip)"
    domain_ip="$(getent hosts "$domain" | awk '{print $1; exit}')"
    if [ -z "$domain_ip" ] || { [ -n "$server_ip" ] && [ "$domain_ip" != "$server_ip" ]; }; then
        warn "$domain resolves to '${domain_ip:-nothing}', but this server's address is '$server_ip'."
        warn "Create a DNS A record: $domain -> $server_ip (and wait a few minutes)."
        confirm "Try to get the certificate anyway?" n || die "Aborted. Fix the DNS record and run the installer again."
    fi
}

install_certbot() {
    command -v certbot >/dev/null 2>&1 && return 0
    info "Installing certbot..."
    case "$PKG" in
        apt) apt-get install -y certbot >/dev/null ;;
        dnf) dnf install -y epel-release >/dev/null 2>&1; dnf install -y certbot >/dev/null ;;
        yum) yum install -y epel-release >/dev/null 2>&1; yum install -y certbot >/dev/null ;;
    esac
    command -v certbot >/dev/null 2>&1 || die "Could not install certbot."
}

# Let's Encrypt briefly needs port 80. Sets CERT_PRE/CERT_POST to pause whatever uses it;
# certbot stores these hooks, so automatic renewals do the same.
port80_hooks() {
    CERT_PRE=""; CERT_POST=""
    port_in_use 80 || return 0
    local panel_container
    panel_container="$(env_value COMPOSE_PROJECT_NAME)"
    panel_container="${panel_container:-recodedptero}-panel-1"
    if docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "$panel_container"; then
        CERT_PRE="docker stop $panel_container"; CERT_POST="docker start $panel_container"
        return 0
    fi
    local svc
    for svc in nginx apache2 httpd caddy haproxy lighttpd; do
        if systemctl is-active --quiet "$svc" 2>/dev/null; then
            CERT_PRE="systemctl stop $svc"; CERT_POST="systemctl start $svc"
            return 0
        fi
    done
    die "Port 80 is used by another program. Let's Encrypt needs it for a moment; stop that program and run the installer again."
}

enable_cert_renewal() {
    if systemctl list-unit-files 2>/dev/null | grep -q '^certbot.timer'; then
        systemctl enable --now certbot.timer >/dev/null 2>&1
    elif systemctl list-unit-files 2>/dev/null | grep -q '^certbot-renew.timer'; then
        systemctl enable --now certbot-renew.timer >/dev/null 2>&1
    else
        echo "17 3,15 * * * root certbot renew -q" > /etc/cron.d/recoded-ptero-certbot
    fi
}

# obtain_certificate DOMAIN EMAIL — certificate in /etc/letsencrypt/live/DOMAIN (where Wings
# looks for it), renewed automatically; Wings restarts after each renewal to load it.
obtain_certificate() {
    local domain="$1" email="$2"
    install_certbot

    mkdir -p /etc/letsencrypt/renewal-hooks/deploy
    cat > /etc/letsencrypt/renewal-hooks/deploy/recoded-ptero-wings.sh <<'EOF'
#!/bin/sh
# Installed by the Recoded Ptero installer: Wings only reads its certificate on start.
systemctl is-active --quiet wings && systemctl restart wings
exit 0
EOF
    chmod +x /etc/letsencrypt/renewal-hooks/deploy/recoded-ptero-wings.sh

    if [ -f "/etc/letsencrypt/live/$domain/fullchain.pem" ]; then
        ok "A certificate for $domain already exists"
    else
        check_dns "$domain"
        port80_hooks
        open_firewall_ports 80/tcp
        info "Requesting a Let's Encrypt certificate for $domain..."
        local args=(certonly --standalone --non-interactive --agree-tos -m "$email" -d "$domain")
        [ -n "$CERT_PRE" ] && args+=(--pre-hook "$CERT_PRE" --post-hook "$CERT_POST")
        certbot "${args[@]}" || die "Could not get a certificate for $domain. Check that the domain points at this server and port 80 is reachable from the internet."
        ok "Certificate for $domain installed"
    fi
    enable_cert_renewal
    ok "Certificates renew automatically"
}

# ---------------------------------------------------------------- wings

install_wings_binary() {
    info "Downloading Wings..."
    mkdir -p /etc/pterodactyl
    curl -fsSL -o /usr/local/bin/wings "https://github.com/pterodactyl/wings/releases/latest/download/wings_linux_$ARCH" \
        || die "Could not download Wings."
    chmod u+x /usr/local/bin/wings
    ok "Wings installed"

    cat > /etc/systemd/system/wings.service <<'EOF'
[Unit]
Description=Pterodactyl Wings Daemon
After=docker.service
Requires=docker.service
PartOf=docker.service

[Service]
User=root
WorkingDirectory=/etc/pterodactyl
LimitNOFILE=4096
PIDFile=/var/run/wings/daemon.pid
ExecStart=/usr/local/bin/wings
Restart=on-failure
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
EOF
    if command -v systemctl >/dev/null 2>&1; then
        systemctl daemon-reload
        systemctl enable wings >/dev/null 2>&1
    fi
}

env_value() {
    grep -m1 "^$1=" "$INSTALL_DIR/.env" 2>/dev/null | cut -d= -f2-
}

start_wings() {
    if ! command -v systemctl >/dev/null 2>&1; then
        warn "systemd is not available here; start Wings yourself with: /usr/local/bin/wings"
        return 0
    fi
    systemctl restart wings >/dev/null 2>&1
    sleep 5
    if systemctl is-active --quiet wings; then
        ok "Wings is running"
    else
        warn "Wings did not start. See: journalctl -u wings -n 50"
    fi
}

# The panel runs on this machine: create the node through the panel and connect Wings, no copying needed.
setup_local_node() {
    local app_url panel_host email scheme node_id mem_mb disk_mb tries=0
    app_url="$(env_value APP_URL)"
    panel_host="${app_url#*://}"; panel_host="${panel_host%%/*}"; panel_host="${panel_host%%:*}"
    email="$(env_value LE_EMAIL)"; [ -n "$email" ] || email="$(env_value APP_SERVICE_AUTHOR)"

    if [[ "$app_url" == https://* ]]; then
        # Browsers only allow the server console over HTTPS when Wings has a certificate too.
        scheme="https"
        ask NODE_FQDN "Domain for Wings (the panel's domain works fine)" "$panel_host"
        is_ip "$NODE_FQDN" && die "With HTTPS, Wings needs a domain name instead of an IP address."
        obtain_certificate "$NODE_FQDN" "$email"
    else
        scheme="http"
        ask NODE_FQDN "Address players and the panel use to reach this machine" "$panel_host"
    fi

    mem_mb=$(( $(awk '/MemTotal/ {print $2}' /proc/meminfo) / 1024 - 1536 ))
    [ "$mem_mb" -lt 1024 ] && mem_mb=1024
    disk_mb=$(( $(df -Pm / | awk 'NR==2 {print $4}') - 10240 ))
    [ "$disk_mb" -lt 5120 ] && disk_mb=5120

    info "Creating the node in the panel..."
    until node_id="$(dc exec -T panel php artisan p:node:quick-setup --fqdn="$NODE_FQDN" --scheme="$scheme" \
            --memory="$mem_mb" --disk="$disk_mb" 2>/dev/null | tr -d '\r' | tail -n1)" \
            && [[ "$node_id" =~ ^[0-9]+$ ]]; do
        tries=$((tries + 1))
        [ "$tries" -ge 12 ] && die "Could not create the node in the panel. Check: recoded-ptero logs panel"
        sleep 5
    done
    ok "Node #$node_id created"

    dc exec -T panel php artisan p:node:configuration "$node_id" --format=yaml > /etc/pterodactyl/config.yml.new \
        && [ -s /etc/pterodactyl/config.yml.new ] || die "Could not read the node configuration from the panel."
    mv -f /etc/pterodactyl/config.yml.new /etc/pterodactyl/config.yml
    chmod 600 /etc/pterodactyl/config.yml
    ok "Wings is configured"
    start_wings
}

# The panel runs somewhere else: prepare the certificate, then connect with the token from the panel.
setup_remote_node() {
    local scheme="http" insecure=""
    echo
    ask PANEL_URL "URL of your panel (for example https://panel.example.com)" ""
    [[ "$PANEL_URL" =~ ^https?:// ]] || die "Please enter the full panel URL starting with http:// or https://"
    PANEL_URL="${PANEL_URL%/}"

    if [[ "$PANEL_URL" == https://* ]]; then
        scheme="https"
        echo "Your panel uses HTTPS, so Wings needs its own domain and certificate."
        ask NODE_FQDN "Domain of THIS machine (for example node1.example.com)" ""
        { [ -n "$NODE_FQDN" ] && ! is_ip "$NODE_FQDN"; } || die "A domain name (not an IP address) is required."
        ask LE_EMAIL "E-mail address for Let's Encrypt (expiry notices)" ""
        [[ "$LE_EMAIL" == *@*.* ]] || die "Please enter a valid e-mail address."
        obtain_certificate "$NODE_FQDN" "$LE_EMAIL"
    else
        insecure="--allow-insecure"
        ask NODE_FQDN "Address of THIS machine (IP or domain)" "$(public_ip)"
    fi

    echo
    printf '%s%s%s\n' "$C_BOLD" "Now create the node in the panel" "$C_RESET"
    echo "  Admin > Nodes > Create New, and enter:"
    echo "    FQDN:                    $NODE_FQDN"
    if [ "$scheme" = "https" ]; then
        echo "    Communicate Over SSL:    Use SSL Connection"
    else
        echo "    Communicate Over SSL:    Use HTTP Connection"
    fi
    echo "    Daemon Port / SFTP Port: 8080 / 2022"
    echo "  Then open the node's Configuration tab and click 'Generate Token'."
    echo

    ask WINGS_TOKEN "Token (shown after 'Generate Token', leave empty to do it later)" ""
    if [ -z "$WINGS_TOKEN" ]; then
        echo " Later: run the command from the Configuration tab on this machine, then: systemctl restart wings"
        return 0
    fi
    ask WINGS_NODE "Node ID (the number in the node's URL)" ""
    if (cd /etc/pterodactyl && /usr/local/bin/wings configure --panel-url "$PANEL_URL" --token "$WINGS_TOKEN" --node "$WINGS_NODE" $insecure); then
        if [ "$scheme" = "https" ] && ! grep -A2 'ssl:' /etc/pterodactyl/config.yml | grep -q 'enabled: true'; then
            warn "The node is set to HTTP in the panel. Edit it: Communicate Over SSL -> Use SSL Connection, then run this again."
        fi
        ok "Wings is configured"
        start_wings
    else
        warn "Configuring Wings failed. Check the URL, token and node ID, or copy the config from the Configuration tab to /etc/pterodactyl/config.yml."
    fi
}

install_wings() {
    local panel_here=0
    [ -f "$INSTALL_DIR/.env" ] && panel_here=1

    install_packages
    install_docker
    install_wings_binary
    open_firewall_ports 8080/tcp 2022/tcp

    if [ "$panel_here" = "1" ]; then
        setup_local_node
    else
        setup_remote_node
    fi

    echo
    ok "Wings installation finished."
    echo " Game server ports are not created or opened automatically. Add them under"
    echo " Admin > Nodes > (your node) > Allocation, and allow them in your firewall yourself."
}

uninstall_wings() {
    [ -f /usr/local/bin/wings ] || die "Wings is not installed."
    confirm "Remove Wings? Servers stay in /var/lib/pterodactyl until you delete them." n || die "Aborted."
    systemctl disable --now wings >/dev/null 2>&1 || true
    rm -f /etc/systemd/system/wings.service /usr/local/bin/wings /etc/letsencrypt/renewal-hooks/deploy/recoded-ptero-wings.sh
    systemctl daemon-reload 2>/dev/null || true
    if confirm "Also delete /etc/pterodactyl (node configuration)?" n; then rm -rf /etc/pterodactyl; fi
    ok "Wings removed."
}

# ---------------------------------------------------------------- main

main() {
    require_root
    detect_system

    printf '\n%s%s%s\n' "$C_BOLD" "Recoded Ptero installer" "$C_RESET"
    echo "Source: github.com/$GITHUB_REPO ($GITHUB_BRANCH)"
    echo

    local action="${MC_ACTION:-}"
    if [ -z "$action" ]; then
        echo "What do you want to do?"
        echo "  [1] Install the panel"
        echo "  [2] Install Wings (game server daemon) on this machine"
        echo "  [3] Install the panel and Wings on this machine"
        echo "  [4] Update the panel now"
        echo "  [5] Uninstall the panel"
        echo "  [6] Uninstall Wings"
        echo "  [0] Quit"
        ask CHOICE "Choose" ""
        case "$CHOICE" in
            1) action="panel" ;;
            2) action="wings" ;;
            3) action="both" ;;
            4) action="update" ;;
            5) action="uninstall-panel" ;;
            6) action="uninstall-wings" ;;
            *) exit 0 ;;
        esac
    fi

    case "$action" in
        panel) install_panel ;;
        wings) install_wings ;;
        both) WITH_WINGS=1; install_panel; install_wings ;;
        update) update_panel ;;
        uninstall-panel) uninstall_panel ;;
        uninstall-wings) uninstall_wings ;;
        *) die "Unknown action: $action" ;;
    esac
}

# The whole script is defined before this line runs, so it is safe to pipe into bash too.
main "$@"; exit $?
