<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Service\Attribute\AttributeServiceInterface;

class Attribute
{
    public function __construct(private AttributeInterface $attribute, private AttributeServiceInterface $service)
    {
    }

    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    public function setAttribute(AttributeInterface $attribute): Attribute
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getService(): AttributeServiceInterface
    {
        return $this->service;
    }

    public function setService(AttributeServiceInterface $service): Attribute
    {
        $this->service = $service;

        return $this;
    }
}
