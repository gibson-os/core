<?php
declare(strict_types=1);

namespace GibsonOS\Core\Utility;

class JsonUtility
{
    public static function encode(mixed $value, int $flags = JSON_UNESCAPED_UNICODE + JSON_THROW_ON_ERROR): string
    {
        return json_encode($value, $flags);
    }

    public static function decode(string $json, int $flags = JSON_THROW_ON_ERROR): mixed
    {
        return json_decode($json, true, 512, $flags);
    }

    public static function decodeNotAssoc(string $json, int $flags = JSON_THROW_ON_ERROR)
    {
        return json_decode($json, false, 512, $flags);
    }
}
