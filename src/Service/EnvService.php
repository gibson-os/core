<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\SetError;

class EnvService extends AbstractService
{
    /**
     * @param string $name
     *
     * @throws GetError
     *
     * @return int
     */
    public function getInt(string $name): int
    {
        return (int) $this->get($name);
    }

    /**
     * @param string $name
     *
     * @throws GetError
     *
     * @return string
     */
    public function getString(string $name): string
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     *
     * @throws GetError
     *
     * @return float
     */
    public function getFloat(string $name): float
    {
        return (float) $this->get($name);
    }

    /**
     * @param string $name
     *
     * @throws GetError
     *
     * @return bool
     */
    public function getBoolValue(string $name): bool
    {
        return mb_strtolower($this->get($name)) === 'true' ? true : false;
    }

    /**
     * @param string $name
     * @param int    $value
     *
     * @throws SetError
     */
    public function setInt(string $name, int $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @throws SetError
     */
    public function setString(string $name, string $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @param float  $value
     *
     * @throws SetError
     */
    public function setFloat(string $name, float $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @param bool   $value
     *
     * @throws SetError
     */
    public function setBool(string $name, bool $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @throws GetError
     *
     * @return string
     */
    private function get(string $name): string
    {
        $name = mb_strtoupper($name);
        $value = getenv($name);

        if (!is_string($value)) {
            throw new GetError(sprintf('Umgebungsvariable "%s" ist nicht gesetzt!', $name));
        }

        return $value;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws SetError
     */
    private function set(string $name, $value): void
    {
        $name = mb_strtoupper($name);
        $putString = $name;

        if ($value !== null) {
            $putString .= '=' . $value;
        }

        if (!putenv($putString)) {
            throw new SetError(sprintf(
                'Umgebungsvariable "%s" konnte nicht mit dem Wert "%s" gesetzt werden!',
                $name,
                $value
            ));
        }
    }
}
