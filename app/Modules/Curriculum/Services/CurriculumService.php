<?php

namespace App\Modules\Curriculum\Services;

use App\Models\User;
use App\Modules\Curriculum\Contracts\CurriculumRouteReaderInterface;
use App\Modules\Curriculum\Contracts\GraphRepositoryInterface;
use App\Modules\Curriculum\Contracts\NodeRepositoryInterface;
use App\Modules\Curriculum\DTO\Output\NodeData;
use App\Modules\Curriculum\DTO\Output\NodeListData;
use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\Curriculum\Exceptions\CurriculumException;
use App\Modules\Curriculum\Models\KnowledgeNode;
use App\Modules\Shared\Services\PrimaryTrackResolver;

class CurriculumService implements CurriculumRouteReaderInterface
{
    public function __construct(
        private readonly NodeRepositoryInterface $nodes,
        private readonly GraphRepositoryInterface $graph,
        private readonly EntryNodeSelector $entryNodeSelector,
        private readonly RouteExpander $routeExpander,
        private readonly PrimaryTrackResolver $primaryTrack,
    ) {}

    public function listNodes(?Track $track = null): NodeListData
    {
        return NodeListData::fromModels($this->nodes->listPublished($track));
    }

    public function getNode(string $slug): NodeData
    {
        return NodeData::fromModel($this->findPublishedNode($slug));
    }

    public function prerequisites(string $slug, bool $transitive = true): array
    {
        $node = $this->findPublishedNode($slug);

        return array_map(
            fn (KnowledgeNode $item) => NodeData::fromModel($item),
            $this->graph->prerequisites($node->id, $transitive),
        );
    }

    public function related(string $slug): array
    {
        $node = $this->findPublishedNode($slug);

        return array_map(
            fn (KnowledgeNode $item) => NodeData::fromModel($item),
            $this->graph->related($node->id),
        );
    }

    public function entryNodes(User $user): array
    {
        return array_map(
            fn (KnowledgeNode $item) => NodeData::fromModel($item),
            $this->entryNodeSelector->suggest($user),
        );
    }

    public function expandRoute(User $user): NodeListData
    {
        return $this->expandRouteForTrack(
            $user,
            $this->primaryTrack->resolve($user),
        );
    }

    public function expandRouteForTrack(User $user, Track $track): NodeListData
    {
        $entry = $this->entryNodeSelector->entryNodeForTrack($user, $track);

        if ($entry === null) {
            return NodeListData::fromModels([]);
        }

        $reachable = $this->graph->reachableFromMany([$entry->id]);

        return NodeListData::fromModels(
            $this->routeExpander->order($reachable),
        );
    }

    private function findPublishedNode(string $slug): KnowledgeNode
    {
        $node = $this->nodes->findBySlug($slug)
            ?? throw CurriculumException::nodeNotFound($slug);

        if ($node->status !== NodeStatus::Published) {
            throw CurriculumException::nodeNotFound($slug);
        }

        return $node;
    }
}
