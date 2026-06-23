<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Listing extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'condition_id',
        'location_id',
        'brand_id',
        'title',
        'slug',
        'description',
        'price',
        'size',
        'size_note',
        'color',
        'listing_mode',
        'listing_type',
        'items_count',
        'is_negotiable',
        'currency',
        'status',
        'views_count',
        'favorites_count',
        'published_at',
        'sold_at',
    ];

    protected $appends = [
        'price_formatted',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'published_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('position');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ListingImage::class)->where('is_primary', true);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ListingAttribute::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function getPriceFormattedAttribute(): string
    {
        return '$'.number_format((float) $this->price, 0, ',', '.');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function universes(): BelongsToMany
    {
        return $this->belongsToMany(Universe::class, 'listing_universe')->withTimestamps();
    }

    public function lotItems(): HasMany
    {
        return $this->hasMany(LotItem::class)->orderBy('position');
    }

    public function isLote(): bool
    {
        return $this->listing_type === 'lote';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'status' => $this->status,
            'category' => $this->category?->name,
            'condition' => $this->condition?->name,
            'city' => $this->location?->city,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active';
    }
}
