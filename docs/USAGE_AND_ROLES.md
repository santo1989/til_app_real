TIL Performance Appraisal — Usage & Roles Guide

Purpose
-------
This document explains how to use the Performance Appraisal application, lists each role and its responsibilities, and provides a step-by-step workflow for the appraisal cycle (Objective-setting → Midterm → Year-end → IDP/PIP). It also covers developer-facing procedures: running migrations, seeders, tests, and common troubleshooting notes.

Quick links
-----------
- App root: public/index.php
- Routes: routes/web.php, routes/api.php
- Seeders: database/seeders/
- Migrations: database/migrations/
- Tests: tests/ (Feature and Unit)
- Super-admin dashboard: /super-admin (see Blade views under resources/views/layouts and resources/views/super_admin)

Roles & capabilities
--------------------
Note: Roles are stored on the `users` table `role` column. The common roles in the system are:

- super_admin
  - Full system access: manage users, departments, financial years, system settings, and run audits.
  - Can impersonate other users (for support & troubleshooting).
  - Responsible for configuring financial-year windows and application-level defaults.

- hr_admin (HR)
  - Configure processes, manage HR-related records, review escalations, and complete final sign-offs.
  - Manage IDPs/PIPs and run reports.

- line_manager
  - Create and review objectives for direct reports, perform midterm review, add manager comments, recommend year-end scores, and sign-off.

- employee (staff)
  - Create and submit objectives, respond to manager feedback, update IDPs, and acknowledge/perhaps sign/appraise depending on configuration.

- reviewer / dept_head (if used)
  - Additional review layer where workflows require department head review before HR or final sign-off.

Common permissions and UI notes
-------------------------------
- Many UI links are conditional on the authenticated user's `role`. For example the Super Admin nav is only shown to `super_admin` users.
- Policies are defined under `app/Http/Policies` and gate access at controller/resource levels.

Appraisal cycle (user view)
---------------------------
This is the typical flow most organizations follow in the system. Times & windows are configured using Financial Years.

1. Financial Year configuration (HR / Super Admin)
   - Super Admin / HR creates the Financial Year (start/end and label) via Admin → Financial Years.
   - The system uses the active Financial Year when creating objectives and IDPs.

2. Objective setting (Employee & Manager)
   - Employee drafts objectives in Objectives → Create Objective.
   - Each objective must include: title, description, SMART target (where applicable), weight (total weights must sum to 100% for the employee's objectives in the same FY).
   - Client-side validation is provided in the objectives form; server-side enforcement also applies.
   - Submit objectives for manager review.

3. Manager review
   - Manager reviews objectives, provides comments and either accepts or requests changes.
   - When accepted, objectives are locked for changes unless the manager reopens them.

4. Midterm review
   - Midterm review window (set by HR) is used to record midterm progress, update IDP status, and capture manager comments.

5. Year-end review and scoring
   - At year-end, managers provide final scores; HR / Super Admin run reports and finalize sign-offs.
   - Signatures and audit logs are stored when a final sign-off occurs (see Signature & Audit notes below).

6. IDP & PIP
   - IDPs (Individual Development Plans) are tracked as a separate entity. Managers and HR can add milestones.
   - PIP (Performance Improvement Plans) are created when the employee falls below thresholds.

Admin tasks (Super Admin & HR)
------------------------------
- Create departments and line managers before adding users to ensure seeders and quick imports work.
- Configure the active Financial Year. Many calculations reference the active FY.
- If you need to impersonate a user: use impersonation from the Super Admin UI (ensures audit logging). Undo impersonation once done.
- Run database maintenance (backups, archive old FYs) as needed.

Developer & test workflow
-------------------------
Local setup (Windows PowerShell)
1. Copy `.env.example` to `.env` and configure DB connection, mail, and queue settings.
2. Install composer dependencies: composer install
3. Install npm (if using frontend tooling): npm install

Database and seeders (local/test)
- Migrate:
  php artisan migrate
- Seed (production-like seed):
  php artisan db:seed --class=DatabaseSeeder

Testing notes
- Tests use Laravel's RefreshDatabase trait. The repo `tests/TestCase.php` is configured to run the project seeders automatically for tests (so tests run with seeded data by default).
- To run tests locally:
  php artisan test
- If you need to run a single test file:
  ./vendor/bin/phpunit tests/Feature/YourTest.php

Seeders & test data
- The canonical test and demo data live under `database/seeders/`.
- Avoid hard-coded demo HTML pages or static data; instead rely on migrations + seeders for reproducible environment setup.

Signature & Audit
-----------------
- Signatures (if configured) are recorded and are stored in an auditable manner (check `app/Models` for polymorphic signature models).
- Audit logs: `app/AuditLog.php` model captures important operations. Super Admins can review audit logs from the admin UI.

Troubleshooting & common issues
-------------------------------
1. Test seeder errors (duplicate keys / null employee_id)
   - Ensure database was reset before seeding. On SQL Server, watch for unique constraints or required non-nullable columns.
   - If you see 'Cannot insert NULL into column employee_id' during testing, check `database/seeders/UserSeeder.php` for required fields and update seed data to provide employee_id where schema requires it.

2. Client-side validation bypass
   - There is both client-side and server-side validation. If a user manages to submit invalid weights, the controller will reject the request — check validation messages returned in the response.

3. Seeders run during tests unexpectedly/duplicate
   - The base `tests/TestCase.php` has been configured to set `$seed = true` and point `$seeder` to `DatabaseSeeder::class`. If you want to avoid seeding for a specific test, override the properties in that test or call `Artisan::call('migrate:fresh')` instead.

4. Impersonation
   - If impersonation is used, audit logs should record the impersonation start/stop. Avoid running long operations while impersonating.

Files & static/demo cleanup notes
--------------------------------
- The repository previously included a static HTML demo under `Performance Appraisal Process/index.html`. That file has been archived/cleared because the application now relies on migrations + seeders. The original demo remains in VCS history if needed.
- I scanned the codebase for additional static `.html` demo files outside vendor and public build outputs; none were found at the top-level beyond the archived file above. If you have other candidate static files to remove, point them out or request a deeper scan.

Next steps & recommendations
----------------------------
- Add a short checklist for Super Admin onboarding that shows steps to configure the first Financial Year, create departments, and seed initial users.
- Add E2E or browser-based tests (Laravel Dusk or Cypress) for critical flows: objective creation & weight enforcement, impersonation, and signature flows.
- Implement background jobs for notifications (queue & worker) and ensure `php artisan queue:work` is configured in production supervisor.

Contact & contribution
----------------------
If you'd like me to convert this into a printable PDF or expand any role's workflow into a step-by-step annotated guide with screenshots (from UI), I can do that next. I can also:
- Run a deeper search for other static assets to remove.
- Create the Super Admin onboarding checklist as a separate quick-start file.

Last updated: automated by the repo assistant
