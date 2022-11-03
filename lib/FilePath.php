<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File;

use SR\File\Metadata\ExtensionMetadata;
use SR\File\Metadata\MediaTypeMetadata;

final class FilePath extends AbstractFilePath implements FilePathInterface
{
    /**
     * @param string|\SplFileInfo|null $file
     */
    public function __construct($file = null, MediaTypeMetadata $mediaType = null, ExtensionMetadata $extension = null)
    {
        parent::__construct($mediaType, $extension);

        if (null !== $file) {
            $this->file = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        }
    }

    /**
     * @return self
     */
    public static function create(string $file = null, string $mediaType = null, string $extension = null)
    {
        return new self($file, MediaTypeMetadata::create($mediaType), ExtensionMetadata::create($extension));
    }
}
