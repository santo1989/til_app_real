<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $employee_id
 * @property string $designation
 * @property int $department_id
 * @property string $date_of_joining
 * @property string $tenure_in_current_role
 * @property string $email
 * @property string $password
 * @property string $role
 * @property int|null $line_manager_id
 * @property bool $is_active
 * 
 * @property-read Department $department
 * @property-read User|null $lineManager
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $reports
 * @property-read \Illuminate\Database\Eloquent\Collection|Objective[] $objectives
 * @property-read \Illuminate\Database\Eloquent\Collection|Appraisal[] $appraisals
 * @property-read \Illuminate\Database\Eloquent\Collection|Idp[] $idps
 * 
 * @method HasMany objectives()
 * @method HasMany reports()
 * @method HasMany appraisals()
 * @method HasMany idps()
 * @method BelongsTo department()
 * @method BelongsTo lineManager()
 */
class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'employee_id',
        'designation',
        'department_id',
        'date_of_joining',
        'tenure_in_current_role',
        'email',
        'password',
        'password_plain',
        'user_image',
        'role',
        'line_manager_id',
        'is_active'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'date_of_joining' => 'date',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'user_image' => 'string',
    ];

    /**
     * Mutator: store password_plain encrypted at rest.
     */
    public function setPasswordPlainAttribute($value)
    {
        if (is_null($value) || $value === '') {
            $this->attributes['password_plain'] = null;
            return;
        }
        // encrypt using Laravel helper
        $this->attributes['password_plain'] = encrypt($value);
    }

    /**
     * Accessor: decrypt password_plain when read. Returns null if not set or decryption fails.
     */
    public function getPasswordPlainAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Throwable $e) {
            // if decryption fails, return null to avoid exceptions in views
            return null;
        }
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Lightweight factory shim for tests when Laravel model factories aren't available.
     * Allows calls like User::factory()->create([...]) in tests.
     */
    public static function factory()
    {
        return new class {
            public function create($attrs = [])
            {
                $defaults = [
                    'name' => 'Test User',
                    'employee_id' => 'EMP' . uniqid(),
                    'email' => uniqid() . '@example.com',
                    // Use the conventional test password 'password' so tests that rely on the default
                    // Laravel test password succeed when calling User::factory()->create()
                    'password' => bcrypt('password'),
                    'role' => 'employee',
                    'is_active' => true,
                ];
                $data = array_merge($defaults, $attrs);
                return \App\Models\User::create($data);
            }

            public function make($attrs = [])
            {
                $defaults = [
                    'name' => 'Test User',
                    'employee_id' => 'EMP' . uniqid(),
                    'email' => uniqid() . '@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'employee',
                    'is_active' => true,
                ];
                $data = array_merge($defaults, $attrs);
                return new \App\Models\User($data);
            }
        };
    }

    /**
     * Get the line manager of the user.
     */
    public function lineManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'line_manager_id');
    }

    /**
     * Get the reports (team members) for the user.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(User::class, 'line_manager_id');
    }

    /**
     * Get the objectives for the user.
     */
    public function objectives(): HasMany
    {
        return $this->hasMany(Objective::class);
    }

    /**
     * Get the appraisals for the user.
     */
    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class);
    }

    /**
     * Get the IDPs for the user.
     */
    public function idps(): HasMany
    {
        return $this->hasMany(Idp::class);
    }

    /**
     * Midterm progress entries recorded for this user.
     */
    public function midtermProgresses(): HasMany
    {
        return $this->hasMany(MidtermProgress::class);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is HR admin or super admin
     */
    public function isHrAdmin()
    {
        return in_array($this->role, ['hr_admin', 'super_admin']);
    }

    /**
     * Check if user is department head or super admin
     */
    public function isDeptHead()
    {
        return in_array($this->role, ['dept_head', 'super_admin']);
    }

    /**
     * Check if user is line manager or super admin
     */
    public function isLineManager()
    {
        return in_array($this->role, ['line_manager', 'super_admin']);
    }

    /**
     * Check if user is board member or super admin
     */
    public function isBoardMember()
    {
        return in_array($this->role, ['board', 'super_admin']);
    }
}
