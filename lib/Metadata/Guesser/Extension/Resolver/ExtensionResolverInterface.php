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

use SR\File\FileInterface;
use SR\File\Metadata\ExtensionMetadata;

interface ExtensionResolverInterface
{
    /**
     * @return bool
     */
    public static function isSupported();

    /**
     * @param FileInterface $file
     *
     * @return null|ExtensionMetadata
     */
    public function resolve(FileInterface $file): ?ExtensionMetadata;
}
