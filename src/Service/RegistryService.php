<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\GetError;

/**
 * @deprecated
 */
class RegistryService
{
    private array $registry = [];

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->registry);
    }

    public function loadFromSession(string $name = 'REGISTRY'): bool
    {
        /** @psalm-suppress InvalidScalarArgument */
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
     * @throws GetError
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->registry)) {
            return $this->registry[$key];
        }

        throw new GetError(sprintf('Schlüssel "%s" nicht in der Registry gefunden', $key));
    }

    public function set(string $key, $value): void
    {
        $this->registry[$key] = $value;
    }
}
