<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\Extension\Resolver;

use SR\File\FilePathInterface;
use SR\File\FileTemp;
use SR\File\Metadata\ExtensionMetadata;

final class FileNameExtensionResolver implements ExtensionResolverInterface
{
    use ExtensionResolverTrait;

    /**
     * @return bool
     */
    public static function isSupported()
    {
        return true;
    }

    /**
     * @param FilePathInterface $file
     *
     * @return null|ExtensionMetadata
     */
    public function doResolveFile(FilePathInterface $file): ?ExtensionMetadata
    {
        return ((!$file instanceof FileTemp) && (null !== $extension = $file->getPathInfo(PATHINFO_EXTENSION)) && (1 === preg_match('{^[a-z0-9-_]+$}i', $extension, $match)))
            ? ExtensionMetadata::create($extension)
            : null;
    }
}
