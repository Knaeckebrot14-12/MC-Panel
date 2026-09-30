# Recoded Ptero

A game server panel for Minecraft hosting, built on the open source [Pterodactyl Panel](https://github.com/pterodactyl/panel) (MIT). On top of stock Pterodactyl it adds:

- **Coin economy**: earn coins via Linkvertise, an AFK page, daily rewards with streaks, vouchers and referrals; spend them in a shop on resources, backups or whole server plans
- **Self-service servers** for normal users, with per-user resource pools and cooldowns
- **Support tickets** with notifications and ratings
- **Roles**: user, supporter, moderator, admin, owner, with an audit log of staff actions
- **Minecraft tools** per server: Modrinth plugin installer, player manager (online players, whitelist, operators, bans), server.properties as a form, CPU/RAM/player history graphs, automatic backups with rotation
- **Public registration** with e-mail confirmation and an accounts-per-IP limit against alt accounts, log in with Discord, forgot-password flow, announcements
- **Admin statistics** (users, servers, tickets, coins, node usage), **maintenance mode** (banner or lock-out) and a public **status page** at /status
- **Translations**: English, German, French, Spanish and more, selectable per user
- **One-click and automatic updates** from within the panel

## Install

On a fresh Debian, Ubuntu, Rocky, Alma or CentOS server, as root:

```bash
bash <(curl -sSL https://raw.githubusercontent.com/Knaeckebrot14-12/Recoded-Ptero/main/install.sh)
```

The installer sets up Docker, downloads this repository to `/opt/recoded-ptero`, asks a few questions (how the panel is reached, owner account) and starts everything. It can also install [Wings](https://github.com/pterodactyl/wings), the daemon that runs the game servers, on the same or another machine.

What the installer asks:

1. **What to do**: install Recoded Ptero, upgrade an existing Pterodactyl panel, install Wings, both, update, or uninstall. Nothing is installed before you pick an option.
2. **How the panel is reached**:
   - `1` HTTP by IP or domain (quick test setups),
   - `2` HTTPS with Let's Encrypt: enter the domain (its DNS A record must already point at the server) and an e-mail for certificate notices,
   - `3` behind your own reverse proxy (Nginx, Caddy, Cloudflare Tunnel): enter the public URL and a local port.
3. **Timezone**: the server's timezone is suggested; press Enter or type another one (e.g. Europe/Berlin).
4. **Owner account**: e-mail, username, name and password (leave empty to generate one).
5. **Automatic updates**: on or off (can be changed later under Settings → Updates).
6. **Wings on this server too?** Say yes and panel, node and Wings are set up in one run.

Then it builds and starts everything (5–15 minutes the first time) and prints the URL and login.

**Wings and certificates** are handled automatically too:

- *Panel and Wings on the same machine*: the installer creates the node in the panel, writes the Wings configuration and starts Wings. If the panel uses HTTPS, it also gets a Let's Encrypt certificate for Wings (the panel's domain can be reused, no extra DNS record needed).
- *Wings on another machine*: enter the panel URL; for an HTTPS panel also the machine's domain, and the certificate is created. The installer then tells you exactly what to enter when creating the node and asks for the token from the node's Configuration tab.
- *Databases for game servers*: the installer can also set up a MariaDB server next to Wings (container recoded-gamedb, port 3306, credentials in /etc/recoded-ptero/gamedb.env) and adds it under Admin → Databases, so users can create databases for plugins like LuckPerms right away. On a separate Wings machine it prints the values to enter there.
- Running the Wings option again on the panel server repairs the setup (same node, fresh configuration). Wings gets a Docker network range that does not collide with the panel's own Docker network, and the panel talks to Wings directly on the machine instead of through the public address.
- Firewall: if ufw is active, the installer opens only the ports the panel and Wings need (80/443 or your panel port, 8080 for Wings, 2022 for SFTP). Game server ports are never created or opened automatically; add them per node under Admin → Nodes → Allocation.
- Before the first certificate, the installer shows Let's Encrypt's current Terms of Service and asks you to agree (unattended installs: MC_LE_AGREE=1).
- All certificates renew automatically (the panel's inside its container, Wings' via the certbot timer, restarting Wings afterwards).

## Log in with Discord

Admin → Discord login (Settings → Login & Registration) shows the redirect URL and a four-step guide: create an application in the [Discord Developer Portal](https://discord.com/developers/applications), add the redirect URL under OAuth2, paste Client ID and Client Secret, tick the box. New Discord users can get an account automatically (can be turned off); existing users link Discord on their account page, or are linked automatically through the same verified e-mail address. Accounts with two-factor authentication keep using password + code.

## Upgrade from Pterodactyl

Run the same command on the server of your existing Pterodactyl panel (1.x, served by nginx, as in the official docs) and choose **Upgrade**. All users, servers, nodes, eggs, API keys and settings move over; logins, the panel URL and your Wings nodes keep working.

How data loss is prevented:

- Recoded Ptero is built first while your panel keeps running; the downtime is only the move itself (a few minutes, game servers keep running).
- Before anything changes: a dump of the database, copies of `.env`, the nginx site and the crontab, and an archive of the panel files are saved to `/opt/recoded-ptero/backups/pterodactyl-<date>/`.
- The old database is only read, never changed; the old panel directory is kept as it is.
- After the copy, every table's row count is compared with the original. The `APP_KEY` is carried over, so encrypted data (node tokens, 2FA, database host passwords) stays readable.
- If any step fails, the old panel is switched back on automatically. Later you can still go back with `rollback.sh` in the backup folder.

Requirements: 2 CPU cores and 4 GB RAM are recommended (the first build needs the memory; the installer offers to add swap on smaller servers). Ports 80 and 443 for the panel.

Unattended installs work with environment variables, for example:

```bash
MC_NONINTERACTIVE=1 MC_ACTION=panel MC_MODE=1 MC_HOST=203.0.113.10 MC_ADMIN_EMAIL=me@example.com \
  bash <(curl -sSL https://raw.githubusercontent.com/Knaeckebrot14-12/Recoded-Ptero/main/install.sh)
```

## Updates

The panel checks GitHub for new versions every five minutes. As owner, open **Admin → Settings → Updates**:

- **Update now** downloads and builds the new version next to the running one, backs up the database, swaps the container and rolls back automatically if the new version does not start.
- **Install updates automatically** does the same as soon as a new version is pushed to this repository.

Behind the scenes a small `updater` container (started by the installer, no ports exposed) watches a shared folder for update requests from the panel. If the panel is ever unreachable, update from the server with:

```bash
recoded-ptero update
```

Other helpers: `recoded-ptero status | logs | backup | restart | artisan <command>`. Backups of the last 7 updates are kept in `/opt/recoded-ptero/backups`.

## Publishing updates (for maintainers)

Every push to the `main` branch is an update. Bump the number in [`VERSION`](VERSION) for a readable version label, commit and push; installed panels show the new commit and its commit messages under **Settings → Updates**.

To make a fork update from its own repository, set `MC_PANEL_REPO=<owner>/<repo>` in `/opt/recoded-ptero/.env` (the installer honours `MC_PANEL_REPO` and `MC_PANEL_BRANCH` too).

## Development

```bash
docker compose up -d --build panel   # with a local docker-compose.yml (not tracked)
```

The production stack is [`docker-compose.prod.yml`](docker-compose.prod.yml); the installer and updater live in [`install.sh`](install.sh) and [`installer/`](installer).

## License

MIT, see [LICENSE.md](LICENSE.md). Pterodactyl® is a registered trademark of its respective owners; this project is not affiliated with them.
