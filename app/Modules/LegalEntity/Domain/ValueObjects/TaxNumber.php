<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Domain\ValueObjects;

final class TaxNumber
{
    private string $ogrn;

    private string $inn;

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
        // Check format: 13 digits for OGRN or 15 digits for OGRNIP
        if (! preg_match('/^\d{13}$/', $ogrn) && ! preg_match('/^\d{15}$/', $ogrn)) {
            throw new \InvalidArgumentException('OGRN must contain exactly 13 digits, or OGRNIP must contain exactly 15 digits');
        }

        $length = strlen($ogrn);

        if ($length === 13) {
            // OGRN validation: last digit must equal (first 12 digits % 11) % 10
            $controlNumber = (int) substr($ogrn, -1);
            $baseNumber = substr($ogrn, 0, 12);
            $calculatedControl = ((int) bcmod($baseNumber, '11')) % 10;

            if ($controlNumber !== $calculatedControl) {
                throw new \InvalidArgumentException('Invalid OGRN: control digit check failed');
            }
        } elseif ($length === 15) {
            // OGRNIP validation: last digit must equal (first 14 digits % 13) % 10
            $controlNumber = (int) substr($ogrn, -1);
            $baseNumber = substr($ogrn, 0, 14);
            $calculatedControl = ((int) bcmod($baseNumber, '13')) % 10;

            if ($controlNumber !== $calculatedControl) {
                throw new \InvalidArgumentException('Invalid OGRNIP: control digit check failed');
            }
        }
    }

    private function validateInn(string $inn): void
    {
        // Check format: 10 digits for legal entities or 12 digits for individuals/IP
        if (! preg_match('/^\d{10}$/', $inn) && ! preg_match('/^\d{12}$/', $inn)) {
            throw new \InvalidArgumentException('INN must contain exactly 10 digits for legal entities or 12 digits for individuals/IP');
        }

        $length = strlen($inn);
        $digits = array_map('intval', str_split($inn));

        if ($length === 10) {
            // Legal entity INN validation (10 digits)
            $weights = [2, 4, 10, 3, 5, 9, 4, 6, 8];
            $checksum = 0;

            for ($i = 0; $i < 9; $i++) {
                $checksum += $digits[$i] * $weights[$i];
            }

            $calculatedControl = $checksum % 11;
            if ($calculatedControl > 9) {
                $calculatedControl = 0;
            }

            if ($digits[9] !== $calculatedControl) {
                throw new \InvalidArgumentException('Invalid INN: control digit check failed');
            }
        } elseif ($length === 12) {
            // Individual/IP INN validation (12 digits)
            // Check first control digit (11th position)
            $weights1 = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
            $checksum1 = 0;

            for ($i = 0; $i < 10; $i++) {
                $checksum1 += $digits[$i] * $weights1[$i];
            }

            $calculatedControl1 = $checksum1 % 11;
            if ($calculatedControl1 > 9) {
                $calculatedControl1 = 0;
            }

            if ($digits[10] !== $calculatedControl1) {
                throw new \InvalidArgumentException('Invalid INN: first control digit check failed');
            }

            // Check second control digit (12th position)
            $weights2 = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8];
            $checksum2 = 0;

            for ($i = 0; $i < 11; $i++) {
                $checksum2 += $digits[$i] * $weights2[$i];
            }

            $calculatedControl2 = $checksum2 % 11;
            if ($calculatedControl2 > 9) {
                $calculatedControl2 = 0;
            }

            if ($digits[11] !== $calculatedControl2) {
                throw new \InvalidArgumentException('Invalid INN: second control digit check failed');
            }
        }
    }

    private function validateKpp(string $kpp): void
    {
        if (! preg_match('/^\d{9}$/', $kpp)) {
            throw new \InvalidArgumentException('KPP must contain exactly 9 digits');
        }

        // Valid Russian region codes (01-99, excluding some)
        $validRegionCodes = [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
            '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
            '21', '22', '23', '24', '25', '26', '27', '28', '29', '30',
            '31', '32', '33', '34', '35', '36', '37', '38', '39', '40',
            '41', '42', '43', '44', '45', '46', '47', '48', '49', '50',
            '51', '52', '53', '54', '55', '56', '57', '58', '59', '60',
            '61', '62', '63', '64', '65', '66', '67', '68', '69', '70',
            '71', '72', '73', '74', '75', '76', '77', '78', '79', '83',
            '86', '87', '89', '91', '92', '99',
        ];

        $regionCode = substr($kpp, 0, 2);
        $reasonCode = (int) substr($kpp, 4, 2);

        // Validate region code
        if (! in_array($regionCode, $validRegionCodes)) {
            throw new \InvalidArgumentException('Invalid KPP: invalid region code');
        }

        // Validate reason code (01-50 for Russian entities, 51-99 for foreign)
        if ($reasonCode < 1 || $reasonCode > 99) {
            throw new \InvalidArgumentException('Invalid KPP: reason code must be between 01 and 99');
        }
    }
}
