<?php

declare(strict_types=1);

namespace App\Domain\Product\Models;

use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\Product\ValueObjects\ProductName;
use App\Domain\Product\ValueObjects\ProductPrice;
use App\Domain\Product\ValueObjects\ProductStatus;
use App\Domain\Product\ValueObjects\ProductType;
use App\Domain\Product\ValueObjects\ProductUid;
use App\Domain\Product\ValueObjects\Sku;
use App\Domain\Product\ValueObjects\UnitOfMeasure;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $uid;

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
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'creator_uid', type: 'guid', nullable: true)]
    private ?string $creatorUid = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'updated_by_uid', type: 'guid', nullable: true)]
    private ?string $updatedByUid = null;

    public function __construct(
        ProductName $name,
        ProductStatus $status,
        ProductType $type,
        UnitOfMeasure $unit,
        Sku $sku,
        ?PersonUid $creatorUid = null,
        ?ProductUid $uid = null
    ) {
        $this->name = $name;
        $this->status = $status->value();
        $this->type = $type->value();
        $this->unit = $unit;
        $this->skuValue = $sku->value();
        $this->creatorUid = $creatorUid?->value();
        $this->createdAt = new \DateTimeImmutable();
        $this->uid = $uid?->value() ?? self::generateUuid();
    }

    private static function generateUuid(): string
    {
        if (function_exists('uuid_create')) {
            return (string) uuid_create(UUID_TYPE_RANDOM);
        }
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function uid(): ProductUid { return new ProductUid($this->uid); }
    public function name(): ProductName { return $this->name; }
    public function status(): ProductStatus { return new ProductStatus($this->status); }
    public function type(): ProductType { return new ProductType($this->type); }
    public function unit(): UnitOfMeasure { return $this->unit; }

    public function groupName(): ?string { return $this->groupName; }
    public function subgroupName(): ?string { return $this->subgroupName; }
    public function code1c(): ?string { return $this->code1c; }

    public function sku(): Sku { return new Sku($this->skuValue); }

    public function salePrice(): ?ProductPrice { return $this->salePrice !== null ? new ProductPrice($this->salePrice) : null; }
    public function avgPurchaseCostYear(): ?ProductPrice { return $this->avgPurchaseCostYear !== null ? new ProductPrice($this->avgPurchaseCostYear) : null; }
    public function lastPurchaseCost(): ?ProductPrice { return $this->lastPurchaseCost !== null ? new ProductPrice($this->lastPurchaseCost) : null; }

    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function creatorUid(): ?PersonUid { return $this->creatorUid ? new PersonUid($this->creatorUid) : null; }
    public function updatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function updatedByUid(): ?PersonUid { return $this->updatedByUid ? new PersonUid($this->updatedByUid) : null; }

    public function setGroupName(?string $groupName): void { $this->groupName = $groupName; }
    public function setSubgroupName(?string $subgroupName): void { $this->subgroupName = $subgroupName; }
    public function setCode1c(?string $code1c): void { $this->code1c = $code1c; }

    public function setSalePrice(?ProductPrice $price): void { $this->salePrice = $price?->value(); }
    public function setAvgPurchaseCostYear(?ProductPrice $price): void { $this->avgPurchaseCostYear = $price?->value(); }
    public function setLastPurchaseCost(?ProductPrice $price): void { $this->lastPurchaseCost = $price?->value(); }

    public function setStatus(ProductStatus $status): void { $this->status = $status->value(); }
    public function setType(ProductType $type): void { $this->type = $type->value(); }
    public function setUnit(UnitOfMeasure $unit): void { $this->unit = $unit; }

    public function touch(?PersonUid $updaterUid = null): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedByUid = $updaterUid?->value();
    }
}
