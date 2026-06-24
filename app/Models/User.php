<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'phone_verified_at',
        'password',
        'avatar_path',
        'bio',
        'is_verified',
        'verification_level',
        'status',
        'is_admin',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null
            && in_array($this->verification_level, ['phone', 'id_document'], true);
    }

    public function hasVerifiedIdentity(): bool
    {
        return $this->verification_level === 'id_document';
    }

    public function identityVerificationStatus(): string
    {
        if ($this->hasVerifiedIdentity()) {
            return 'approved';
        }

        if ($this->identityVerifications()->where('status', 'pending')->exists()) {
            return 'pending';
        }

        return 'none';
    }

    /** Señales de confianza (enfoque A: badges + reseñas, sin puntos). */
    public function trustSummary(): array
    {
        return [
            'email_verified' => $this->hasVerifiedEmail(),
            'phone_verified' => $this->hasVerifiedPhone(),
            'identity_verified' => $this->hasVerifiedIdentity(),
            'identity_status' => $this->identityVerificationStatus(),
            'rating_avg' => (float) ($this->profile?->rating_avg ?? 0),
            'ratings_count' => (int) ($this->profile?->ratings_count ?? 0),
            'sales_count' => (int) ($this->profile?->sales_count ?? 0),
            'purchases_count' => (int) ($this->profile?->purchases_count ?? 0),
        ];
    }

    public function unreadMessagesCount(): int
    {
        return Message::query()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $this->id)
            ->whereHas('conversation', function ($query) {
                $query->where('buyer_id', $this->id)
                    ->orWhere('seller_id', $this->id);
            })
            ->count();
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin && $this->status === 'active';
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id')
            ->orWhere('seller_id', $this->id);
    }

    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function identityVerifications(): HasMany
    {
        return $this->hasMany(IdentityVerification::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }
}
