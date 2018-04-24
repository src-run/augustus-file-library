<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\MediaType\Resolver;

use SR\File\FileInterface;
use SR\File\Metadata\MediaTypeMetadata;

interface MediaTypeResolverInterface
{
    /**
     * @return bool
     */
    public static function isSupported();

    /**
     * @param FileInterface $file
     *
     * @return null|MediaTypeMetadata
     */
    public function resolve(FileInterface $file): ?MediaTypeMetadata;
}
