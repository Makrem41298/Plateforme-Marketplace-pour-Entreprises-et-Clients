<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Offre extends Pivot
{
    public $incrementing=true;
    public $table = 'offres';
    public function contrat():HasOne{
        return $this->hasOne(Contrat::class);
    }

}
