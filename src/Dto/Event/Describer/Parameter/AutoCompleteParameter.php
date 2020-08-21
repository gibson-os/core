<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

class AutoCompleteParameter extends AbstractParameter
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $model;

    /**
     * @var array
     */
    private $parameters = [];

    public function __construct(string $title, string $url, string $model)
    {
        parent::__construct($title, 'autoComplete');
        $this->url = $url;
        $this->model = $model;
    }

    public function setParameters(array $parameters): AutoCompleteParameter
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function setParameter(string $key, $value): AutoCompleteParameter
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    protected function getTypeConfig(): array
    {
        return [
            'url' => $this->url,
            'model' => $this->model,
            'parameters' => $this->parameters,
        ];
    }
}