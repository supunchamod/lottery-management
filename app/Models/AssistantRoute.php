<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistantRoute extends Model
{
    protected $fillable = ['name', 'color_code'];

    public function assistants(): HasMany
    {
        return $this->hasMany(SalesAssistant::class, 'route_id');
    }
}
