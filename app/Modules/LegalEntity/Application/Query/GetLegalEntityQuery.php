<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Application\Query;

final readonly class GetLegalEntityQuery
{
    public function __construct(public string $uid) {}
}
