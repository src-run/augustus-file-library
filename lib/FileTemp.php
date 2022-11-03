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

use SR\File\Exception\FileOperationException;
use SR\File\Lock\LockClosure;
use SR\Interpreter\Interpreter;

final class FileTemp extends AbstractFilePath implements FilePathInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $root;

    public function __construct(string $name = null, string $root = null)
    {
        parent::__construct();

        $this->setName($name);
        $this->setRoot($root);
    }

    /**
     * Automatically release the temporary file.
     */
    public function __destruct()
    {
        $this->release();
    }

    public static function create(string $name = null, string $root = null, string $contents = null): self
    {
        $temporary = new self($name, $root);

        if (null !== $contents) {
            $temporary->setBlob($contents);
        }

        return $temporary;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name = null): self
    {
        $this->requireReleasedState('failed to change context descriptor');
        $this->name = sprintf('imagine-bundle-temporary_%s', $name ?: 'general');

        return $this;
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function setRoot(string $path = null): self
    {
        $this->requireReleasedState('failed to change path prefix');
        $this->root = self::makePathIfNotExists($path ?? sys_get_temp_dir());

        return $this;
    }

    public function isAcquired(): bool
    {
        return $this->hasFile();
    }

    public function acquire(): self
    {
        $this->requireReleasedState('failed to acquire a new one');

        $this->file = LockClosure::blocking($this, function (): \SplFileInfo {
            if (false !== $file = @tempnam($this->getRoot(), $this->getName())) {
                return new \SplFileInfo($file);
            }

            throw new FileOperationException('Failed to acquire temporary file in "%s": %s.', $this->getRoot(), Interpreter::error()->text());
        });

        return $this;
    }

    public function release(bool $remove = true): self
    {
        if (!$this->isAcquired()) {
            return $this;
        }

        if ($remove) {
            $this->remove();
        }

        $this->file = null;

        return $this;
    }

    protected function doDumpBlob(string $string, bool $append): void
    {
        if (!$this->isAcquired()) {
            $this->acquire();
        }

        parent::doDumpBlob($string, $append);
    }

    private function requireReleasedState(string $message): void
    {
        if ($this->isAcquired()) {
            throw new FileOperationException('Temporary file must be released first: %s', $message);
        }
    }
}
