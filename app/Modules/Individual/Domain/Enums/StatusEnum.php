<?php

namespace App\Modules\Individual\Domain\Enums;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
}
