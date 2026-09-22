<?php

namespace App\Modules\Gamification\Models;

use App\Models\User;
use App\Modules\Gamification\Enums\XpSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpLedgerEntry extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'xp_ledger';

    protected $fillable = [
        'user_id',
        'source',
        'amount',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'source' => XpSource::class,
            'amount' => 'integer',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
