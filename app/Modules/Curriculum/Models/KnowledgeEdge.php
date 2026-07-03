<?php

namespace App\Modules\Curriculum\Models;

use App\Modules\Curriculum\Enums\EdgeKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeEdge extends Model
{
    protected $table = 'knowledge_edges';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'from_node_id',
        'to_node_id',
        'kind',
    ];

    protected function casts(): array
    {
        return [
            'kind' => EdgeKind::class,
        ];
    }

    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(KnowledgeNode::class, 'from_node_id');
    }

    public function toNode(): BelongsTo
    {
        return $this->belongsTo(KnowledgeNode::class, 'to_node_id');
    }
}
