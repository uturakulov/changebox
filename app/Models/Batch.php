<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 *
 * @property string $id
 * @property string $status
 * @property int $size
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static Builder<static>|Batch newModelQuery()
 * @method static Builder<static>|Batch newQuery()
 * @method static Builder<static>|Batch query()
 */
class Batch extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'id',
        'status',
        'size',
    ];

    /**
     * Связь с транзакциями
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'batch_id');
    }
}
