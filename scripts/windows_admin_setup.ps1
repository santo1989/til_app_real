<#
  Windows PowerShell setup helper for local development (PowerShell v5.1)
  Usage: Open PowerShell in the project root (d:/NTG_APP/www/til_app_real) and run:

    .\scripts\windows_admin_setup.ps1

  Notes:
  - Assumes PHP and Composer are in PATH.
  - This script is intended for local dev only. Do NOT run in production.
#>

Write-Host "=== Windows Admin Setup Script ===" -ForegroundColor Cyan

if (!(Test-Path "vendor")) {
    Write-Host "Vendor directory not found. Running composer install..." -ForegroundColor Yellow
    composer install
}

Write-Host "Clearing caches..." -ForegroundColor Green
php artisan config:clear
php artisan cache:clear
php artisan route:clear

Write-Host "Generating app key (if needed)..." -ForegroundColor Green
php artisan key:generate

Write-Host "Running migrations and seeders (migrate:fresh --seed). This will wipe the local DB." -ForegroundColor Yellow
php artisan migrate:fresh --seed

Write-Host "Running tests (PHPUnit)" -ForegroundColor Green
try {
    php vendor/phpunit/phpunit/phpunit
} catch {
    Write-Host "PHPUnit run failed. Ensure dependencies are installed and PHP on PATH." -ForegroundColor Red
}

Write-Host "Local server: run `php artisan serve` in another shell to start the dev server." -ForegroundColor Cyan

Write-Host "Done." -ForegroundColor Cyan
