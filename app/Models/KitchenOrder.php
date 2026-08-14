<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_SERVED = 'served';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'company_id',
        'pos_transaction_id',
        'table_id',
        'order_number',
        'status',
        'priority',
        'note',
        'ordered_at',
        'started_at',
        'ready_at',
        'served_at',
        'created_by',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'started_at' => 'datetime',
        'ready_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function posTransaction()
    {
        return $this->belongsTo(PosTransaction::class, 'pos_transaction_id');
    }

    public function table()
    {
        return $this->belongsTo(ResTable::class, 'table_id');
    }

    public function items()
    {
        return $this->hasMany(KitchenOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Status helpers ────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return !in_array($this->status, [self::STATUS_SERVED, self::STATUS_CANCELLED], true);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPreparing(): bool
    {
        return $this->status === self::STATUS_PREPARING;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isServed(): bool
    {
        return $this->status === self::STATUS_SERVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isHighPriority(): bool
    {
        return $this->priority === self::PRIORITY_HIGH;
    }

    public function getTableLabel(): string
    {
        if ($this->table) {
            return $this->table->name;
        }

        if ($this->posTransaction?->serviceType?->isTakeaway()) {
            return 'Bawa Pulang';
        }

        return 'Meja -';
    }

    public function elapsedSeconds(): int
    {
        return max(0, $this->ordered_at?->diffInSeconds(now()) ?? 0);
    }
}
