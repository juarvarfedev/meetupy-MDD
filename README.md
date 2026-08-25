# MeetupY

MeetupY is a Laravel application for discovering Laravel meetup groups and events, creating and managing events, and allowing authenticated users to RSVP. RSVP confirmations are sent through a queued mail job.

This project was built while following the Master.dev **Laravel Fundamentals** course with Leah Thompson.

## Features

- Authentication, email verification, password reset, two-factor authentication, and passkeys through Laravel Fortify.
- User profile, password, security, and appearance settings.
- Meetup groups and seeded example events.
- Public event listing and event detail pages.
- Authenticated event creation and editing with authorization policies and form requests.
- RSVP and cancellation management with a unique event/user pivot record.
- Queued RSVP confirmation emails rendered with a Markdown Blade mail template.
- Inertia React frontend with TypeScript, Tailwind CSS, and Wayfinder route helpers.

## Tech stack

- PHP 8.3+
- Laravel 13
- Inertia.js 3 with React 19 and TypeScript
- Tailwind CSS 4
- Laravel Fortify
- Pest 5, PHPStan/Larastan, Pint, ESLint, and Prettier
- SQLite for local development; PostgreSQL is recommended for production

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- A database supported by Laravel

## Installation

```bash
git clone <repository-url>
cd meetupy-MDD
composer run setup
```

The setup script installs PHP and JavaScript dependencies, creates the environment file, generates an application key, runs migrations, and builds the frontend assets.

Start the local application with:

```bash
composer run dev
```

The default local configuration uses SQLite, the database queue, and the log mailer. The log mailer is useful for development, but it does not deliver email to an inbox.

## Queued emails

Because RSVP confirmations use the database queue, run a worker while developing:

```bash
php artisan queue:work database --sleep=3 --tries=3
```

With `MAIL_MAILER=log`, inspect `storage/logs/laravel.log` to see the rendered email. To send real emails, configure a production mail provider in `.env`.

## Useful commands

```bash
# Run the test suite
php artisan test --compact

# Check PHP formatting and static analysis
vendor/bin/pint --test --format agent
php artisan test --compact
phpstan analyse

# Build frontend assets
npm run build
```

## Deployment

The application can be deployed to Laravel Cloud or another platform such as Render. A production Render setup should use:

1. A Docker-based Laravel web service.
2. Render PostgreSQL rather than SQLite, since Render service filesystems are ephemeral by default.
3. A separate background worker running `php artisan queue:work database --sleep=3 --tries=3`.
4. Production mail credentials instead of `MAIL_MAILER=log`.
5. Environment variables including `APP_KEY`, `APP_ENV=production`, `APP_URL`, `DB_CONNECTION=pgsql`, and the database connection values.

Render's Laravel deployment guide is available at <https://render.com/docs/deploy-php-laravel-docker>.

## Project structure

```text
app/
├── Http/Controllers/       Web and settings controllers
├── Http/Requests/           Validation for event and settings forms
├── Jobs/                    Asynchronous application work
├── Mail/                    Mailable definitions
├── Models/                  Eloquent models and relationships
└── Policies/                Authorization rules

resources/js/
├── components/              Reusable React and UI components
├── layouts/                 Application, event, and settings layouts
└── pages/                   Inertia page components

database/
├── migrations/              Database schema history
└── seeders/                 Demo meetup groups and events
```

## Course notes

The implementation notes and lessons learned from the project history are in [LESSONS_LEARNED.md](LESSONS_LEARNED.md).
