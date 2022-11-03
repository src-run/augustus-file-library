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

final class FileBlob extends AbstractFileBlob implements FileBlobInterface
{
    /**
     * @var string|null
     */
    private $contents;

    public function __construct(string $contents = null, MediaTypeMetadata $mediaType = null, ExtensionMetadata $extension = null)
    {
        parent::__construct($mediaType, $extension);

        $this->contents = $contents;
    }

    /**
     * @return self
     */
    public static function create(string $contents = null, string $mediaType = null, string $extension = null)
    {
        return new self($contents, MediaTypeMetadata::create($mediaType), ExtensionMetadata::create($extension));
    }

    protected function doReadBlob(): ?string
    {
        return $this->contents;
    }

    protected function doDumpBlob(string $string, bool $append): void
    {
        $this->contents = true === $append ? $this->contents . $string : $string;
    }
}
