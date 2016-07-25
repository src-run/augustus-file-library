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
     * {@inheritdoc}
     */
    public function __construct($file, $relativePath = null, $relativePathname = null, $resolvePath = true)
    {
        parent::__construct($file, $relativePath, $relativePathname, $resolvePath);

        $this->enableAutomaticDeletion();
    }

    /**
     * @param string $prefix
     *
     * @return static
     */
    public static function create($prefix = null)
    {
        $prefix = $prefix ?: 'php-tmp-file-';

        return new static(tempnam(sys_get_temp_dir(), $prefix));
    }

    /**
     * @param bool $enable
     *
     * @return $this
     */
    public function automaticDeletion($enable = true)
    {
        if ($enable) {
            TemporaryFileRegistry::add($this);
        } else {
            TemporaryFileRegistry::remove($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function enableAutomaticDeletion()
    {
        return $this->automaticDeletion(true);
    }

    /**
     * @return $this
     */
    public function disableAutomaticDeletion()
    {
        return $this->automaticDeletion(false);
    }
}

/* EOF */
