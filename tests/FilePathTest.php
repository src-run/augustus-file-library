<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests;

use SR\File\Exception\FileFailedDumpException;
use SR\File\FileInterface;
use SR\File\FilePath;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamException;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Component\Finder\Finder;

/**
 * @covers \SR\File\AbstractFile
 * @covers \SR\File\AbstractFilePath
 * @covers \SR\File\FilePath
 */
class FilePathTest extends AbstractFileTest
{
    /**
     * @var vfsStreamDirectory
     */
    private $filesystemRoot;

    /**
     * @var vfsStreamDirectory
     */
    private $filesystemWork;

    /**
     * Setup our virtual filesystem environment.
     */
    public function setUp(): void
    {
        if (!class_exists(vfsStream::class)) {
            $this->markTestSkipped(sprintf('Requires "%s"', vfsStream::class));
        }

        parent::setUp();

        try {
            $this->filesystemRoot = vfsStream::setup('php-unit', 0700);
            $this->filesystemRoot->chown(getmyuid());
            $this->filesystemRoot->chgrp(getmygid());
            $this->filesystemWork = new vfsStreamDirectory(self::normalizeClassOrMethodName(__CLASS__), 0777);
            $this->filesystemWork->at($this->filesystemRoot);
        } catch (vfsStreamException $e) {
            $this->fail(sprintf('Failed creating virtual filesystem base: %s', $e->getMessage()));
        }
    }

    public function testInstanceOfFileInterface(): void
    {
        $this->assertInstanceOf(FileInterface::class, new FilePath());
    }

    /**
     * @return \Iterator
     */
    public static function provideContentsData(): \Iterator
    {
        $finder = (new Finder())
            ->in(__DIR__)
            ->name('*.php')
            ->ignoreUnreadableDirs(true);

        foreach ($finder->files() as $f) {
            yield [$f];
        }
    }

    /**
     * @dataProvider provideContentsData
     *
     * @param string $contents
     */
    public function testSettersAndAccesses(string $contents)
    {
        $path = $this->createFakeFileName(__METHOD__, $this->filesystemWork->url());
        $file = $this->createFileReference($path, 'type/sub-type', 'ext');

        $this->assertValidUuid($file->getUuid());
        $this->assertSame($file->getUuid(), $file->getUuid());

        $this->assertSame($path, $file->getFile()->getPathname());
        $this->assertSame($file->getFileUuid()->toString(), (string) $file);
        $this->assertTrue($file->hasFile());
        $this->assertFalse($file->isFileExisting());
        $this->assertFalse($file->isFileReadable());
        $this->assertFalse($file->isFileWritable());
        $this->assertTrue($file->isPathExisting());
        $this->assertTrue($file->isPathReadable());
        $this->assertTrue($file->isPathWritable());
        $this->assertNull($file->getBlob());
        $this->assertFalse($file->hasBlob());

        $copy = FilePath::create($file->stringifyFile());
        $this->assertSame($file->getUuid()->toString(), $copy->getUuid()->toString());

        $file->setBlob($contents);

        $this->assertTrue($file->isFileExisting());
        $this->assertTrue($file->isFileReadable());
        $this->assertTrue($file->isFileWritable());
        $this->assertSame($contents, $file->getBlob());
        $this->assertTrue($file->hasBlob());

        $file->setBlob($contents);

        $this->assertSame($contents, $file->getBlob());

        $file->setBlob('');

        for ($i = 1; $i < 8; ++$i) {
            $file->setBlob($contents, true);
            $this->assertSame(str_repeat($contents, $i), $file->getBlob());
        }

        $file->remove();

        $this->assertFalse($file->isFileExisting());
    }

    /**
     * @group exceptions
     */
    public function testThrowsIfDumpFails(): void
    {
        $this->expectException(FileFailedDumpException::class);
        $this->expectExceptionMessageRegExp('{Failed to write contents of "vfs://php-unit/[^/]+/[^\.]+.ext": [^\.]+.}');

        $fake = $this->createFakeFile(__FUNCTION__, 'foobar', 0000, vfsStream::getCurrentGroup() + 1, vfsStream::getCurrentGroup() + 1);
        $file = $this->createFileReference($fake);

        $file->setBlob('foobar');
    }

    public function testRealOperations(): void
    {
        $file = FilePath::create($raw = $this->createFunctionFile('file.ext', 'foo/bar'));

        $this->checkFileTimeMethods($file, new \DateTime('@'.$file->getFile()->getATime()), new \DateTime('@'.$file->getFile()->getCTime()), new \DateTime('@'.$file->getFile()->getMTime()), 0);

        $this->assertValidUuid($file->getUuid());
        $this->assertSame($file->getUuid(), $file->getUuid());

        $this->assertInstanceOf(\SplFileInfo::class, $file->getFile());
        $this->assertSame($raw, $file->getFile()->getPathname());
        $this->assertTrue($file->hasFile());
        $this->assertSame(pathinfo($raw, PATHINFO_BASENAME), $file->getFileName());
        $this->assertSame(pathinfo($raw, PATHINFO_EXTENSION), $file->getFileExtension());
        $this->assertSame(sprintf('file://%s', $raw), $file->getFileUrl());
        $this->assertSame($raw, $file->stringifyFile());
        $this->assertSame(pathinfo($raw, PATHINFO_EXTENSION), $file->getPathInfo(PATHINFO_EXTENSION));
        $this->assertSame(pathinfo($raw, PATHINFO_FILENAME), $file->getPathInfo(PATHINFO_FILENAME));
        $this->assertSame(pathinfo($raw, PATHINFO_DIRNAME), $file->getPathInfo(PATHINFO_DIRNAME));
        $this->assertSame(pathinfo($raw, PATHINFO_BASENAME), $file->getPathInfo(PATHINFO_BASENAME));
        $this->assertSame(pathinfo($raw, PATHINFO_DIRNAME), $file->getPath());
        $this->checkFileSizeMethods($file, $file->getBlob());
        $this->checkFileMediaTypeAndExtensionMethods();

        $this->assertTrue($file->isFileExisting());
        $this->assertTrue($file->isFileReadable());
        $this->assertTrue($file->isFileWritable());
        $this->assertTrue($file->isPathExisting());
        $this->assertTrue($file->isPathReadable());
        $this->assertTrue($file->isPathWritable());
    }

    /**
     * @param string|vfsStreamFile $file
     * @param string|null          $contentType
     * @param string|null          $extension
     *
     * @return FilePath
     */
    private function createFileReference($file, string $contentType = null, string $extension = null): FilePath
    {
        return FilePath::create(
            $file instanceof vfsStreamFile ? $file->url() : $file,
            $contentType ?? 'content-type/sub-type',
            $extension ?? 'ext'
        );
    }

    /**
     * @param string      $method
     * @param string|null $contents
     * @param int|null    $permissions
     * @param int|null    $u
     * @param int|null    $g
     * @param bool        $add
     *
     * @return vfsStreamFile
     */
    private function createFakeFile(string $method, string $contents = null, int $permissions = null, int $u = null, int $g = null, bool $add = true): vfsStreamFile
    {
        $file = new vfsStreamFile(self::createFakeFileName($method), $permissions);

        if (null !== $contents) {
            $file->setContent('foobar');
        }

        if (-1 !== $u) {
            $file->chown($u ?: vfsStream::getCurrentUser());
        }

        if (-1 !== $g) {
            $file->chgrp($g ?: vfsStream::getCurrentGroup());
        }

        if (true === $add) {
            $this->filesystemWork->addChild($file);
        }

        return $file;
    }

    /**
     * @param string      $method
     * @param string|null $root
     *
     * @return string
     */
    private static function createFakeFileName(string $method, string $root = null): string
    {
        $name = sprintf(
            '%s-%s.ext', self::normalizeClassOrMethodName($method), mt_rand(10000000000, 99999999999)
        );

        if (null !== $root) {
            $name = sprintf('%s/%s', $root, $name);
        }

        return $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private static function normalizeClassOrMethodName(string $name): string
    {
        return mb_strtolower(ltrim(preg_replace('{[A-Z]([a-z]+)}', '-$0',
                preg_replace('{^.+\\\}i', '', get_called_class())
        ), '-'));
    }
}
