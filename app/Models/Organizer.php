<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'rsvp_notification_frequency',
    ];

    protected $appends = [
        'logo_url',
        'banner_url',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo_url;
    }
    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function creditAccounts()
    {
        return $this->hasMany(EventCreditAccount::class);
    }

    /** @return HasMany<Ticket, $this> */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<TicketType, $this> */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    /** @return HasMany<Purchase, $this> */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<Discount, $this> */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    /** @return HasMany<DiscountUser, $this> */
    public function discountUsers(): HasMany
    {
        return $this->hasMany(DiscountUser::class);
    }

    /** @return HasMany<Reservation, $this> */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /** @return HasMany<ReservationType, $this> */
    public function reservationTypes(): HasMany
    {
        return $this->hasMany(ReservationType::class);
    }

    /** @return HasOne<PaymentInfo, $this> */
    public function paymentInfo(): HasOne
    {
        return $this->hasOne(PaymentInfo::class);
    }

    /** @return HasOne<PaymentInfo, $this> */
    public function paymentInfos(): HasOne
    {
        return $this->hasOne(PaymentInfo::class);
    }

    public function organizerSubscriptions(){
        return $this->hasMany(OrganizerSubscription::class);
    }

    public function eventReports(){
        return $this->hasMany(EventReport::class);
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

    public function rsvps()
    {
        return $this->hasMany(RSVP::class);
    }

    public function checkins()
    {
        return $this->hasMany(RSVP::class);
    }

    //    public function addresses(){
    //        return $this->morphMany(Address::class, 'addressable');
    //    }

    /** @return HasMany<OrganizerSubscription, $this> */
    public function subscriptions(): HasMany
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
