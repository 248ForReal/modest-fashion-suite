# Modest Fashion Suite

An operations platform for a small garment studio: a public product catalog and landing page, plus an admin back office for attendance, production work, inventory and distribution. Built with Laravel 13, Filament 4 and Livewire, served by FrankenPHP and run with Docker Compose.

> **About this repository.** It is the cleaned, generic version of a system built for a real garment business. Brand, copy, photos and contact details are fictional demo data; no production data or credentials are included.

## Features

**Public site**
- Landing page assembled from database-driven sections (hero carousel, story, collections, process, partners, testimonials, contact), editable from the admin panel, with per-section preview and EN/ID language switch.
- Product catalog with sizes, size guides, per-product video links, a persistent cart and WhatsApp checkout.
- PDF catalog viewer for phones and tablets (pdf.js, swipe / spread / pinch-zoom) with a printable QR code, managed from the admin panel.

**Back office (Filament 4)**
- **Attendance**: GPS-validated clock-in/out, leave requests, monthly recap, XLSX export. Late rules come from one place: a global work start time, an optional late tolerance, and **per-user custom start times**, with the tolerance applied from each person's own start time.
- **Work assignments**: work types, assignment and review flow, self-assign limits, cancel requests.
- **Inventory and products**: garment categories and types, sizes, warehouses, stock per unit, barcode printing and scanning.
- **Distribution and transaction reports.**
- **Users and roles**: registration with admin approval, Google sign-in (optional), role and permission matrix (Spatie Permission) with sidebar access control per role.
- **AI-assisted product entry**: generates a ready-to-paste prompt and parses the reply into the product form (name, descriptions, attributes).

## Tech stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13, PHP 8.5 |
| Admin UI | Filament 4, Livewire 3, Alpine.js |
| Server | FrankenPHP (Caddy), automatic HTTPS |
| Database | MySQL 8.4 |
| Auth / RBAC | Laravel Socialite, Spatie Laravel-Permission |
| Imaging / export | Intervention Image (GD), OpenSpout (XLSX), picqer barcode generator |
| Packaging | Docker Compose |

## Getting started

Requirements: Docker with Compose v2.

```bash
git clone <this-repo> && cd modest-fashion-suite

# 1. Compose settings (database credentials). Use your own strong values.
cp .env.example .env
# edit .env: set DB_PASSWORD and DB_ROOT_PASSWORD

# 2. Application settings
cp src/.env.example src/.env
# edit src/.env: set the same DB_PASSWORD

# 3. Build and start
docker compose up -d --build

# 4. Install and initialise
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# 5. Front-end assets (Vite + Tailwind). Needs Node.js 20+ on the host.
cd src && npm ci && npm run build && cd ..
```

Open <http://localhost> for the public site and <http://localhost/admin> for the admin panel.

### Demo accounts (local environment only)

The seeder creates these only when `APP_ENV` is `local` or `testing`:

| Role | Email | Password |
|---|---|---|
| Super admin | `admin@example.com` | `password` |
| Tailor | `tailor@example.com` | `password` |
| Intern | `intern@example.com` | `password` |

Change or delete them before exposing an instance to a network.

## Project layout

```
.
├── Dockerfile                 # FrankenPHP + PHP extensions
├── docker-compose.yml         # app + MySQL (loopback-only port)
├── frankenphp/Caddyfile       # web server config, domain from SERVER_NAME
└── src/                       # Laravel application
    ├── app/Filament/          # resources and pages of the admin panel
    ├── app/Livewire/          # attendance clock-in component
    ├── app/Models/            # incl. UserWorkSchedule (single source of late rules)
    ├── database/              # migrations and demo seeders
    └── resources/views/       # public site, landing, catalog, PDF viewer
```

## Engineering notes

- **One source of truth for lateness.** `UserWorkSchedule` resolves each user's start time and tolerance; clock-in, the monthly recap, the XLSX export and the dashboard all go through it, so rules cannot drift apart.
- **Hardened defaults.** The database port is bound to `127.0.0.1` (Docker publishes ports around host firewalls such as UFW), `APP_DEBUG=false` in production, and uploads are validated and size-limited.
- **No CDN dependency at runtime.** Alpine.js, pdf.js and the QR library are served locally, so the public pages work in restricted networks.
- **Pinned runtime.** The image tag pins the exact FrankenPHP and PHP patch version so rebuilds are reproducible.

## Security

Never commit `.env` files, database dumps or uploaded media; `.gitignore` already excludes them. To report a vulnerability, open a private security advisory on the repository.

## License

[MIT](LICENSE)
