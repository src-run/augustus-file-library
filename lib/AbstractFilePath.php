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

use SR\File\Exception\FileFailedDumpException;
use SR\File\Exception\FileFailedMakeException;
use SR\File\Exception\FileFailedOustException;
use SR\File\Lock\LockClosure;
use SR\Interpreter\Interpreter;

/**
 * @internal
 */
abstract class AbstractFilePath extends AbstractFileBlob
{
    /**
     * @var \SplFileInfo|null
     */
    protected $file;

    /**
     * {@inheritdoc}
     */
    public function getFile(): ?\SplFileInfo
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function isFileExisting(): bool
    {
        if (!$this->hasFile() || !($path = $this->stringifyFile())) {
            return false;
        }

        return is_file($path) && file_exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isPathExisting(): bool
    {
        return $this->hasFile() ? file_exists($this->getPath()) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFileReadable(): bool
    {
        return $this->isFileExisting() && is_readable($this->stringifyFile());
    }

    /**
     * {@inheritdoc}
     */
    public function isPathReadable(): bool
    {
        return $this->isPathExisting() && is_readable($this->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public function isFileWritable(): bool
    {
        return $this->isFileExisting() && is_writable($this->stringifyFile());
    }

    /**
     * {@inheritdoc}
     */
    public function isPathWritable(): bool
    {
        return $this->isPathExisting() && is_writable($this->getPath());
    }

    /**
     * @throws FileFailedOustException
     *
     * @return self
     */
    public function remove(): self
    {
        LockClosure::blocking($this, function (): void {
            if ($this->isFileExisting() && false === @unlink($this->stringifyFile()) && $this->isFileExisting()) {
                throw new FileFailedOustException(
                    'Failed to remove file "%s": %s', $this->file->getPathname(), Interpreter::error()->text()
                );
            }
        });

        return $this;
    }

    /**
     * @return null|string
     */
    protected function doReadBlob(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }

        return LockClosure::blocking($this, function (): ?string {
            if (false !== $contents = @file_get_contents($this->stringifyFile())) {
                return $contents;
            }

            return null;
        });
    }

    /**
     * @param string $string
     * @param bool   $append
     *
     * @throws FileFailedMakeException|FileFailedDumpException
     */
    protected function doDumpBlob(string $string, bool $append): void
    {
        LockClosure::blocking($this, function () use ($string, $append): void {
            self::makePathIfNotExists($this->getPath());
            self::dumpContentsForFile($this->stringifyFile(), $string, $append);
        });
    }

    /**
     * @param string $path
     *
     * @throws FileFailedMakeException
     *
     * @return string
     */
    protected static function makePathIfNotExists(string $path): string
    {
        if (false === is_dir($path) && false === @mkdir($path, 0777, true) && false === is_dir($path)) {
            throw new FileFailedMakeException(
                'Failed to create file "%s": %s', $path, Interpreter::error()->text()
            );
        }

        if (false !== $real = @realpath($path)) {
            return $real;
        }

        return $path;
    }

    /**
     * @param string $file
     * @param string $contents
     * @param bool   $append
     */
    private static function dumpContentsForFile(string $file, string $contents, bool $append): void
    {
        if (false === @file_put_contents($file, $contents, $append ? FILE_APPEND : 0)) {
            throw new FileFailedDumpException(
                'Failed to write contents of "%s": %s.', $file, Interpreter::error()->text()
            );
        }
    }
}
