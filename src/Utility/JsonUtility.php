<?php
declare(strict_types=1);

namespace GibsonOS\Core\Utility;

use JsonException;

class JsonUtility
{
    /**
     * @param mixed $value
     *
     * @throws JsonException
     */
    public static function encode($value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE + JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     *
     * @return mixed
     */
    public static function decode(string $json)
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     *
     * @return mixed
     */
    public static function decodeNotAssoc(string $json)
    {
        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }
}
