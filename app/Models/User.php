<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Yebor974\Filament\RenewPassword\Contracts\RenewPasswordContract;
use Yebor974\Filament\RenewPassword\Traits\RenewPassword;

class User extends Authenticatable implements FilamentUser, HasTenants, RenewPasswordContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, RenewPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'force_renew_password',
        'renew_password_at',
        'is_system_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'is_system_admin' => 'boolean',
            'force_renew_password' => 'boolean',
        ];
    }

    public function organizers()
    {
        return $this->belongsToMany(Organizer::class);
    }

    public function organizer()
    {
        return $this->belongsToMany(Organizer::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function discountUsers()
    {
        return $this->hasMany(DiscountUser::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // return $panel->auth()->user()->role === 'admin';
        return true;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->organizers()->get();
    }

    public function canAccessTenant(\Illuminate\Database\Eloquent\Model $tenant): bool
    {
        return $this->organizers()->whereKey($tenant)->exists();
    }
}
