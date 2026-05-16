<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class SliderParameter extends AbstractParameter
{
    private bool $controlByVolumeControl = false;

    public function __construct(
        string $title,
        private readonly int $minValue,
        private readonly int $maxValue,
        private readonly int $increment,
    ) {
        parent::__construct($title, 'gosCoreComponentFormFieldSliderField');
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'minValue' => $this->minValue,
            'maxValue' => $this->maxValue,
            'increment' => $this->increment,
            'controlByVolumeControl' => $this->controlByVolumeControl,
        ];
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }

    public function setControlByVolumeControl(bool $controlByVolumeControl): SliderParameter
    {
        $this->controlByVolumeControl = $controlByVolumeControl;

        return $this;
    }
}
