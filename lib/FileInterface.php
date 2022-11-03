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

use Ramsey\Uuid\UuidInterface;
use SR\File\Metadata\ExtensionMetadata;
use SR\File\Metadata\Guesser\Extension\ExtensionGuesser;
use SR\File\Metadata\Guesser\MediaType\MediaTypeGuesser;
use SR\File\Metadata\MediaTypeMetadata;

interface FileInterface
{
    public function __toString(): string;

    public function stringify(): string;

    public function getIdentity(): string;

    public function getUuid(): UuidInterface;

    public function setMediaType(MediaTypeMetadata $mediaType = null): self;

    public function getMediaType(): MediaTypeMetadata;

    public function hasMediaType(): bool;

    public function stringifyMediaType(): string;

    /**
     * @param ExtensionMetadata $extension
     */
    public function setExtension(ExtensionMetadata $extension = null): self;

    public function getExtension(): ExtensionMetadata;

    public function hasExtension(): bool;

    public function stringifyExtension(): string;

    public function hasFile(): bool;

    public function getFile(): ?\SplFileInfo;

    public function getFileUuid(): UuidInterface;

    public function getFileName(): ?string;

    public function getFileBaseName(): ?string;

    public function getFileExtension(): ?string;

    public function getFileUrl(): ?string;

    public function stringifyFile(): string;

    public function getPathInfo(int $option): ?string;

    public function getSizeBytes(): int;

    public function getSizeHuman(int $precision = null, bool $verbose = false): string;

    public function getPath(): ?string;

    public function getBlobUuid(): UuidInterface;

    public function getBlob(): ?string;

    public function hasBlob(): bool;

    public function setBlob(string $string = '', bool $append = false): self;

    public function getBlobHash(): string;

    public function getBlobLength(): int;

    /**
     * @return string[]
     */
    public function getBlobLines(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): array;

    /**
     * @return \Iterator|string
     */
    public function yieldBlobLines(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): \Iterator;

    public function getBlobLinesCount(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): int;

    public function getAccessedTime(): \DateTime;

    public function stringifyAccessedTime(string $format = null): string;

    public function getChangedTime(): \DateTime;

    public function stringifyChangedTime(string $format = null): string;

    public function getModifiedTime(): \DateTime;

    public function stringifyModifiedTime(string $format = null): string;

    public static function setDateTimeFormat(string $format): void;

    public static function resetDateTimeFormat(): void;

    public static function getMediaTypeGuesser(): MediaTypeGuesser;

    public static function setMediaTypeGuesser(MediaTypeGuesser $mediaTypeGuesser = null): ?MediaTypeGuesser;

    public static function getExtensionGuesser(): ExtensionGuesser;

    public static function setExtensionGuesser(ExtensionGuesser $extensionGuesser = null): ?ExtensionGuesser;
}
