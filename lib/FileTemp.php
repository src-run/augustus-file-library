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

    /**
     * @param string|null $name
     * @param string|null $root
     */
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

    /**
     * @param string|null $name
     * @param string|null $root
     * @param string|null $contents
     *
     * @return self
     */
    public static function create(string $name = null, string $root = null, string $contents = null): self
    {
        $temporary = new self($name, $root);

        if (null !== $contents) {
            $temporary->setBlob($contents);
        }

        return $temporary;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return self
     */
    public function setName(string $name = null): self
    {
        $this->requireReleasedState('failed to change context descriptor');
        $this->name = sprintf('imagine-bundle-temporary_%s', $name ?: 'general');

        return $this;
    }

    /**
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * @param string|null $path
     *
     * @return FileTemp
     */
    public function setRoot(string $path = null): self
    {
        $this->requireReleasedState('failed to change path prefix');
        $this->root = self::makePathIfNotExists($path ?? sys_get_temp_dir());

        return $this;
    }

    /**
     * @return bool
     */
    public function isAcquired(): bool
    {
        return $this->hasFile();
    }

    /**
     * @return self
     */
    public function acquire(): self
    {
        $this->requireReleasedState('failed to acquire a new one');

        $this->file = LockClosure::blocking($this, function (): \SplFileInfo {
            if (false !== $file = @tempnam($this->getRoot(), $this->getName())) {
                return new \SplFileInfo($file);
            }

            throw new FileOperationException(
                'Failed to acquire temporary file in "%s": %s.', $this->getRoot(), Interpreter::error()->text()
            );
        });

        return $this;
    }

    /**
     * @param bool $remove
     *
     * @return self
     */
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

    /**
     * @param string $string
     * @param bool   $append
     */
    protected function doDumpBlob(string $string, bool $append): void
    {
        if (!$this->isAcquired()) {
            $this->acquire();
        }

        parent::doDumpBlob($string, $append);
    }

    /**
     * @param string $message
     */
    private function requireReleasedState(string $message): void
    {
        if ($this->isAcquired()) {
            throw new FileOperationException('Temporary file must be released first: %s', $message);
        }
    }
}
