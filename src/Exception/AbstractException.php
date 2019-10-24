<?php
namespace GibsonOS\Core\Exception;

use Exception;

/**
 * Gibson OS
 *
 * @author Benjamin Wollenweber
 * @package GibsonOS\System
 * @copyright 2014
 */
abstract class AbstractException extends Exception
{
    const INFO = 0;
    const WARNING = 1;
    const ERROR = 2;
    const QUESTION = 3;
    const PROMPT = 4;

    /**
     * @var null|string
     */
    private $title = null;
    /**
     * @var int
     */
    private $type = self::ERROR;
    /**
     * @var array
     */
    private $extraParameters = [];
    /**
     * @var array
     */
    private $buttons = [];
    /**
     * @var null|string
     */
    private $promptParameter = null;

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     * @return AbstractException
     */
    public function setTitle($title): AbstractException
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return AbstractException
     */
    public function setType($type): AbstractException
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraParameters(): array
    {
        return $this->extraParameters;
    }

    /**
     * @param array $extraParameters
     * @return AbstractException
     */
    public function setExtraParameters($extraParameters): AbstractException
    {
        $this->extraParameters = $extraParameters;
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return AbstractException
     */
    public function setExtraParameter(string $key, $value): AbstractException
    {
        $this->extraParameters[$key] = $value;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPromptParameter(): ?string
    {
        return $this->promptParameter;
    }

    /**
     * @param null|string $promptParameter
     * @return AbstractException
     */
    public function setPromptParameter($promptParameter): AbstractException
    {
        $this->promptParameter = $promptParameter;
        return $this;
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        if (!count($this->buttons)) {
            $this->addButton('OK');
        }

        return $this->buttons;
    }

    /**
     * @param array $buttons
     */
    public function setButtons(array $buttons)
    {
        $this->buttons = $buttons;
    }

    /**
     * @param string $text
     * @param string|null $parameter
     * @param mixed|null $value
     */
    public function addButton(string $text, string $parameter = null, $value = null)
    {
        $this->buttons[] = [
            'text' => $text,
            'parameter' => $parameter,
            'value' => $value,
            'sendRequest' => $parameter ? true : false
        ];
    }
}