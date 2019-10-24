<?php
namespace GibsonOS\Core\Utility;

class ArrayKey
{
    /**
     * Durchsucht Mehrdimensionale Arrays
     *
     * Durchsucht Mehrdimensionale Arrays nach einem Schlüssel.
     *
     * @param string|int|array $key Schlüssel
     * @param array $search Zu durchsuchendes Array
     * @return bool
     */
    public static function exists($key, $search)
    {
        if (is_array($key)) {
            foreach ($key as $keyValue) {
                if (self::exists($keyValue, $search)) {
                    return true;
                }
            }
        } else if (!is_array($search)) {
            return false;
        }

        return array_key_exists($key, $search);
    }
}