<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 
 *
 * @property-read Model|\Eloquent $receiver
 * @property-read Model|\Eloquent $sender
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @mixin \Eloquent
 */
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
