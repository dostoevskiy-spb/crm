<?php

declare(strict_types=1);

namespace App\Domain\Equipment\ValueObjects;

final class EquipmentStatus
{
    public const WAREHOUSE = 'warehouse'; // На складе
    public const ISSUED = 'issued';       // Выдано под отчет
    public const INSTALLED = 'installed'; // Установлено
    public const SOLD = 'sold';           // Продано
    public const RECLAMATION = 'reclamation'; // Рекламация
    public const UTIL = 'util';           // Утиль
    public const CUSTOMER = 'customer';   // У заказчика

    private string $value;

    public function __construct(string $value)
    {
        $v = strtolower(trim($value));
        if (!in_array($v, self::all(), true)) {
            throw new \InvalidArgumentException('Invalid equipment status');
        }
        $this->value = $v;
    }

    public static function all(): array
    {
        return [
            self::WAREHOUSE,
            self::ISSUED,
            self::INSTALLED,
            self::SOLD,
            self::RECLAMATION,
            self::UTIL,
            self::CUSTOMER,
        ];
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
