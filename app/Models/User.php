<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\StaffPasswordResetNotification;
use App\Services\MediaService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'department_id', 'employee_id', 'is_admin', 'status', 'phone', 'address', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->employee_id)) {
                $user->employee_id = generateUniqueId(self::class, 'EMP', 'employee_id');
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Use the branded reset email instead of the framework's default.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new StaffPasswordResetNotification($token));
    }

    /* ------------------------------------------------- navbar user menu */

    /**
     * Avatar shown in the navbar. Falls back to a generated initials image so
     * the menu never renders a broken picture.
     */
    public function adminlte_image(): string
    {
        if ($this->avatar) {
            return app(MediaService::class)->url($this->avatar, thumb: true);
        }

        return 'data:image/svg+xml;base64,'.base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">'
            .'<rect width="64" height="64" rx="32" fill="#4f46e5"/>'
            .'<text x="32" y="41" font-family="Segoe UI, sans-serif" font-size="24" font-weight="600"'
            .' fill="#ffffff" text-anchor="middle">'
            .htmlspecialchars(Str::upper(Str::substr($this->name ?? 'U', 0, 2)), ENT_XML1)
            .'</text></svg>'
        );
    }

    /**
     * Sub-line under the name in the navbar menu.
     */
    public function adminlte_desc(): string
    {
        if ($this->is_admin) {
            return 'Administrator';
        }

        return $this->roles->pluck('name')->implode(', ')
            ?: ($this->department?->name ?? 'Staff');
    }

    /**
     * The navbar template passes this through route(), so it must be a route
     * name rather than a URL.
     */
    public function adminlte_profile_url(): string
    {
        return 'admin.profile.edit';
    }
}
