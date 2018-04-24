<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\Extension\Resolver;

use SR\File\FileInterface;
use SR\File\FilePathInterface;
use SR\File\FilePathCastTrait;
use SR\File\Metadata\ExtensionMetadata;

trait ExtensionResolverTrait
{
    use FilePathCastTrait;

    /**
     * @param FileInterface $file
     *
     * @return null|ExtensionMetadata
     */
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

    /**
     * @param FilePathInterface $file
     *
     * @return null|string
     */
    abstract protected function doResolveFile(FilePathInterface $file): ?string;
}
