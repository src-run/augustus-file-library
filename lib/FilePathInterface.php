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

interface FilePathInterface extends FileInterface
{
    /**
     * return bool
     */
    public function isFileExisting(): bool;

    public function isPathExisting(): bool;

    public function isFileReadable(): bool;

    public function isPathReadable(): bool;

    public function isFileWritable(): bool;

    public function isPathWritable(): bool;
}
