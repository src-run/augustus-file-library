<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\Extension\Resolver;

use SR\File\FileInterface;
use SR\File\FilePathCastTrait;
use SR\File\FilePathInterface;
use SR\File\Metadata\ExtensionMetadata;

trait ExtensionResolverTrait
{
    use FilePathCastTrait;

    public function resolve(FileInterface $file): ?ExtensionMetadata
    {
        if (
            (true === self::isSupported()) &&
            (null !== $file = self::castToFilePath($file)) &&
            (null !== $type = $this->doResolveFile($file))
        ) {
            return ExtensionMetadata::create($type);
        }

        return null;
    }

    abstract protected function doResolveFile(FilePathInterface $file);
}
