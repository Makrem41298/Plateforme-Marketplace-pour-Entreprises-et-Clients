<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    protected $guarded=[];
    public function messageable(): MorphTo
    {
        return $this->morphTo();
    }
}
