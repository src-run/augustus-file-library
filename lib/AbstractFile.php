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

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SR\File\Metadata\Guesser\Extension\ExtensionGuesser;
use SR\File\Metadata\Guesser\MediaType\MediaTypeGuesser;
use SR\File\Metadata\MediaTypeMetadata;
use SR\File\Metadata\ExtensionMetadata;

/**
 * @internal
 */
abstract class AbstractFile implements FileInterface
{
    /**
     * @var string
     */
    private const DEFAULT_DATETIME_FORMAT = 'D M j Y H\:i\:s T';

    /**
     * @var string
     */
    private static $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;

    /**
     * @var MediaTypeGuesser
     */
    private static $mediaTypeGuesser;

    /**
     * @var ExtensionGuesser
     */
    private static $extensionGuesser;

    /**
     * @var Uuid|null
     */
    private $fileUuid;

    /**
     * @var Uuid|null
     */
    private $blobUuid;

    /**
     * @var string|null
     */
    private $blobUuidHash;

    /**
     * @var MediaTypeMetadata
     */
    private $mediaType;

    /**
     * @var ExtensionMetadata
     */
    private $extension;

    /**
     * @var int
     */
    private $timeAccessed;

    /**
     * @var int
     */
    private $timeChanged;

    /**
     * @var int
     */
    private $timeModified;

    /**
     * @param MediaTypeMetadata|null $mediaType
     * @param ExtensionMetadata|null $extension
     */
    public function __construct(MediaTypeMetadata $mediaType = null, ExtensionMetadata $extension = null)
    {
        $this->setMediaType($mediaType);
        $this->setExtension($extension);

        $this->timeAccessed = $this->timeChanged = $this->timeModified = time();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getIdentity();
    }

    /**
     * @return string
     */
    public function stringify(): string
    {
        return $this->hasFile() ? ($this->getFileUrl() ?? $this->getIdentity()) : $this->getIdentity();
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->getUuid()->toString();
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->hasFile() ? $this->getFileUuid() : $this->getBlobUuid();
    }

    /**
     * @param MediaTypeMetadata|null $mediaType
     *
     * @return FileInterface
     */
    public function setMediaType(MediaTypeMetadata $mediaType = null): FileInterface
    {
        $this->mediaType = $mediaType ?? MediaTypeMetadata::create();

        return $this;
    }

    /**
     * @return MediaTypeMetadata
     */
    public function getMediaType(): MediaTypeMetadata
    {
        return $this->mediaType;
    }

    /**
     * @return bool
     */
    public function hasMediaType(): bool
    {
        return $this->getMediaType()->isValid();
    }

    /**
     * @return string
     */
    public function stringifyMediaType(): string
    {
        return $this->getMediaType()->stringify();
    }

    /**
     * @param ExtensionMetadata $extension
     *
     * @return FileInterface
     */
    public function setExtension(ExtensionMetadata $extension = null): FileInterface
    {
        $this->extension = $extension ?? ExtensionMetadata::create();

        return $this;
    }

    /**
     * @return ExtensionMetadata
     */
    public function getExtension(): ExtensionMetadata
    {
        return $this->extension;
    }

    /**
     * @return bool
     */
    public function hasExtension(): bool
    {
        return $this->getExtension()->isValid();
    }

    /**
     * @return string
     */
    public function stringifyExtension(): string
    {
        return $this->getExtension()->stringify();
    }

    /**
     * @return null|string
     */
    public function getFileName(): ?string
    {
        return $this->hasFile() ? ($this->getFile()->getFilename() ?: null) : null;
    }

    /**
     * @return null|string
     */
    public function getFileBaseName(): ?string
    {
        return $this->hasFile() ? ($this->getFile()->getBasename() ?: null) : null;
    }

    /**
     * @return null|string
     */
    public function getFileExtension(): ?string
    {
        return $this->hasFile() ? ($this->getFile()->getExtension() ?: null) : null;
    }

    /**
     * @return null|string
     */
    public function getFileUrl(): ?string
    {
        if (!$this->hasFile() || !($file = $this->stringifyFile())) {
            return null;
        }

        if (null === $scheme = parse_url($file, PHP_URL_SCHEME)) {
            $scheme = 'file';
        }

        return sprintf('%s://%s', $scheme, str_replace(sprintf('%s://', $scheme), '', $file));
    }

    /**
     * @return string
     */
    public function stringifyFile(): string
    {
        return $this->hasFile() ? ($this->getFile()->getPathname() ?: '') : '';
    }

    /**
     * @param int $option
     *
     * @return null|string
     */
    public function getPathInfo(int $option): ?string
    {
        return $this->hasFile() ? (pathinfo($this->stringifyFile(), $option) ?: null) : null;
    }

    /**
     * @return int
     */
    public function getSizeBytes(): int
    {
        return $this->hasFile() ? $this->getFile()->getSize() : self::calculateBlobBytes($this->getBlob());
    }

    /**
     * @param int|null $precision
     * @param bool     $verbose
     *
     * @return string
     */
    public function getSizeHuman(int $precision = null, bool $verbose = false): string
    {
        $bytes = $this->getSizeBytes();
        $index = (int) floor(log($bytes, 1024));
        $units = true === $verbose
            ? ['Byte', 'Kilobyte', 'Megabyte', 'Gigabyte', 'Terabyte', 'Petabyte', 'Exabyte', 'Zettabyte', 'Yottabyte']
            : ['B', 'KB', 'MB', 'GB', 'TB', 'PB','EB','ZB','YB'];

        if (null === $precision) {
            $precision = [0, 1, 2, 2, 3, 3, 3, 3, 3][$index];
        }

        return vsprintf('%s %s', [
            $size = sprintf(sprintf('%%.%df', $precision), $bytes / pow(1024, $index)),
            (function (string $word, float $count) {
                return strlen($word) > 2 && ($count > 1 || $count === 0.0) ? sprintf('%ss', $word) : $word;
            })($units[(int) $index] ?? $units[0], (float) $size)
        ]);
    }

    /**
     * @return UuidInterface
     */
    public function getFileUuid(): UuidInterface
    {
        if (null === $this->fileUuid) {
            $this->fileUuid = $this->hasFile()
                ? Uuid::uuid5(Uuid::NAMESPACE_URL, $this->getFileUrl())
                : Uuid::fromString(Uuid::NIL);
        }

        return $this->fileUuid;
    }

    /**
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->hasFile() ? $this->getFile()->getPath() : null;
    }

    /**
     * @return UuidInterface
     */
    public function getBlobUuid(): UuidInterface
    {
        $hash = $this->getBlobHash();

        if (null === $this->blobUuid || $hash !== $this->blobUuidHash) {
            $this->blobUuid = $this->hasBlob()
                ? Uuid::uuid5(Uuid::NAMESPACE_OID, $this->getBlob())
                : Uuid::fromString(Uuid::NIL);
            $this->blobUuidHash = $hash;
        }

        return $this->blobUuid;
    }

    /**
     * @return null|string
     */
    public function getBlob(): ?string
    {
        $this->timeAccessed = time();

        return $this->doReadBlob();
    }

    /**
     * @return bool
     */
    public function hasBlob(): bool
    {
        return null !== $this->doReadBlob();
    }

    /**
     * @param string $string
     * @param bool   $append
     *
     * @return FileInterface
     */
    public function setBlob(string $string = '', bool $append = false): FileInterface
    {
        $this->timeModified = time();
        $this->doDumpBlob($string, $append);

        return $this;
    }

    /**
     * @return string
     */
    public function getBlobHash(): string
    {
        return hash('sha512', $this->getBlob() ?? '');
    }

    /**
     * @return int
     */
    public function getBlobLength(): int
    {
        return mb_strlen($this->getBlob());
    }

    /**
     * @param \Closure|null $normalizer
     * @param \Closure|null $filterer
     * @param int|null      $newLineLimit
     * @param string        $newLineType
     *
     * @return string[]
     */
    public function getBlobLines(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): array
    {
        return self::filterNormalizedList(explode(
            $newLineType ?? PHP_EOL, self::normalizeBlobNewLines($this->getBlob(), $newLineLimit, $newLineType)
        ), $filterer, $normalizer);
    }

    /**
     * @param \Closure|null $normalizer
     * @param \Closure|null $filterer
     * @param int|null      $newLineLimit
     * @param string        $newLineType
     *
     * @return \Iterator|string
     */
    public function yieldBlobLines(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): \Iterator
    {
        foreach (self::explodeNormalizedBlob($this->getBlob(), $newLineLimit, $newLineType) as $i => $v) {
            $line = $normalizer ? $normalizer($v) : $v;

            if (true === ($filterer ? $filterer($line) : true)) {
                yield $i => $line;
            }
        }
    }

    /**
     * @param \Closure|null $normalizer
     * @param \Closure|null $filterer
     * @param int|null      $newLineLimit
     * @param string        $newLineType
     *
     * @return int
     */
    public function getBlobLinesCount(\Closure $normalizer = null, \Closure $filterer = null, int $newLineLimit = null, string $newLineType = PHP_EOL): int
    {
        return count(self::filterNormalizedList(explode(
            $newLineType ?? PHP_EOL, self::normalizeBlobNewLines($this->getBlob(), $newLineLimit, $newLineType)
        ), $filterer, $normalizer));
    }

    /**
     * @return \DateTime
     */
    public function getAccessedTime(): \DateTime
    {
        return new \DateTime(sprintf(
            '@%s', $this->hasFile() ? $this->getFile()->getATime() : $this->timeAccessed
        ));
    }

    /**
     * @param string|null $format
     *
     * @return string
     */
    public function stringifyAccessedTime(string $format = null): string
    {
        return self::normalizeStringifyTime(
            $this->getAccessedTime()->format($format ?? self::$dateTimeFormat), $format
        );
    }

    /**
     * @return \DateTime
     */
    public function getChangedTime(): \DateTime
    {
        return new \DateTime(sprintf(
            '@%s', $this->hasFile() ? $this->getFile()->getCTime() : $this->timeChanged
        ));
    }

    /**
     * @param string|null $format
     *
     * @return string
     */
    public function stringifyChangedTime(string $format = null): string
    {
        return self::normalizeStringifyTime(
            $this->getChangedTime()->format($format ?? self::$dateTimeFormat), $format
        );
    }

    /**
     * @return \DateTime
     */
    public function getModifiedTime(): \DateTime
    {
        return new \DateTime(sprintf(
            '@%s', $this->hasFile() ? $this->getFile()->getMTime() : $this->timeModified
        ));
    }

    /**
     * @param string|null $format
     *
     * @return string
     */
    public function stringifyModifiedTime(string $format = null): string
    {
        return self::normalizeStringifyTime(
            $this->getModifiedTime()->format($format ?? self::$dateTimeFormat), $format
        );
    }

    /**
     * @param string $format
     */
    public static function setDateTimeFormat(string $format): void
    {
        self::$dateTimeFormat = $format;
    }

    /**
     * @return void
     */
    public static function resetDateTimeFormat(): void
    {
        self::$dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;
    }

    /**
     * @return MediaTypeGuesser
     */
    public static function getMediaTypeGuesser(): MediaTypeGuesser
    {
        return static::$mediaTypeGuesser ?? self::setMediaTypeGuesser(new MediaTypeGuesser());
    }

    /**
     * @param MediaTypeGuesser|null $mediaTypeGuesser
     *
     * @return null|MediaTypeGuesser
     */
    public static function setMediaTypeGuesser(MediaTypeGuesser $mediaTypeGuesser = null): ?MediaTypeGuesser
    {
        return static::$mediaTypeGuesser = $mediaTypeGuesser;
    }

    /**
     * @return ExtensionGuesser
     */
    public static function getExtensionGuesser(): ExtensionGuesser
    {
        return static::$extensionGuesser ?? self::setExtensionGuesser(new ExtensionGuesser());
    }

    /**
     * @param ExtensionGuesser|null $extensionGuesser
     *
     * @return null|ExtensionGuesser
     */
    public static function setExtensionGuesser(ExtensionGuesser $extensionGuesser = null): ?ExtensionGuesser
    {
        return static::$extensionGuesser = $extensionGuesser;
    }

    /**
     * @return null|string
     */
    abstract protected function doReadBlob(): ?string;

    /**
     * @param string $string
     * @param bool   $append
     */
    abstract protected function doDumpBlob(string $string, bool $append): void;

    /**
     * @param string $string
     *
     * @return int
     */
    private static function calculateBlobBytes(string $string): int
    {
        $temp = new FileTemp();
        $temp->acquire();
        $temp->setBlob($string);

        return (new \SplFileInfo($temp->stringifyFile()))->getSize();
    }

    /**
     * @param string      $time
     * @param string|null $format
     *
     * @return string
     */
    private static function normalizeStringifyTime(string $time, string $format = null): string
    {
        if (self::DEFAULT_DATETIME_FORMAT === ($format ?? self::$dateTimeFormat)) {
            $time = preg_replace('{([A-Z]+)((?:\+|\-)[0-9]{4})$}', '$1', $time);
        }

        return trim($time, ' ');
    }

    /**
     * @param string      $string
     * @param null        $ceiling
     * @param string|null $replace
     *
     * @return array
     */
    private static function explodeNormalizedBlob(string $string, $ceiling = null, string $replace = null): array
    {
        return explode($replace ?? PHP_EOL, self::normalizeBlobNewLines($string, $ceiling, $replace));
    }

    /**
     * @param string      $string
     * @param null        $ceiling
     * @param string|null $replace
     *
     * @return string
     */
    private static function normalizeBlobNewLines(string $string, $ceiling = null, string $replace = null): string
    {
        $ceiling = $ceiling ?? 1;
        $replace = $replace ?? PHP_EOL;

        return preg_replace(
            sprintf('{([\s]*%s[\s]*){%d}([\s]*%s[\s]*){1,}}', $replace, $ceiling, $replace),
            str_repeat($replace, $ceiling),
            (function (string $s) use ($replace): string {
                return preg_replace(['{\r\n}', '{\r}', '{\n}'], [$replace, $replace, $replace], $s);
            })($string)
        );
    }

    /**
     * @param array         $array
     * @param \Closure|null $filter
     * @param \Closure|null $normal
     *
     * @return array
     */
    private static function filterNormalizedList(array $array, \Closure $filter = null, \Closure $normal = null): array
    {
        return array_filter(
            array_map(function ($element) use ($normal) {
                return $normal ? $normal($element) : $element;
            }, $array),
            function ($element) use ($filter): bool {
                return (bool) ($filter ? $filter($element) : true);
            }
        );
    }
}
