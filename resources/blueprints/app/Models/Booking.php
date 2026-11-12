<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'object_title',
        'event_date',
        'payment',
        'status',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public const STATUSES = [
        'new' => '@@STATUS_NEW@@',
        'assigned' => '@@STATUS_ASSIGNED@@',
        'done' => '@@STATUS_DONE@@',
    ];

    public const PAYMENTS = [
        'onsite' => '@@PAY1@@',
        'sbp' => '@@PAY2@@',
    ];

    /**
     * Только вперёд: new → assigned → done.
     * Шаг назад и прыжок через статус запрещены.
     */
    public const TRANSITIONS = [
        'new' => ['assigned'],
        'assigned' => ['done'],
        'done' => [],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function paymentLabel(): string
    {
        return self::PAYMENTS[$this->payment] ?? $this->payment;
    }

    public function canMoveTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function canBeReviewed(): bool
    {
        return $this->isDone() && $this->review === null;
    }
}
