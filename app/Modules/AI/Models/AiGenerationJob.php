<?php

namespace App\Modules\AI\Models;

use App\Modules\AI\Enums\GenerationJobKind;
use App\Modules\AI\Enums\GenerationJobStatus;
use App\Modules\Content\Models\ContentVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationJob extends Model
{
    protected $fillable = [
        'kind',
        'input',
        'status',
        'result_version_id',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'kind' => GenerationJobKind::class,
            'input' => 'array',
            'status' => GenerationJobStatus::class,
        ];
    }

    public function resultVersion(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class, 'result_version_id');
    }

    public function nodeId(): ?int
    {
        $nodeId = $this->input['node_id'] ?? null;

        return is_int($nodeId) ? $nodeId : (is_numeric($nodeId) ? (int) $nodeId : null);
    }
}
