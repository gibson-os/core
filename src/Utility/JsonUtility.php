<?php
declare(strict_types=1);

namespace GibsonOS\Core\Utility;

use JsonException;

class JsonUtility
{
    /**
     * @throws JsonException
     */
    public static function encode($value, int $flags = JSON_UNESCAPED_UNICODE + JSON_THROW_ON_ERROR): string
    {
        return json_encode($value, $flags);
    }

    /**
     * @throws JsonException
     */
    public static function decode(string $json, int $flags = JSON_THROW_ON_ERROR)
    {
        return json_decode($json, true, 512, $flags);
    }

    /**
     * @throws JsonException
     */
    public static function decodeNotAssoc(string $json, int $flags = JSON_THROW_ON_ERROR)
    {
        return json_decode($json, false, 512, $flags);
    }
}
