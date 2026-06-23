<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Condition extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'slug'];

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}
