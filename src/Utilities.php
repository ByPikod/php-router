<?php

namespace PHPRouter;

/**
 * Utilities class
 * @since 1.0.1
 */
abstract class Utilities
{
    /**
     * Seperate the path into an array of strings.
     * @param string $path The path to seperate.
     * @return array The array of strings.
     * @example /test/:param/test -> [test, :param, test]
     * @since 1.0.0
     */
    public static function seperatePath(string $path): array
    {
        $path = trim($path, '/'); // Remove slashes from start and end
        $path = explode('/', $path); // Seperate to parts by '/'

        // Remove empty strings (eg. /test//test -> [test, test])
        $path = array_filter($path, function ($value) {
            return $value !== '';
        });

        return $path;
    }
}
