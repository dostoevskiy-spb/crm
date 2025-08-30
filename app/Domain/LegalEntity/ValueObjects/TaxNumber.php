<?php

declare(strict_types=1);

namespace App\Domain\LegalEntity\ValueObjects;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class TaxNumber
{
    #[ORM\Column(name: 'ogrn', type: 'string', length: 13)]
    private string $ogrn;
    #[ORM\Column(name: 'inn', type: 'string', length: 10)]
    private string $inn;
    #[ORM\Column(name: 'kpp', type: 'string', length: 9)]
    private string $kpp;

    public function __construct(string $ogrn, string $inn, string $kpp)
    {
        $this->validateOgrn($ogrn);
        $this->validateInn($inn);
        $this->validateKpp($kpp);

        $this->ogrn = $ogrn;
        $this->inn = $inn;
        $this->kpp = $kpp;
    }

    public function ogrn(): string
    {
        return $this->ogrn;
    }

    public function inn(): string
    {
        return $this->inn;
    }

    public function kpp(): string
    {
        return $this->kpp;
    }

    private function validateOgrn(string $ogrn): void
    {
        if (!preg_match('/^\d{13}$/', $ogrn)) {
            throw new \InvalidArgumentException('OGRN must contain exactly 13 digits');
        }
    }

    private function validateInn(string $inn): void
    {
        if (!preg_match('/^\d{10}$/', $inn)) {
            throw new \InvalidArgumentException('INN must contain exactly 10 digits');
        }
    }

    private function validateKpp(string $kpp): void
    {
        if (!preg_match('/^\d{9}$/', $kpp)) {
            throw new \InvalidArgumentException('KPP must contain exactly 9 digits');
        }
    }
}
