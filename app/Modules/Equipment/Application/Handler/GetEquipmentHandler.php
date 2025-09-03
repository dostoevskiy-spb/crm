<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Application\Handler;

use App\Modules\Equipment\Application\Query\GetEquipmentQuery;
use App\Modules\Equipment\Domain\Contracts\EquipmentRepositoryInterface;
use App\Modules\Equipment\Domain\ValueObjects\Id;

final readonly class GetEquipmentHandler
{
    public function __construct(
        private EquipmentRepositoryInterface $repository
    ) {}

    public function __invoke(GetEquipmentQuery $query): ?array
    {
        $uid = new Id($query->uid);
        $e = $this->repository->findByUid($uid);
        if (! $e) {
            return null;
        }

        return [
            'uid' => $e->uid()->value(),
            'name' => $e->name()->value(),
            'status' => $e->status()->value(),
            'previousStatus' => $e->previousStatus()?->value(),
            'purchaseInvoiceUid' => $e->purchaseInvoiceUid(),
            'supplierUid' => $e->supplierUid()?->value(),
            'warehouse' => $e->warehouse(),
            'issuedToUid' => $e->issuedToUid()?->value(),
            'issueDocUid' => $e->issueDocUid(),
            'transportUid' => $e->transportUid(),
            'mountingDate' => $e->mountingDate()?->format('Y-m-d'),
            'shipmentInvoiceUid' => $e->shipmentInvoiceUid(),
            'customerUid' => $e->customerUid()?->value(),
            'skziFrom' => $e->skziFrom()?->format('Y-m-d'),
            'skziTo' => $e->skziTo()?->format('Y-m-d'),
            'createdAt' => $e->createdAt()->format('Y-m-d H:i:s'),
            'creatorUid' => $e->creatorUid()?->value(),
            'updatedAt' => $e->updatedAt()?->format('Y-m-d H:i:s'),
            'updatedByUid' => $e->updatedByUid()?->value(),
        ];
    }
}
