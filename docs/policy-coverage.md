# Policy Coverage Overview

This document summarizes which controllers and actions in the appraisal module are protected by named Policy classes, and notes where manual `abort(403)` checks were found (none detected).

-- Summary (high level)

- `App\Policies\ObjectivePolicy` — governs `Objective` access (viewAny, view, create, update, delete).
  - Controllers: `App\Http\Controllers\Appraisal\ObjectiveController` and top-level `App\Http\Controllers\ObjectiveController` where present.
  - Actions covered: index/listing, show, create/store, edit/update, destroy.

- `App\Policies\IdpPolicy` — governs `Idp` access (view, create, update, delete).
  - Controllers: `App\Http\Controllers\Appraisal\IdpController`.
  - Actions covered: index, show/edit, create/store, update, delete. Note: `delete` is explicitly restricted to `hr_admin` and `super_admin` in the policy.

- `App\Policies\AppraisalPolicy` — governs `Appraisal` access (view, sign, approve).
  - Controllers: `App\Http\Controllers\Appraisal\AppraisalController` and related signing endpoints.
  - Actions covered: viewing user appraisals, signing (employee/manager/supervisor), approval by dept_head/hr_admin.

- `App\Policies\UserPolicy` — governs user profile access (view, viewConfidential).
  - Controllers: `App\Http\Controllers\UserController` and profile routes.
  - Actions covered: viewing profiles (self, manager, hr/super), and `viewConfidential` restricts access to `password_plain` and similar sensitive fields (hr_admin, super_admin, and the user themselves).

-- Observations

- I searched controllers for manual `abort(403)` calls and similar hard-coded denies; none were found in `app/Http/Controllers`.
- Many controllers use policy checks directly (`$this->authorize(...)`) or call `can(...)` in Blade where appropriate. Recent standardization efforts added `authorizeResource` and explicit `$this->authorize('update', $model)` usages in key controllers (Objectives, Idp, Appraisal flows).

-- Next recommendations

1. Replace scattered role-based middleware checks with policy checks where an ownership decision is required (e.g., prefer `authorize('update', $objective)` over `role:line_manager` for per-object checks).
2. Add a small CI job that fails if any controller action lacks an obvious policy invocation (heuristic: resource controllers should call `authorizeResource` or individual actions should call `$this->authorize`). This can be manual at first and automated later.
3. Keep `docs/policy-coverage.md` updated after further refactors. If you want, I can generate a more detailed CSV mapping controller->method->policy method by scanning method signatures and usages.

-- Generated: 2025-10-26
