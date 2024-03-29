<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Fixture\Data\File\Metadata;

use SR\File\Tests\Fixture\Data\DataLoader;
use SR\File\Tests\Metadata\MediaTypeMetadataTest;

return function (): array {
    return [
        'default' => array_map(function (array $data): array {
            return [array_pop($data)];
        }, iterator_to_array((new DataLoader())(MediaTypeMetadataTest::class))),
    ];
};
