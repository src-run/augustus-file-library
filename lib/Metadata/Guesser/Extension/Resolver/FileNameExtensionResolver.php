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

    public function doResolveFile(FilePathInterface $file): ?ExtensionMetadata
    {
        return ((!$file instanceof FileTemp) && (null !== $extension = $file->getPathInfo(PATHINFO_EXTENSION)) && (1 === preg_match('{^[a-z0-9-_]+$}i', $extension ?? '', $match)))
            ? ExtensionMetadata::create($extension)
            : null;
    }
}
