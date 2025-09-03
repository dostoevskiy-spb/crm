<?php

declare(strict_types=1);

namespace App\Modules\Individual\Domain\Enums;

enum LegalRelation: string
{
    case Referred = 'referred'; // «Привел»
    case Employee = 'employee'; // «Сотрудник»
}
