<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Command;

class ArgumentService
{
    private array $arguments = [];

    private array $options = [];

    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return bool[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function setArguments(array $arguments): ArgumentService
    {
        $this->arguments = [];

        foreach ($arguments as $argument) {
            if (mb_strpos($argument, '--') !== 0) {
                continue;
            }

            $argumentArray = explode('=', $argument);
            $this->arguments[mb_substr($argumentArray[0], 2)] = $argumentArray[1] ?? null;
        }

        return $this;
    }

    public function setOptions(array $options): ArgumentService
    {
        $this->options = [];

        foreach ($options as $option) {
            if (mb_strpos($option, '--') === 0 || mb_strpos($option, '-') !== 0) {
                continue;
            }

            $optionName = mb_substr($option, 1);
            $this->options[$optionName] = true;
        }

        return $this;
    }
}
