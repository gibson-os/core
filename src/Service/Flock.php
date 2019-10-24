<?php
namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Flock\UnFlockError;
use GibsonOS\Core\Exception\Flock\FlockError;

class Flock extends AbstractSingletonService
{
    /**
     * @var resource[]
     */
    private $flocks;

    /**
     * @param null|string $name
     * @throws FlockError
     */
    public function flock($name = null)
    {
        $name = $this->getName($name);

        if (isset($this->flocks[$name])) {
            throw new FlockError();
        }

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name . '.flock';

        if (!file_exists($path)) {
            file_put_contents($path, '');
        }

        $flock = fopen($path, 'r+');

        if (!$flock) {
            throw new FlockError();
        }

        if (!flock($flock, LOCK_EX + LOCK_NB)) {
            fclose($flock);

            throw new FlockError();
        }

        $this->flocks[$name] = $flock;
    }

    /**
     * @param null|string $name
     * @throws UnFlockError
     */
    public function unFlock($name = null)
    {
        $name = $this->getName($name);

        if (!isset($this->flocks[$name])) {
            throw new UnFlockError();
        }

        $flock = $this->flocks[$name];

        if (!flock($flock, LOCK_UN)) {
            fclose($flock);

            throw new UnFlockError();
        }

        fclose($flock);
        unset($this->flocks[$name]);
    }

    /**
     * @param null|string $name
     */
    public function waitUnFlockToFlock($name = null)
    {
        try {
            $this->flock($name);
        } catch (FlockError $e) {
            usleep(10);
            $this->waitUnFlockToFlock($name);
        }
    }

    private function getName($name = null)
    {
        if (!is_null($name)) {
            return $name;
        }

        $caller = debug_backtrace();

        return str_replace(DIRECTORY_SEPARATOR, '', $caller[1]['file']);
    }

    /**
     * @return AbstractSingletonService|Flock
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }
}