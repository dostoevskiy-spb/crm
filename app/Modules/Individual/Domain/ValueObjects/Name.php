<?php

declare(strict_types=1);

namespace App\Modules\Individual\Domain\ValueObjects;

final class Name
{
    private string $firstName;

    private string $lastName;

    private string $middleName;

    public function __construct(string $firstName, string $lastName, string $middleName)
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $middleName = trim($middleName);

        if ($firstName === '' || mb_strlen($firstName) > 20) {
            throw new \InvalidArgumentException('First name must be between 1 and 20 characters');
        }
        if ($lastName === '' || mb_strlen($lastName) > 20) {
            throw new \InvalidArgumentException('Last name must be between 1 and 20 characters');
        }
        if ($middleName === '' || mb_strlen($middleName) > 20) {
            throw new \InvalidArgumentException('Middle name must be between 1 and 20 characters');
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->middleName = $middleName;
    }

    public function first(): string
    {
        return $this->firstName;
    }

    public function last(): string
    {
        return $this->lastName;
    }

    public function middle(): string
    {
        return $this->middleName;
    }

    public function full(): string
    {
        return sprintf('%s %s %s', $this->lastName, $this->firstName, $this->middleName);
    }

    public function short(): string
    {
        $f = mb_substr($this->firstName, 0, 1);
        $m = mb_substr($this->middleName, 0, 1);

        return sprintf('%s %s.%s.', $this->lastName, $f, $m);
    }
}
