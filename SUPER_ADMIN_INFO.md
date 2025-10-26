# Super Admin Account - TIL Performance Appraisal System

## Account Information

**Email:** admin@ntg.com.bd  
**Password:** 12345678  
**Employee ID:** SUPER001  
**Role:** super_admin

## Features & Permissions

The Super Admin role has been created with **full system access** and the following capabilities:

### 1. **Unrestricted Access**
- Bypasses all role-based middleware checks
- Can access all routes regardless of role requirements
- Has permissions across all modules (Users, Departments, Objectives, Appraisals, IDPs)

### 2. **Policy Permissions**
The super admin automatically passes all policy checks:
- ✅ View any appraisal
- ✅ Submit any appraisal
- ✅ Approve any appraisal
- ✅ View, create, update, delete any objective
- ✅ View, create, update any IDP
- ✅ Manage all users and departments

### 3. **Middleware Integration**
Both `RoleMiddleware` and `RoleCheckMiddleware` have been updated to automatically grant access to super_admin role users.

### 4. **User Model Helper Methods**
The User model now includes convenience methods:
- `isSuperAdmin()` - Check if user is super admin
- `isHrAdmin()` - Returns true for both hr_admin and super_admin
- `isDeptHead()` - Returns true for both dept_head and super_admin
- `isLineManager()` - Returns true for both line_manager and super_admin
- `isBoardMember()` - Returns true for both board and super_admin

## Database Changes

### Migration Updated
- `2025_10_19_000001_create_users_table.php`
- Role enum updated: `['employee', 'line_manager', 'dept_head', 'board', 'hr_admin', 'super_admin']`

### Seeder Created
- `SuperAdminSeeder.php` - Creates the super admin account on database seed
- Automatically called first in `DatabaseSeeder`

## Security Considerations

### ⚠️ Important Security Notes:

1. **Change the Default Password**
   - The default password (12345678) should be changed immediately after first login
   - Navigate to your profile or user management to update the password

2. **Limited Super Admin Accounts**
   - Only create super admin accounts for system administrators
   - Most administrative tasks should use the `hr_admin` role

3. **Audit Trail**
   - All super admin actions are logged in the audit_logs table
   - Regular monitoring recommended

4. **No Department/Line Manager Required**
   - Super admin doesn't belong to any department
   - Has no line manager (stands outside organizational hierarchy)

## Usage Examples

### Login as Super Admin
1. Navigate to: http://127.0.0.1:8000/login
2. Email: admin@ntg.com.bd
3. Password: 12345678
4. Access granted to all system features

### Create Additional Super Admins
1. Login as existing super admin or hr_admin
2. Navigate to Users > Create User
3. Select "Super Admin" from Role dropdown
4. Fill in required details and save

### Verify Super Admin Access
```php
// In any controller or blade template
if (auth()->user()->isSuperAdmin()) {
    // Super admin specific logic
}

// Or check multiple roles including super admin
if (auth()->user()->isHrAdmin()) {
    // Both hr_admin and super_admin can access
}
```

## Files Modified

### 1. Models
- `app/Models/User.php` - Added helper methods for role checking

### 2. Middleware
- `app/Http/Middleware/RoleMiddleware.php` - Super admin bypass
- `app/Http/Middleware/RoleCheckMiddleware.php` - Super admin bypass

### 3. Policies
- `app/Policies/AppraisalPolicy.php` - Super admin full access
- `app/Policies/ObjectivePolicy.php` - Super admin full access
- `app/Policies/IdpPolicy.php` - Super admin full access

### 4. Controllers
- `app/Http/Controllers/UserController.php` - Added super_admin to validation rules

### 5. Views
- `resources/views/appraisal/hr_admin/user_create.blade.php` - Added super_admin option
- `resources/views/appraisal/hr_admin/user_edit.blade.php` - Added super_admin option

### 6. Database
- `database/migrations/2025_10_19_000001_create_users_table.php` - Added super_admin to enum
- `database/seeders/SuperAdminSeeder.php` - New seeder for super admin account
- `database/seeders/DatabaseSeeder.php` - Added SuperAdminSeeder to call stack

## Testing the Super Admin Account

Run the following commands to test:

```bash
# Fresh migration with seeding (creates super admin)
php artisan migrate:fresh --seed

# Login at
http://127.0.0.1:8000/login
# Email: admin@ntg.com.bd
# Password: 12345678

# Test access to all modules:
# - Users Management
# - Departments Management
# - Objectives (all users)
# - Appraisals (all users)
# - IDPs (all users)
# - Reports (all types)
```

## Troubleshooting

### Cannot Login
- Verify database was migrated with seeders: `php artisan migrate:fresh --seed`
- Check users table: `SELECT * FROM users WHERE role='super_admin'`
- Clear cache: `php artisan cache:clear` and `php artisan config:clear`

### Access Denied After Login
- Clear sessions: `php artisan session:clear`
- Verify middleware is properly registered in `app/Http/Kernel.php`
- Check route middleware assignments in `routes/web.php`

### Role Not Appearing in Dropdown
- Clear view cache: `php artisan view:clear`
- Verify UserController validation includes 'super_admin'
- Check browser cache and hard refresh (Ctrl+F5)

---

**Document Created:** October 20, 2025  
**System:** TIL Performance Appraisal System  
**Version:** 1.0  
**Organization:** NTG / Tosrifa Industries Limited
