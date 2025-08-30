<?php

declare(strict_types=1);

namespace App\Application\Equipment\Handler;

use App\Application\Equipment\Query\GetEquipmentsQuery;
use App\Domain\Equipment\Contracts\EquipmentRepositoryInterface;

final class GetEquipmentsHandler
{
    public function __construct(
        private EquipmentRepositoryInterface $repository
    ) {}

    public function __invoke(GetEquipmentsQuery $query): array
    {
        $items = $this->repository->findByFilters($query->filters);

        return array_map(static function ($e) {
            return [
                'uid' => $e->uid()->value(),
                'name' => $e->name()->value(),
                'status' => $e->status()->value(),
                'transportUid' => $e->transportUid(),
                'warehouse' => $e->warehouse(),
                'issuedToUid' => $e->issuedToUid()?->value(),
                'createdAt' => $e->createdAt()->format('Y-m-d H:i:s'),
                'creatorUid' => $e->creatorUid()?->value(),
            ];
        }, $items);
    }
}
