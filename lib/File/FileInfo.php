<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File;

use SR\Path\PathInfo;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class FileInfo.
 */
class FileInfo extends SplFileInfo
{
    /**
     * @param string      $file
     * @param string|null $relativePath
     * @param string|null $relativePathname
     * @param bool        $resolvePath
     */
    public function __construct($file, $relativePath = null, $relativePathname = null, $resolvePath = true)
    {
        if (true === $resolvePath && false !== $realFile = realpath($file)) {
            $file = $realFile;
        }

        if ($relativePath === null || $relativePathname === null) {
            $relativePathname = PathInfo::absoluteToRelative($file);
            $relativePath = dirname($relativePathname);
        }

        parent::__construct($file, $relativePath, $relativePathname);
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return static
     */
    public static function createFromSplFileInfo(\SplFileInfo $file)
    {
        return new static($file->getPathname());
    }

    /**
     * @param int $precision
     *
     * @return string
     */
    public function getSizeHuman($precision = 2)
    {
        $sizes = 'BKMGTP';
        $factor = floor((strlen($this->getSize()) - 1) / 3);

        return sprintf("%.{$precision}f", $this->getSize() / pow(1024, $factor)).@$sizes[(int) $factor];
    }
}

/* EOF */
