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

final class FileBlob extends AbstractFileBlob implements FileBlobInterface
{
    /**
     * @var string|null
     */
    private $contents;

    /**
     * @param string|null            $contents
     * @param MediaTypeMetadata|null $mediaType
     * @param ExtensionMetadata|null $extension
     */
    public function __construct(string $contents = null, MediaTypeMetadata $mediaType = null, ExtensionMetadata $extension = null)
    {
        parent::__construct($mediaType, $extension);

        $this->contents = $contents;
    }

    /**
     * @param string|null $contents
     * @param string|null $mediaType
     * @param string|null $extension
     *
     * @return self
     */
    public static function create(string $contents = null, string $mediaType = null, string $extension = null)
    {
        return new self($contents, MediaTypeMetadata::create($mediaType), ExtensionMetadata::create($extension));
    }

    /**
     * @return null|string
     */
    protected function doReadBlob(): ?string
    {
        return $this->contents;
    }

    /**
     * @param string $string
     * @param bool   $append
     */
    protected function doDumpBlob(string $string, bool $append): void
    {
        $this->contents = true === $append ? $this->contents.$string : $string;
    }
}
