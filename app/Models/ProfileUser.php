<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $date_of_birth
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileUser whereUserId($value)
 * @mixin \Eloquent
 */
class ProfileUser extends Model
{
    protected $guarded=[];
    protected $table='user_profiles';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }}
