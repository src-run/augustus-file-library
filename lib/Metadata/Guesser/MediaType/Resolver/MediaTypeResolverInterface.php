<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
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

    public function resolve(FileInterface $file): ?MediaTypeMetadata;
}
