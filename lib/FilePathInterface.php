<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
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

    /**
     * @return bool
     */
    public function isPathExisting(): bool;

    /**
     * @return bool
     */
    public function isFileReadable(): bool;

    /**
     * @return bool
     */
    public function isPathReadable(): bool;

    /**
     * @return bool
     */
    public function isFileWritable(): bool;

    /**
     * @return bool
     */
    public function isPathWritable(): bool;
}
