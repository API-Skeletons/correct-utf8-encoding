<?php

namespace ApiSkeletonsTest;

use RuntimeException;

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    public static function init()
    {
        static::initAutoloader();
    }

    public static function chroot()
    {
        chdir(__DIR__ . "/..");
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (file_exists($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (! is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();
Bootstrap::chroot();
