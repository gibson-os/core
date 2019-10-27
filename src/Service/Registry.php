<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\GetError;

/**
 * @deprecated
 */
class Registry extends AbstractSingletonService
{
    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->registry);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function loadFromSession(string $name = 'REGISTRY'): bool
    {
        if (array_key_exists($name, $_SESSION)) {
            $this->registry = $_SESSION[$name];

            return true;
        }

        return false;
    }

    /**
     * @param string $name Name
     */
    public function saveToSession(string $name = 'REGISTRY'): void
    {
        $_SESSION[$name] = $this->registry;
    }

    /**
     * @param string $key
     *
     * @throws GetError
     *
     * @return mixed
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->registry)) {
            return $this->registry[$key];
        }

        throw new GetError(sprintf('Schlüssel "%s" nicht in der Registry gefunden', $key));
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value)
    {
        $this->registry[$key] = $value;
    }
}
