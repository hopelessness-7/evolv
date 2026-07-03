<?php

namespace App\Modules\Practice\Models;

use App\Models\User;
use App\Modules\Practice\Enums\AttemptKind;
use App\Modules\Practice\Enums\AttemptVerdict;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attempt extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'node_id',
        'kind',
        'payload',
        'verdict',
        'error_tags',
        'duration_ms',
        'judge0_response',
    ];

    protected function casts(): array
    {
        return [
            'kind' => AttemptKind::class,
            'verdict' => AttemptVerdict::class,
            'payload' => 'array',
            'error_tags' => 'array',
            'judge0_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
