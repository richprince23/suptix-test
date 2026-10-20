<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Str;

class Organizer extends Model implements HasAvatar
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'description',
        'website',
        'logo',
        'banner',
    ];

    protected $appends = [
        'logo_url',
        'banner_url',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo_url;
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    public function discountUsers()
    {
        return $this->hasMany(DiscountUser::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function reservationTypes()
    {
        return $this->hasMany(ReservationType::class);
    }

    public function paymentInfo()
    {
        return $this->hasOne(PaymentInfo::class);
    }

    public function paymentInfos()
    {
        return $this->hasOne(PaymentInfo::class);
    }

    public function organizerSubscriptions(){
        return $this->hasMany(OrganizerSubscription::class);
    }

    // Get the organizer's logo URL using proper Laravel Storage methods
    public function getLogoUrlAttribute()
    {
        if (! $this->logo) {
            return null;
        }

        // Use the default filesystem disk which Laravel Cloud automatically configures
        return Storage::disk(config('filesystems.default'))->url($this->logo);
    }

    // Get the organizer's banner URL using proper Laravel Storage methods
    public function getBannerUrlAttribute()
    {
        if (! $this->banner) {
            return null;
        }

        // Use the default filesystem disk which Laravel Cloud automatically configures
        return Storage::disk(config('filesystems.default'))->url($this->banner);
    }

    //    public function addresses(){
    //        return $this->morphMany(Address::class, 'addressable');
    //    }

    public function subscriptions()
    {
        return $this->hasMany(OrganizerSubscription::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($organizer) {
            $organizer->slug = Str::slug($organizer->name).'-'.Str::random(6);
        });

        static::updating(function ($model) {
            // Delete old logo if it's being updated
            if ($model->isDirty('logo')) {
                $oldLogo = $model->getOriginal('logo');
                if ($oldLogo && $oldLogo !== $model->logo) {
                    Storage::disk(config('filesystems.default'))->delete($oldLogo);
                }
            }

            // Delete old banner if it's being updated
            if ($model->isDirty('banner')) {
                $oldBanner = $model->getOriginal('banner');
                if ($oldBanner && $oldBanner !== $model->banner) {
                    Storage::disk(config('filesystems.default'))->delete($oldBanner);
                }
            }
        });

        static::deleted(function ($model) {
            // Delete logo when organizer is deleted
            if ($model->logo) {
                Storage::disk(config('filesystems.default'))->delete($model->logo);
            }

            // Delete banner when organizer is deleted
            if ($model->banner) {
                Storage::disk(config('filesystems.default'))->delete($model->banner);
            }
        });
    }
}
