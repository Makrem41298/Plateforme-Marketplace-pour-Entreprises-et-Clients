<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $entreprise_id
 * @property int|null $rating Note de 1 à 5
 * @property string|null $commentaire
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis whereCommentaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis whereEntrepriseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Avis whereUserId($value)
 * @mixin \Eloquent
 */
class Avis extends Pivot
{
    protected $table='avis';
}
