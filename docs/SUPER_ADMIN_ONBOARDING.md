# Super Admin Onboarding & Quick Reference

Purpose: A concise checklist and quick actions for Super Admins to get started managing the TIL Appraisals application.

Prerequisites
- PHP 8+ installed and on PATH
- Composer installed
- A database configured in `.env`
- Local dev: run from project root `d:/NTG_APP/www/til_app_real`

Quick Setup (one-time for a new dev machine)
1. Install dependencies

    composer install

2. Copy `.env.example` to `.env` and set DB credentials. Then generate app key:

    php artisan key:generate

3. Run migrations and seeders (this will create test data used by the app and tests):

    php artisan migrate:fresh --seed

Core Super Admin Tasks
- Manage users: `Users` -> add/edit/disable accounts. Super Admin can impersonate (Start/Stop).
- Manage departments and assign department heads.
- Manage financial years: create/activate/close a financial year before objective setting.
- Audit logs: review the `Audit Logs` module for system changes and signature events.

Impersonation
- Start impersonation: from Super Admin dashboard click "Impersonate" on a user (or POST to `impersonate.start` route).
- While impersonating you will see a yellow banner with a Stop Impersonation button. Use that to revert.

Common Developer/Admin Commands (Windows PowerShell)
See `scripts/windows_admin_setup.ps1` for an automated script. Basic commands:

    # from project root (PowerShell)
    composer install
    php artisan migrate:fresh --seed
    php artisan serve --host=127.0.0.1 --port=8000

Running Tests

    # run PHPUnit (requires vendor packages installed)
    php vendor/phpunit/phpunit/phpunit

Notes & Troubleshooting
- If you see seed-related duplicate key issues when running tests locally, run `php artisan migrate:fresh --seed` to reset the DB.
- If a blade view references a file that was recently moved/archived, check the view for the original path and update links if necessary.

Support
- For issues with production data or backups, coordinate with the ops/DBA team and do not run `migrate:fresh` on production.

Change log
- 2025-11-16: Created onboarding and quick admin script. Archive demo HTML moved to `archive/static-demo/20251116/`.
