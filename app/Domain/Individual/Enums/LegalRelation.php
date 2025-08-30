<?php

declare(strict_types=1);

namespace App\Domain\Individual\Enums;

enum LegalRelation: string
{
    case Referred = 'referred'; // «Привел»
    case Employee = 'employee'; // «Сотрудник»
}
