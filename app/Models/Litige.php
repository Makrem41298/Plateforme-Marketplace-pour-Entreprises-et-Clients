<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Litige extends Model
{
    protected $guarded=[];
    public function contrat():BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }
}
