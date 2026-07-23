<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    /**
     * Get the user who owns the collection.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The items in the collection.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_collection')
                    ->withTimestamps();
    }
}
