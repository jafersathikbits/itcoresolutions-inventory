# IT Core Solutions Inventory Management System

A Laravel-based inventory and order management application for tracking products, raw materials, stock movements, bills of materials, user permissions, and operational reports.

## Overview

The IT Core Solutions Inventory Management System helps manage inventory operations through a centralized web application. It tracks raw materials and finished products, manages product composition, processes orders against available inventory, and maintains stock movement records.

Built with Laravel, the application includes role-based access control, reporting capabilities, and optional Telegram notifications to support organized inventory workflows.

## Key Features

- **Product Management:** Create, view, update, and manage finished products.
- **Raw Material Management:** Maintain material records, stock quantities, and units of measurement.
- **Bill of Materials (BOM):** Define the materials required to manufacture products and check material availability.
- **Composite Materials:** Manage materials composed of other material components.
- **Stock Management:** Record stock additions and maintain stock movement history.
- **Order Processing:** Manage orders and update inventory through application services.
- **Role-Based Access Control:** Restrict access using roles and permissions, including Super Admin, Admin, and Operator roles.
- **User Management:** Manage users and support ownership-transfer functionality.
- **Reports:** Generate daily reports and downloadable PDF reports.
- **Automated Scheduled Tasks:** Generate daily reports and purge older reports using Laravel's scheduler.
- **Notifications:** Provide in-app notifications and optional Telegram notifications.
- **Authentication:** Support user registration, login, password recovery, and profile management.

## Tech Stack

| Category | Technologies |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Blade, Tailwind CSS, Alpine.js |
| Asset Bundling | Vite, npm |
| Database | SQLite by default; other Laravel-supported databases can be configured |
| Authentication | Laravel Breeze |
| Authorization | Spatie Laravel Permission |
| PDF Generation | barryvdh/laravel-dompdf |
| Testing | PHPUnit |
| Dependency Management | Composer, npm |
| Notifications | Laravel notifications, optional Telegram Bot API integration |

## System Architecture

The application follows Laravel's MVC architecture with a service layer for business operations.

- **Presentation Layer:** Blade templates, Tailwind CSS, and Alpine.js provide the web interface.
- **Routing and Controllers:** Laravel routes direct requests to controllers, which handle application workflows.
- **Business Logic:** Services such as `OrderStockService`, `ReportService`, `OwnershipService`, and `TelegramService` handle specific operations.
- **Data Layer:** Eloquent models interact with the configured database to manage products, materials, orders, stock logs, reports, users, and notifications.
- **Authorization Layer:** Authentication middleware, role checks, and permission middleware protect application functionality.
- **Background and Scheduled Operations:** Laravel console commands and the scheduler handle recurring report generation and report cleanup.

## Project Structure

```text
itcoresolutions-inventory/
├── app/
│   ├── Console/Commands/    # Scheduled report commands
│   ├── Http/
│   │   ├── Controllers/     # Request handling and workflows
│   │   ├── Middleware/      # Role and access control
│   │   └── Requests/        # Request validation
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic and integrations
│   └── View/Components/     # Blade layout components
├── bootstrap/               # Laravel application bootstrap
├── config/                  # Application configuration
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database schema
│   └── seeders/             # Roles, permissions, and sample data
├── public/                  # Web entry point and public assets
├── resources/
│   ├── css/                 # Stylesheets
│   ├── js/                  # Frontend JavaScript
│   └── views/               # Blade templates
├── routes/                  # Web, authentication, and scheduler routes
├── storage/                 # Runtime files and logs
├── tests/                   # Automated tests
├── .env.example             # Environment configuration template
├── artisan                 # Laravel CLI
├── composer.json
├── composer.lock
├── package.json
└── package-lock.json
```

## Prerequisites

Install the following before setting up the application:

- PHP 8.2 or newer, with the PHP extensions required by Laravel 12.
- Composer 2 or newer.
- Node.js and npm.
- SQLite with the PHP SQLite extension, or another supported database and its corresponding PHP driver.
- Git.

A compatible image-processing extension, such as GD or Imagick, may be needed for particular PDF rendering requirements.

## Installation & Setup

**1. Clone the repository**

```bash
git clone <your-repository-url>
cd itcoresolutions-inventory
```

Replace `<your-repository-url>` with the actual GitHub repository URL.

**2. Install backend and frontend dependencies**

```bash
composer install
npm ci
```

**3. Configure the environment**

Copy the example configuration file:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

**4. Generate the application key**

```bash
php artisan key:generate
```

**5. Create the SQLite database**

If you are using SQLite, create the database file if it does not already exist.

Linux or macOS:

```bash
touch database/database.sqlite
```

Windows PowerShell:

```powershell
New-Item -ItemType File -Force database/database.sqlite
```

Confirm that `DB_CONNECTION=sqlite` is configured in your `.env` file.

**6. Run database migrations**

```bash
php artisan migrate
```

**7. Seed roles, permissions, and sample users (optional)**

```bash
php artisan db:seed
```

Review the seeders before using sample accounts. Change or remove demo credentials before exposing the application publicly.

**8. Build frontend assets**

```bash
npm run build
```

The compiled frontend assets are generated locally and are not included in the source repository.

**9. Create the public storage link**

```bash
php artisan storage:link
```

**10. Start the application**

```bash
php artisan serve
```

Open the local URL displayed in your terminal, usually `http://127.0.0.1:8000`.

## Environment Variables

Configure these variables in your local `.env` file. The repository includes `.env.example` as a starting point.

| Variable | Purpose |
|---|---|
| `APP_NAME` | Application name |
| `APP_ENV` | Application environment |
| `APP_KEY` | Encryption key generated by Laravel |
| `APP_DEBUG` | Enables or disables detailed debugging output |
| `APP_URL` | Base application URL |
| `DB_CONNECTION` | Database connection, SQLite by default |
| `DB_HOST`, `DB_PORT` | Database server settings when applicable |
| `DB_DATABASE` | Database name or configured database path |
| `DB_USERNAME`, `DB_PASSWORD` | Database credentials when applicable |
| `SESSION_DRIVER` | Session storage configuration |
| `CACHE_STORE` | Cache storage configuration |
| `QUEUE_CONNECTION` | Queue backend configuration |
| `MAIL_*` | Email transport and sender configuration |
| `TELEGRAM_BOT_TOKEN` | Optional Telegram bot token; leave blank to disable Telegram integration |

For production, configure secrets through your hosting provider's environment settings. Set `APP_ENV=production` and `APP_DEBUG=false`, and never commit your actual `.env` file or credentials.

## Usage

After installation, sign in and use the available modules according to your assigned role and permissions.

Typical workflows include:

1. Create and manage units of measurement.
2. Register raw materials and maintain stock quantities.
3. Define composite materials and product bills of materials.
4. Create finished products and configure their material requirements.
5. Process orders and track inventory changes.
6. Review stock history and operational notifications.
7. Generate reports and download PDF output.
8. Manage user access where permitted.

**Development mode:** To enable Vite hot reloading while developing, open a second terminal and run:

```bash
npm run dev
```

**Scheduled tasks:** The application schedules daily report generation at 23:55 and report cleanup at 23:58, using the server's configured timezone. For local development, run:

```bash
php artisan schedule:work
```

In production, configure Laravel's scheduler as described in the Deployment section.

**Testing:** The repository includes PHPUnit tests. Run them with:

```bash
php artisan test
```

## Deployment

This is a server-rendered Laravel application and requires a PHP-capable hosting environment. GitHub Pages alone cannot run the backend.

1. Provision a compatible PHP runtime, database, and web server.
2. Configure the web server's document root to point to the application's `public/` directory.
3. Set production environment variables, including the application key, database credentials, and `APP_DEBUG=false`.
4. Install production dependencies and build frontend assets:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

5. Apply database migrations:

   ```bash
   php artisan migrate --force
   ```

6. Configure the required writable permissions for `storage/` and `bootstrap/cache/`.
7. Create the storage link if required:

   ```bash
   php artisan storage:link
   ```

8. Configure a cron job to run Laravel's scheduler every minute:

   ```cron
   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
   ```

9. Configure a queue worker if the selected queue backend and application workflows require one.
10. Verify authentication, permissions, database operations, report downloads, and frontend assets after deployment.

Do not expose environment files, database files, application logs, or private uploads through the public web root. Remove or change seeded demo accounts before public deployment.

## Future Improvements

Potential enhancements include:

- Add inventory threshold alerts and low-stock dashboards.
- Expand inventory analytics with trend visualizations and forecasting.
- Add more comprehensive automated tests for stock calculations, order processing, and permission boundaries.
- Improve audit reporting and traceability for inventory adjustments.
- Add deployment automation and continuous integration checks.
- Introduce configurable notification rules for stock and order events.

These are potential enhancements, not claims about functionality already implemented.

## Contributing

Contributions and suggestions are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Follow the existing Laravel conventions and keep changes focused.
4. Run the automated tests and build the frontend assets.
5. Submit a pull request describing the changes and the validation performed.

## License

This project is licensed under the MIT License.
See the [LICENSE](LICENSE) file for details.
