<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileUser extends Model
{
    protected $guarded=[];
    protected $table='user_profiles';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }}
