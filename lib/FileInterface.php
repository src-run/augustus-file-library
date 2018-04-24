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

use Ramsey\Uuid\UuidInterface;
use SR\File\Metadata\Guesser\Extension\ExtensionGuesser;
use SR\File\Metadata\Guesser\MediaType\MediaTypeGuesser;
use SR\File\Metadata\MediaTypeMetadata;
use SR\File\Metadata\ExtensionMetadata;

interface FileInterface
{
    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @return string
     */
    public function stringify(): string;

    /**
     * @return string
     */
    public function getIdentity(): string;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface;

    /**
     * @param MediaTypeMetadata|null $mediaType
     *
     * @return self
     */
    public function setMediaType(MediaTypeMetadata $mediaType = null): self;

    /**
     * @return MediaTypeMetadata
     */
    public function getMediaType(): MediaTypeMetadata;

    /**
     * @return bool
     */
    public function hasMediaType(): bool;

    /**
     * @return string
     */
    public function stringifyMediaType(): string;

    /**
     * @param ExtensionMetadata $extension
     *
     * @return self
     */
    public function setExtension(ExtensionMetadata $extension = null): self;

    /**
     * @return ExtensionMetadata
     */
    public function getExtension(): ExtensionMetadata;

    /**
     * @return bool
     */
    public function hasExtension(): bool;

    /**
     * @return string
     */
    public function stringifyExtension(): string;

    /**
     * @return bool
     */
    public function hasFile(): bool;

    /**
     * @return \SplFileInfo|null
     */
    public function getFile(): ?\SplFileInfo;

    /**
     * @return UuidInterface
     */
    public function getFileUuid(): UuidInterface;

    /**
     * @return null|string
     */
    public function getFileName(): ?string;

    /**
     * @return null|string
     */
    public function getFileBaseName(): ?string;

    /**
     * @return null|string
     */
    public function getFileExtension(): ?string;

    /**
     * @return null|string
     */
    public function getFileUrl(): ?string;

    /**
     * @return string
     */
    public function stringifyFile(): string;

    /**
     * @param int $option
     *
     * @return null|string
     */
    public function getPathInfo(int $option): ?string;

    /**
     * @return int
     */
    public function getSizeBytes(): int;

    /**
     * @param int|null $precision
     * @param bool     $verbose
     *
     * @return string
     */
    public function getSizeHuman(int $precision = null, bool $verbose = false): string;

    /**
     * @return null|string
     */
    public function getPath(): ?string;

    /**
     * @return UuidInterface
     */
    public function getBlobUuid(): UuidInterface;

    /**
     * @return null|string
     */
    public function getBlob(): ?string;

    /**
     * @return bool
     */
    public function hasBlob(): bool;

    /**
     * @param string $string
     * @param bool   $append
     *
     * @return FileInterface
     */
    public function setBlob(string $string = '', bool $append = false): self;

    /**
     * @return string
     */
    public function getBlobHash(): string;

    /**
     * @return int
     */
    public function getBlobLength(): int;

    /**
     * @param \Closure|null $normalizer
     * @param \Closure|null $filterer
     * @param int|null      $newLineLimit
     * @param string        $newLineType
     *
     * @return string[]
     */
    public function getBlobLines(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): array;

    /**
     * @param \Closure|null $normalizer
     * @param \Closure|null $filterer
     * @param int|null      $newLineLimit
     * @param string        $newLineType
     *
     * @return \Iterator|string
     */
    public function yieldBlobLines(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): \Iterator;

    /**
     * @param \Closure|null $normalizer
     * @param \Closure|null $filterer
     * @param int|null      $newLineLimit
     * @param string        $newLineType
     *
     * @return int
     */
    public function getBlobLinesCount(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): int;

    /**
     * @return \DateTime
     */
    public function getAccessedTime(): \DateTime;

    /**
     * @param string|null $format
     *
     * @return string
     */
    public function stringifyAccessedTime(string $format = null): string;

    /**
     * @return \DateTime
     */
    public function getChangedTime(): \DateTime;

    /**
     * @param string|null $format
     *
     * @return string
     */
    public function stringifyChangedTime(string $format = null): string;

    /**
     * @return \DateTime
     */
    public function getModifiedTime(): \DateTime;

    /**
     * @param string|null $format
     *
     * @return string
     */
    public function stringifyModifiedTime(string $format = null): string;

    /**
     * @param string $format
     */
    public static function setDateTimeFormat(string $format): void;

    /**
     * @return void
     */
    public static function resetDateTimeFormat(): void;

    /**
     * @return MediaTypeGuesser
     */
    public static function getMediaTypeGuesser(): MediaTypeGuesser;

    /**
     * @param MediaTypeGuesser|null $mediaTypeGuesser
     *
     * @return null|MediaTypeGuesser
     */
    public static function setMediaTypeGuesser(MediaTypeGuesser $mediaTypeGuesser = null): ?MediaTypeGuesser;

    /**
     * @return ExtensionGuesser
     */
    public static function getExtensionGuesser(): ExtensionGuesser;

    /**
     * @param ExtensionGuesser|null $extensionGuesser
     *
     * @return null|ExtensionGuesser
     */
    public static function setExtensionGuesser(ExtensionGuesser $extensionGuesser = null): ?ExtensionGuesser;
}
