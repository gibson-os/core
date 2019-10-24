<?php
namespace GibsonOS\Core\Utility;

class Json
{
    /**
     * Wandelt in JSON um
     *
     * Wandelt Wert in JSON um.
     *
     * @param mixed $value Wert
     * @return string
     */
    public static function encode($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Wandelt von JSON um.
     *
     * Wandelt einen JSON String um.
     *
     * @param string $json JSON
     * @param bool $assoc Als Assoziatives Array
     * @return mixed
     */
    public static function decode($json, $assoc = true)
    {
        return json_decode($json, $assoc);
    }
}