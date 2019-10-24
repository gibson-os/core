<?php
namespace GibsonOS\Core\Service;

class Registry
{
    /** @var null Instanz */
    private static $instance = null;
    /** @var array Registry */
    private $_registry = array();

    /**
     * Konstruktor
     *
     * Keine Instanzen erlauben.
     */
    private function __construct() {}

    /**
     * Klonen
     *
     * Keine Klonen erlauben.
     */
    private function __clone() {}
    
    /**
     * Gibt Registry Instanz zurück
     *
     * Gibt die Registry Instanz zurück.
     *
     * @return Registry
     */
    public static function getInstance(): Registry
    {
        if (self::$instance === NULL) {
            self::$instance = new self;
        }

        return self::$instance;
    }
    
    /**
     * Prüft ob Schlüssel existiert.
     *
     * Prüft ob ein Schlüssel existiert.
     *
     * @param string $key Schlüssel
     * @return boolean
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->_registry);
    }
   
    /**
     * Lädt Registry aus Session
     *
     * Lädt die Registry aus der Session.<br>
     * Wenn der Schlüssel $name nicht existiert, wird false zurückgegeben.
     *
     * @param string $name Name
     * @return boolean
     */
    public function loadFromSession($name = 'REGISTRY')
    {
        if (array_key_exists($name, $_SESSION)) {
            $this->_registry = $_SESSION[$name];
            return true;
        }

        return false;
    }
    
    /**
     * Speichert Registry in Session
     *
     * Speichert Registry in der Session zwischen.<br>
     * Der Schlüssel kann optional mit übergeben werden.
     *
     * @param string $name Name
     */    
    public function saveToSession($name = 'REGISTRY')
    {
        $_SESSION[$name] = $this->_registry;
    }

    /**
     * Gibt Wert zurück
     *
     * Gibt einen Wert anhand des Schlüssel zurück.
     *
     * @param string $key Schlüssel
     * @return mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->_registry)) {
            return $this->_registry[$key];
        }

        return false;
    }
    
    /**
     * Setzt Wert
     *
     * Setzt einen Wert anhand des Schlüssels.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->_registry[$key] = $value;
    }
}
