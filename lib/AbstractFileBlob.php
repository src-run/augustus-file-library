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

/**
 * @internal
 */
abstract class AbstractFileBlob extends AbstractFile
{
    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        return false;
    }

    /**
     * @return null|\SplFileInfo
     */
    public function getFile(): ?\SplFileInfo
    {
        return null;
    }
}
