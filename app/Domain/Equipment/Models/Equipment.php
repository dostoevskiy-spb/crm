<?php

declare(strict_types=1);

namespace App\Domain\Equipment\Models;

use App\Domain\Equipment\ValueObjects\EquipmentName;
use App\Domain\Equipment\ValueObjects\EquipmentStatus;
use App\Domain\Equipment\ValueObjects\EquipmentUid;
use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\LegalEntity\ValueObjects\LegalEntityUid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'equipment')]
class Equipment
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $uid;

    #[ORM\Embedded(class: EquipmentName::class, columnPrefix: false)]
    private EquipmentName $name;

    #[ORM\Column(name: 'status', type: 'string', length: 20)]
    private string $status;

    // Transport (external aggregate UID)
    #[ORM\Column(name: 'transport_uid', type: 'guid', nullable: true)]
    private ?string $transportUid = null;

    // Warehouse dictionary name
    #[ORM\Column(name: 'warehouse', type: 'string', length: 100, nullable: true)]
    private ?string $warehouse = null;

    // Issued to a person (Individual)
    #[ORM\Column(name: 'issued_to_uid', type: 'guid', nullable: true)]
    private ?string $issuedToUid = null;

    #[ORM\Column(name: 'prev_status', type: 'string', length: 20, nullable: true)]
    private ?string $previousStatus = null;

    // Documents and relations
    #[ORM\Column(name: 'purchase_invoice_uid', type: 'guid', nullable: true)]
    private ?string $purchaseInvoiceUid = null;

    #[ORM\Column(name: 'supplier_uid', type: 'guid', nullable: true)]
    private ?string $supplierUid = null;

    #[ORM\Column(name: 'issue_doc_uid', type: 'guid', nullable: true)]
    private ?string $issueDocUid = null;

    #[ORM\Column(name: 'mounting_date', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $mountingDate = null;

    #[ORM\Column(name: 'shipment_invoice_uid', type: 'guid', nullable: true)]
    private ?string $shipmentInvoiceUid = null;

    #[ORM\Column(name: 'customer_uid', type: 'guid', nullable: true)]
    private ?string $customerUid = null;

    // SKZI
    #[ORM\Column(name: 'skzi_from', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $skziFrom = null;

    #[ORM\Column(name: 'skzi_to', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $skziTo = null;

    // Audit
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'creator_uid', type: 'guid', nullable: true)]
    private ?string $creatorUid = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'updated_by_uid', type: 'guid', nullable: true)]
    private ?string $updatedByUid = null;

    public function __construct(
        EquipmentName $name,
        EquipmentStatus $status,
        ?PersonUid $creatorUid = null,
        ?EquipmentUid $uid = null
    ) {
        $this->name = $name;
        $this->status = $status->value();
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

    // Getters
    public function uid(): EquipmentUid { return new EquipmentUid($this->uid); }
    public function name(): EquipmentName { return $this->name; }
    public function status(): EquipmentStatus { return new EquipmentStatus($this->status); }
    public function previousStatus(): ?EquipmentStatus { return $this->previousStatus ? new EquipmentStatus($this->previousStatus) : null; }
    public function transportUid(): ?string { return $this->transportUid; }
    public function warehouse(): ?string { return $this->warehouse; }
    public function issuedToUid(): ?PersonUid { return $this->issuedToUid ? new PersonUid($this->issuedToUid) : null; }
    public function purchaseInvoiceUid(): ?string { return $this->purchaseInvoiceUid; }
    public function supplierUid(): ?LegalEntityUid { return $this->supplierUid ? new LegalEntityUid($this->supplierUid) : null; }
    public function issueDocUid(): ?string { return $this->issueDocUid; }
    public function mountingDate(): ?\DateTimeImmutable { return $this->mountingDate; }
    public function shipmentInvoiceUid(): ?string { return $this->shipmentInvoiceUid; }
    public function customerUid(): ?LegalEntityUid { return $this->customerUid ? new LegalEntityUid($this->customerUid) : null; }
    public function skziFrom(): ?\DateTimeImmutable { return $this->skziFrom; }
    public function skziTo(): ?\DateTimeImmutable { return $this->skziTo; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function creatorUid(): ?PersonUid { return $this->creatorUid ? new PersonUid($this->creatorUid) : null; }
    public function updatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function updatedByUid(): ?PersonUid { return $this->updatedByUid ? new PersonUid($this->updatedByUid) : null; }

    // Mutators
    public function setTransportUid(?string $uid): void { $this->transportUid = $uid !== null ? trim($uid) : null; }
    public function setWarehouse(?string $warehouse): void { $this->warehouse = $warehouse !== null ? trim($warehouse) : null; }
    public function setIssuedToUid(?PersonUid $uid): void { $this->issuedToUid = $uid?->value(); }
    public function setPurchaseInvoiceUid(?string $uid): void { $this->purchaseInvoiceUid = $uid; }
    public function setSupplierUid(?LegalEntityUid $uid): void { $this->supplierUid = $uid?->value(); }
    public function setIssueDocUid(?string $uid): void { $this->issueDocUid = $uid; }
    public function setMountingDate(?\DateTimeImmutable $date): void { $this->mountingDate = $date; }
    public function setShipmentInvoiceUid(?string $uid): void { $this->shipmentInvoiceUid = $uid; }
    public function setCustomerUid(?LegalEntityUid $uid): void { $this->customerUid = $uid?->value(); }
    public function setSkziFrom(?\DateTimeImmutable $date): void { $this->skziFrom = $date; }
    public function setSkziTo(?\DateTimeImmutable $date): void { $this->skziTo = $date; }

    public function changeStatus(EquipmentStatus $newStatus, ?PersonUid $author = null): void
    {
        $this->previousStatus = $this->status;
        $this->status = $newStatus->value();
        $this->touch($author);
    }

    public function touch(?PersonUid $updaterUid = null): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->updatedByUid = $updaterUid?->value();
    }
}
