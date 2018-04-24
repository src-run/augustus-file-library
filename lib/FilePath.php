<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File;

use SR\File\Metadata\MediaTypeMetadata;
use SR\File\Metadata\ExtensionMetadata;

final class FilePath extends AbstractFilePath implements FilePathInterface
{
    /**
     * @param string|\SplFileInfo|null $file
     * @param MediaTypeMetadata|null   $mediaType
     * @param ExtensionMetadata|null   $extension
     */
    public function __construct($file = null, MediaTypeMetadata $mediaType = null, ExtensionMetadata $extension = null)
    {
        parent::__construct($mediaType, $extension);

        if (null !== $file) {
            $this->file = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        }
    }

    /**
     * @param string|null $file
     * @param string|null $mediaType
     * @param string|null $extension
     *
     * @return self
     */
    public static function create(string $file = null, string $mediaType = null, string $extension = null)
    {
        return new self($file, MediaTypeMetadata::create($mediaType), ExtensionMetadata::create($extension));
    }
}
