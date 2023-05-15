<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Mail;

class Address
{
    public function __construct(private string $address, private string $name)
    {
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): Address
    {
        $this->address = $address;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Address
    {
        $this->name = $name;

        return $this;
    }
}
