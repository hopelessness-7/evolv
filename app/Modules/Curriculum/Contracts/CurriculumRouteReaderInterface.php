<?php

namespace App\Modules\Curriculum\Contracts;

use App\Models\User;
use App\Modules\Curriculum\DTO\Output\NodeData;
use App\Modules\Curriculum\DTO\Output\NodeListData;
use App\Modules\Curriculum\Enums\Track;

interface CurriculumRouteReaderInterface
{
    public function listNodes(?Track $track = null): NodeListData;

    public function getNode(string $slug): NodeData;

    /**
     * @return list<NodeData>
     */
    public function prerequisites(string $slug, bool $transitive = true): array;

    /**
     * @return list<NodeData>
     */
    public function related(string $slug): array;

    /**
     * @return list<NodeData>
     */
    public function entryNodes(User $user): array;

    public function expandRoute(User $user): NodeListData;

    public function expandRouteForTrack(User $user, Track $track): NodeListData;
}
