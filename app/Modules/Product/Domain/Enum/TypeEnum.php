<?php

namespace App\Modules\Product\Domain\Enum;

enum TypeEnum: string
{
    case ITEM = 'item';
    case SERVICE = 'service';

    public static function item(): self
    {
        return self::ITEM;
    }

    public static function service(): self
    {
        return self::SERVICE;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isItem(): bool
    {
        return $this === self::ITEM;
    }

    public function isService(): bool
    {
        return $this === self::SERVICE;
    }
}
