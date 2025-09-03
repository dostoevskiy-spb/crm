<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Models;

use App\Modules\Individual\Domain\ValueObjects\Id as IndividualId;
use App\Modules\Product\Domain\ValueObjects\Id;
use App\Modules\Product\Domain\ValueObjects\ProductName;
use App\Modules\Product\Domain\ValueObjects\ProductPrice;
use App\Modules\Product\Domain\ValueObjects\ProductStatus;
use App\Modules\Product\Domain\ValueObjects\ProductType;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

class Product
{
    private Id $uid;

    #[ORM\Embedded(class: ProductName::class, columnPrefix: false)]
    private ProductName $name;

    #[ORM\Column(name: 'status', type: 'string', length: 16)]
    private string $status;

    #[ORM\Column(name: 'type', type: 'string', length: 16)]
    private string $type;

    #[ORM\Embedded(class: UnitOfMeasure::class, columnPrefix: false)]
    private UnitOfMeasure $unit;

    #[ORM\Column(name: 'group_name', type: 'string', length: 50, nullable: true)]
    private ?string $groupName = null;

    #[ORM\Column(name: 'subgroup_name', type: 'string', length: 50, nullable: true)]
    private ?string $subgroupName = null;

    #[ORM\Column(name: 'code_1c', type: 'string', length: 50, nullable: true, unique: false)]
    private ?string $code1c = null;

    #[ORM\Column(name: 'sku', type: 'string', length: 50, unique: true)]
    private string $skuValue;

    #[ORM\Column(name: 'sale_price', type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $salePrice = null;

    #[ORM\Column(name: 'avg_purchase_cost_year', type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $avgPurchaseCostYear = null;

    #[ORM\Column(name: 'last_purchase_cost', type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $lastPurchaseCost = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'creator_uid', type: 'guid', nullable: true)]
    private ?string $creatorUid = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'updated_by_uid', type: 'guid', nullable: true)]
    private ?string $updatedByUid = null;

    public function __construct(
        ProductName $name,
        ProductStatus $status,
        ProductType $type,
        UnitOfMeasure $unit,
        Sku $sku,
        ?IndividualId $creatorUid = null,
        ?Id $uid = null
    ) {
        $this->uid = $uid ?? Id::next();
        $this->name = $name;
        $this->status = $status->value();
        $this->type = $type->value();
        $this->unit = $unit;
        $this->skuValue = $sku->value();
        $this->creatorUid = $creatorUid?->value();
        $this->createdAt = new DateTimeImmutable;
    }

    public function uid(): IndividualId
    {
        return new IndividualId($this->uid);
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function status(): ProductStatus
    {
        return new ProductStatus($this->status);
    }

    public function type(): ProductType
    {
        return new ProductType($this->type);
    }

    public function unit(): UnitOfMeasure
    {
        return $this->unit;
    }

    public function groupName(): ?string
    {
        return $this->groupName;
    }

    public function subgroupName(): ?string
    {
        return $this->subgroupName;
    }

    public function code1c(): ?string
    {
        return $this->code1c;
    }

    public function sku(): Sku
    {
        return new Sku($this->skuValue);
    }

    public function salePrice(): ?ProductPrice
    {
        return $this->salePrice !== null ? new ProductPrice($this->salePrice) : null;
    }

    public function avgPurchaseCostYear(): ?ProductPrice
    {
        return $this->avgPurchaseCostYear !== null ? new ProductPrice($this->avgPurchaseCostYear) : null;
    }

    public function lastPurchaseCost(): ?ProductPrice
    {
        return $this->lastPurchaseCost !== null ? new ProductPrice($this->lastPurchaseCost) : null;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function creatorUid(): ?IndividualId
    {
        return $this->creatorUid ? new IndividualId($this->creatorUid) : null;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updatedByUid(): ?IndividualId
    {
        return $this->updatedByUid ? new IndividualId($this->updatedByUid) : null;
    }

    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function setSubgroupName(?string $subgroupName): void
    {
        $this->subgroupName = $subgroupName;
    }

    public function setCode1c(?string $code1c): void
    {
        $this->code1c = $code1c;
    }

    public function setSalePrice(?ProductPrice $price): void
    {
        $this->salePrice = $price?->value();
    }

    public function setAvgPurchaseCostYear(?ProductPrice $price): void
    {
        $this->avgPurchaseCostYear = $price?->value();
    }

    public function setLastPurchaseCost(?ProductPrice $price): void
    {
        $this->lastPurchaseCost = $price?->value();
    }

    public function setStatus(ProductStatus $status): void
    {
        $this->status = $status->value();
    }

    public function setType(ProductType $type): void
    {
        $this->type = $type->value();
    }

    public function setUnit(UnitOfMeasure $unit): void
    {
        $this->unit = $unit;
    }

    public function touch(?IndividualId $updaterUid = null): void
    {
        $this->updatedAt = new DateTimeImmutable;
        $this->updatedByUid = $updaterUid?->value();
    }
}
