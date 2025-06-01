<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transcation extends Model
{
    protected $guarded=[];
    public function contrat():BelongsTo
    {
        return $this->belongsTo(Contrat::class,'contrat_id');
    }
}
