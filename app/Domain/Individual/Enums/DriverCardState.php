<?php

declare(strict_types=1);

namespace App\Domain\Individual\Enums;

enum DriverCardState: string
{
    case Green = 'green';   // valid for a long term
    case Yellow = 'yellow'; // expiring within ~3 months
    case Red = 'red';       // expired
}
