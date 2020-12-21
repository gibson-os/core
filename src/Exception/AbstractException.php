<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use Exception;

/**
 * Gibson OS.
 *
 * @author Benjamin Wollenweber
 *
 * @package GibsonOS\System
 *
 * @copyright 2014
 */
abstract class AbstractException extends Exception
{
    const INFO = 0;

    const WARNING = 1;

    const ERROR = 2;

    const QUESTION = 3;

    const PROMPT = 4;

    private ?string $title;

    private int $type = self::ERROR;

    private array $extraParameters = [];

    private array $buttons = [];

    private ?string $promptParameter;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): AbstractException
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): AbstractException
    {
        $this->type = $type;

        return $this;
    }

    public function getExtraParameters(): array
    {
        return $this->extraParameters;
    }

    /**
     * @param array $extraParameters
     */
    public function setExtraParameters($extraParameters): AbstractException
    {
        $this->extraParameters = $extraParameters;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setExtraParameter(string $key, $value): AbstractException
    {
        $this->extraParameters[$key] = $value;

        return $this;
    }

    public function getPromptParameter(): ?string
    {
        return $this->promptParameter;
    }

    /**
     * @param string|null $promptParameter
     */
    public function setPromptParameter($promptParameter): AbstractException
    {
        $this->promptParameter = $promptParameter;

        return $this;
    }

    public function getButtons(): array
    {
        if (!count($this->buttons)) {
            $this->addButton('OK');
        }

        return $this->buttons;
    }

    public function setButtons(array $buttons): AbstractException
    {
        $this->buttons = $buttons;

        return $this;
    }

    /**
     * @param mixed|null $value
     */
    public function addButton(string $text, string $parameter = null, $value = null): AbstractException
    {
        $this->buttons[] = [
            'text' => $text,
            'parameter' => $parameter,
            'value' => $value,
            'sendRequest' => $parameter ? true : false,
        ];

        return $this;
    }
}
