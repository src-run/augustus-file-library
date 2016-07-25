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

use SR\File\FileInfo;

/**
 * Class TemporaryFileInfo.
 */
class TemporaryFileInfo extends FileInfo
{
    /**
     * @param string      $file
     * @param string|null $relativePath
     * @param string|null $relativePathname
     * @param bool        $resolvePath
     */
    public function __construct($file, $relativePath = null, $relativePathname = null, $resolvePath = true)
    {
        parent::__construct($file, $relativePath, $relativePathname, $resolvePath);

        TemporaryFileRegistry::add($this);
    }

    /**
     * @param string      $prefix
     *
     * @return static
     */
    public static function create($prefix = null)
    {
        $prefix = $prefix ?: 'php-tmp-file-';

        return new static(tempnam(sys_get_temp_dir(), $prefix));
    }

    /**
     * @return $this
     */
    public function persist()
    {
        TemporaryFileRegistry::remove($this);

        return $this;
    }
}

/* EOF */
