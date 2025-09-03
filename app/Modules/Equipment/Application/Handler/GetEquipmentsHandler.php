<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Application\Handler;

use App\Modules\Equipment\Application\Query\GetEquipmentsQuery;
use App\Modules\Equipment\Domain\Contracts\EquipmentRepositoryInterface;

final readonly class GetEquipmentsHandler
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
        }, $items->toArray());
    }
}
