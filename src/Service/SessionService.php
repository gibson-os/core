<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use OutOfBoundsException;

class SessionService
{
    private $data = [];

    public function __construct()
    {
        session_start();
        $this->data = $_SESSION;
        session_write_close();
    }

    public function set(string $key, $value): SessionService
    {
        $this->data[$key] = $value;
        session_start();
        $_SESSION[$key] = $value;
        session_write_close();

        return $this;
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        if (isset($this->data[$key])) {
            throw new OutOfBoundsException(sprintf('Session key $%s not exists!', $key));
        }

        return $this->data[$key];
    }

    public function unset(string $key): void
    {
        if (isset($this->data[$key])) {
            throw new OutOfBoundsException(sprintf('Session key $%s not exists!', $key));
        }

        $this->unset($this->data[$key]);
    }
}
