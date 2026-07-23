<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'file_url',
        'file_type',
        'uploader_id',
    ];

    /**
     * Get the user who uploaded the item.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * The collections that the item belongs to.
     */
    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'item_collection')
                    ->withTimestamps();
    }
}
