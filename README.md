# MC Panel

A game server panel for Minecraft hosting, built on the open source [Pterodactyl Panel](https://github.com/pterodactyl/panel) (MIT). On top of stock Pterodactyl it adds:

- **Coin economy**: earn coins via Linkvertise, an AFK page, daily rewards with streaks, vouchers and referrals; spend them in a shop on resources, backups or whole server plans
- **Self-service servers** for normal users, with per-user resource pools and cooldowns
- **Support tickets** with notifications and ratings
- **Roles**: user, supporter, moderator, admin, owner, with an audit log of staff actions
- **Modrinth plugin installer** per server
- **Public registration**, forgot-password flow, announcements
- **Translations**: English, German, French, Spanish and more, selectable per user
- **One-click and automatic updates** from within the panel

## Install

On a fresh Debian, Ubuntu, Rocky, Alma or CentOS server, as root:

```bash
bash <(curl -sSL https://raw.githubusercontent.com/Knaeckebrot14-12/MC-Panel/main/install.sh)
```

The installer sets up Docker, downloads this repository to `/opt/mc-panel`, asks a few questions (how the panel is reached, owner account) and starts everything. It can also install [Wings](https://github.com/pterodactyl/wings), the daemon that runs the game servers, on the same or another machine.

Requirements: 2 CPU cores and 4 GB RAM are recommended (the first build needs the memory; the installer offers to add swap on smaller servers). Ports 80 and 443 for the panel.

Unattended installs work with environment variables, for example:

```bash
MC_NONINTERACTIVE=1 MC_ACTION=panel MC_MODE=1 MC_HOST=203.0.113.10 MC_ADMIN_EMAIL=me@example.com \
  bash <(curl -sSL https://raw.githubusercontent.com/Knaeckebrot14-12/MC-Panel/main/install.sh)
```

## Updates

The panel checks GitHub for new versions every five minutes. As owner, open **Admin → Settings → Updates**:

- **Update now** downloads and builds the new version next to the running one, backs up the database, swaps the container and rolls back automatically if the new version does not start.
- **Install updates automatically** does the same as soon as a new version is pushed to this repository.

Behind the scenes a small `updater` container (started by the installer, no ports exposed) watches a shared folder for update requests from the panel. If the panel is ever unreachable, update from the server with:

```bash
mc-panel update
```

Other helpers: `mc-panel status | logs | backup | restart | artisan <command>`. Backups of the last 7 updates are kept in `/opt/mc-panel/backups`.

## Publishing updates (for maintainers)

Every push to the `main` branch is an update. Bump the number in [`VERSION`](VERSION) for a readable version label, commit and push; installed panels show the new commit and its commit messages under **Settings → Updates**.

To make a fork update from its own repository, set `MC_PANEL_REPO=<owner>/<repo>` in `/opt/mc-panel/.env` (the installer honours `MC_PANEL_REPO` and `MC_PANEL_BRANCH` too).

## Development

```bash
docker compose up -d --build panel   # with a local docker-compose.yml (not tracked)
```

The production stack is [`docker-compose.prod.yml`](docker-compose.prod.yml); the installer and updater live in [`install.sh`](install.sh) and [`installer/`](installer).

## License

MIT, see [LICENSE.md](LICENSE.md). Pterodactyl® is a registered trademark of its respective owners; this project is not affiliated with them.
