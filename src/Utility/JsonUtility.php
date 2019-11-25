<?php
declare(strict_types=1);

namespace GibsonOS\Core\Utility;

class JsonUtility
{
    /**
     * Wandelt in JSON um.
     *
     * Wandelt Wert in JSON um.
     *
     * @param mixed $value Wert
     */
    public static function encode($value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    public static function decode(string $json)
    {
        return json_decode($json, true);
    }

    /**
     * @return mixed
     */
    public static function decodeNotAssoc(string $json)
    {
        return json_decode($json, false);
    }
}
