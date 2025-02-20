<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *
 * @property string $id
 * @property string $user_id
 * @property string $currency
 * @property string $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Balance newModelQuery()
 * @method static Builder<static>|Balance newQuery()
 * @method static Builder<static>|Balance query()
 */
class Balance extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'currency',
        'amount',
    ];
}
