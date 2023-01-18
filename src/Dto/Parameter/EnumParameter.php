<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\Manager\ReflectionManager;

class EnumParameter extends OptionParameter
{
    /**
     * @param class-string $className
     *
     * @throws \ReflectionException
     */
    public function __construct(
        private readonly ReflectionManager $reflectionManager,
        string $title,
        private readonly string $className,
    ) {
        $reflectionEnum = $this->reflectionManager->getReflectionEnum($this->className);
        $cases = [];

        foreach ($reflectionEnum->getCases() as $case) {
            $cases[$case->getName()] = $case->getValue();
        }

        parent::__construct($title, $cases);
    }
}
