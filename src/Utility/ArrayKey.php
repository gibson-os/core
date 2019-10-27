<?php
declare(strict_types=1);

namespace GibsonOS\Core\Utility;

class ArrayKey
{
    /**
     * @param string|int|array $key
     * @param array            $search
     *
     * @return bool
     */
    public static function exists($key, array $search): bool
    {
        if (is_array($key)) {
            foreach ($key as $keyValue) {
                if (self::exists($keyValue, $search)) {
                    return true;
                }
            }

            return false;
        }

        return array_key_exists($key, $search);
    }
}
