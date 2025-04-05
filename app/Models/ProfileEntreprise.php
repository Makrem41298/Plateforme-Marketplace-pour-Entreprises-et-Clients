<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEntreprise extends Model
{
    protected $guarded=[];
    protected $table='entreprise_profiles';

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);

    }
}
