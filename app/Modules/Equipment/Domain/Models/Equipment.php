<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Domain\Models;

use App\Modules\Equipment\Domain\Enums\EquipmentStatusEnum;
use App\Modules\Equipment\Domain\ValueObjects\Id;
use App\Modules\Equipment\Domain\ValueObjects\Name;
use DateTimeImmutable;

class Equipment
{
    public Id $uid;
    public Name $name;
    public EquipmentStatusEnum $status;
    public ?string $transportUid = null;
    public ?string $warehouse = null;
    public ?Id $issuedToUid = null;
    public ?string $purchaseInvoiceUid = null;
    public ?string $supplierUid = null;
    public ?string $issueDocUid = null;
    public ?DateTimeImmutable $mountingDate = null;
    public ?string $shipmentInvoiceUid = null;
    public ?string $customerUid = null;
    public ?DateTimeImmutable $skziFrom = null;
    public ?DateTimeImmutable $skziTo = null;
    public DateTimeImmutable $createdAt;
    public ?Id $creatorUid = null;
    public ?DateTimeImmutable $updatedAt = null;
    public ?Id $updatedByUid = null;

    public function __construct(
        Name $name,
        EquipmentStatusEnum $status,
        ?Id $creatorUid = null,
        ?Id $uid = null
    ) {
        $this->name = $name;
        $this->status = $status;
        $this->uid = $uid ?? Id::next();
        $this->creatorUid = $creatorUid;
        $this->createdAt = new DateTimeImmutable;
    }

    public function changeStatus(EquipmentStatusEnum $newStatus, ?Id $author = null): void
    {
        $this->status = $newStatus;
        $this->touch($author);
    }

    public function touch(?Id $updaterUid = null): void
    {
        $this->updatedAt = new DateTimeImmutable;
        $this->updatedByUid = $updaterUid;
    }
}
