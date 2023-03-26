<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\Exception\ParameterException;
use GibsonOS\Core\Manager\ReflectionManager;
use ReflectionException;

class EnumParameter extends OptionParameter
{
    /**
     * @param class-string $className
     *
     * @throws ReflectionException
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

    public function getEnum(string $value): object
    {
        $className = $this->className;

        if (!enum_exists($className)) {
            throw new ParameterException(sprintf('%s is no enum!', $className));
        }

        return $className::from($value);
    }
}
