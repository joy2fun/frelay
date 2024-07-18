<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Endpoint extends Model
{
    use HasFactory;

    public function targets(): HasMany
    {
        return $this->hasMany(EndpointTarget::class, 'endpoint_id', 'id');
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn() => sprintf("/api/endpoint/%s", $this->slug),
        );
    }

    public function titleAndUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => sprintf("/api/endpoint/%s - %s", $this->slug, $this->title),
        );
    }

    public function telescopeUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => sprintf("/%s/requests?tag=slug:%s", config('telescope.path'), $this->slug),
        );
    }
}
