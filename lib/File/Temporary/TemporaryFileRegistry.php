<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 * (c) Scribe Inc      <scr@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Temporary;

/**
 * Class TemporaryFileRegistry.
 */
class TemporaryFileRegistry
{
    /**
     * @var \SplFileInfo[]
     */
    private static $files = [];

    /**
     * @return \SplFileInfo[]
     */
    public static function getFiles()
    {
        return static::$files;
    }

    /**
     * @param \SplFileInfo $file
     */
    public static function add(\SplFileInfo $file)
    {
        if (!in_array($file, static::$files, true)) {
            static::$files[] = $file;
        }

        static::registerRemovalHandler();
    }

    /**
     * @param \SplFileInfo $file
     */
    public static function remove(\SplFileInfo $file)
    {
        static::$files = array_filter(static::$files, function (\SplFileInfo $f) use ($file) {
            return $f->getPathname() !== $file->getPathname();
        });
    }

    /**
     * @param \SplFileInfo $file
     */
    public static function delete(\SplFileInfo $file)
    {
        static::remove($file);

        if (file_exists($file->getPathname())) {
            @unlink($file->getPathname());
        }
    }

    /**
     * Remove all files known to registry from filesystem.
     */
    public static function deleteAll()
    {
        foreach (static::$files as $f) {
            static::delete($f);
        }
    }

    /**
     * Register function to removal temporary files.
     */
    private static function registerRemovalHandler()
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        register_shutdown_function([static::class, 'deleteAll']);

        $registered = true;
    }
}

/* EOF */
