<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    protected $guarded=[];
public function sender()
{
    return $this->morphTo();
}

    public function receiver()
    {
        return $this->morphTo();
    }
}
