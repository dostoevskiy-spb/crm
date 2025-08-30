<?php

declare(strict_types=1);

namespace App\Application\LegalEntity\Query;

final readonly class GetLegalEntityQuery
{
    public function __construct(public string $uid) {}
}
