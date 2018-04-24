<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\MediaType\Resolver;

use SR\File\FileInterface;
use SR\File\FilePathInterface;
use SR\File\FilePathCastTrait;
use SR\File\Metadata\MediaTypeMetadata;

trait MediaTypeResolverTrait
{
    use FilePathCastTrait;

    /**
     * @param FileInterface $file
     *
     * @return null|MediaTypeMetadata
     */
    public function resolve(FileInterface $file): ?MediaTypeMetadata
    {
        if (
            (true === static::isSupported()) &&
            (null !== $file = self::castToFilePath($file)) &&
            (null !== $type = $this->doResolveFile($file))
        ) {
            return MediaTypeMetadata::create($type);
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
