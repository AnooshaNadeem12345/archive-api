<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'email',
        'name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the items uploaded by the user.
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'uploader_id');
    }

    /**
     * Get the collections owned by the user.
     */
    public function collections()
    {
        return $this->hasMany(Collection::class, 'owner_id');
    }
}
