<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $batch_id
 * @property string $type
 * @property string $status
 * @property string $amount_from
 * @property string $currency_from
 * @property string|null $amount_to
 * @property string|null $currency_to
 * @property string|null $rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Batch|null $batch
 * @method static Builder<static>|Transaction newModelQuery()
 * @method static Builder<static>|Transaction newQuery()
 * @method static Builder<static>|Transaction query()
 */
class Transaction extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'batch_id',
        'type',
        'status',
        'amount_from',
        'currency_from',
        'amount_to',
        'currency_to',
        'rate',
    ];

    /**
     * Связь с батчем
     *
     * @return BelongsTo
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
}
