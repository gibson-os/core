<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\SetError;

class EnvService extends AbstractService
{
    /**
     * @throws GetError
     */
    public function getInt(string $name): int
    {
        return (int) $this->get($name);
    }

    /**
     * @throws GetError
     */
    public function getString(string $name): string
    {
        return $this->get($name);
    }

    /**
     * @throws GetError
     */
    public function getFloat(string $name): float
    {
        return (float) $this->get($name);
    }

    /**
     * @throws GetError
     */
    public function getBool(string $name): bool
    {
        return mb_strtolower($this->get($name)) === 'true' ? true : false;
    }

    /**
     * @throws SetError
     */
    public function setInt(string $name, int $value): void
    {
        $this->set($name, (string) $value);
    }

    /**
     * @throws SetError
     */
    public function setString(string $name, string $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @throws SetError
     */
    public function setFloat(string $name, float $value): void
    {
        $this->set($name, (string) $value);
    }

    /**
     * @throws SetError
     */
    public function setBool(string $name, bool $value): void
    {
        $this->set($name, $value === true ? 'true' : 'false');
    }

    /**
     * @throws SetError
     */
    public function setEmpty(string $name): void
    {
        $this->set($name, '');
    }

    /**
     * @throws GetError
     */
    private function get(string $name): string
    {
        $name = mb_strtoupper($name);
        $value = getenv($name);

        if (is_bool($value)) {
            throw new GetError(sprintf('Umgebungsvariable "%s" ist nicht gesetzt!', $name));
        }

        return $value;
    }

    /**
     * @throws SetError
     */
    private function set(string $name, string $value): void
    {
        $name = mb_strtoupper($name);
        $putString = $name;

        if ($value !== '') {
            $putString .= '=' . $value;
        }

        if (!putenv($putString)) {
            throw new SetError(sprintf('Umgebungsvariable "%s" konnte nicht mit dem Wert "%s" gesetzt werden!', $name, $value));
        }
    }
}
