<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Metadata\Guesser\MediaType;

use SR\File\Exception\FileNotExistingException;
use SR\File\Exception\FileNotReadableException;
use SR\File\FileBlob;
use SR\File\FileInterface;
use SR\File\FilePath;
use SR\File\Metadata\Guesser\MediaType\MediaTypeGuesser;
use SR\File\Metadata\Guesser\MediaType\Resolver\FileBinaryMediaTypeResolver;
use SR\File\Metadata\Guesser\MediaType\Resolver\FileinfoMediaTypeResolver;
use SR\File\Metadata\Guesser\MediaType\Resolver\MediaTypeResolverInterface;
use SR\File\Metadata\Guesser\MediaType\Resolver\MediaTypeResolverTrait;
use SR\File\Metadata\MediaTypeMetadata;
use SR\File\Tests\AbstractFileTest;
use Symfony\Component\Finder\Finder;

/**
 * @covers \SR\File\FilePathCastTrait
 * @covers \SR\File\Metadata\Guesser\MediaType\MediaTypeGuesser
 * @covers \SR\File\Metadata\Guesser\MediaType\Resolver\FileBinaryMediaTypeResolver
 * @covers \SR\File\Metadata\Guesser\MediaType\Resolver\FileinfoMediaTypeResolver
 * @covers \SR\File\Metadata\Guesser\MediaType\Resolver\MediaTypeResolverTrait
 */
class MediaTypeGuesserTest extends AbstractFileTest
{
    public function setUp(): void
    {
        parent::setUp();

        MediaTypeGuesser::setInstance();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        MediaTypeGuesser::setInstance();
    }

    public static function provideFileData(): \Iterator
    {
        $finder = new Finder();
        $finder
            ->in(realpath(sprintf('%s/../../../../lib', __DIR__)))
            ->size('> 1K')
            ->name('*.php');

        foreach ($finder->files() as $file) {
            yield [FilePath::create($file), 'text/x-php'];
        }

        $finder = new Finder();
        $finder
            ->in(realpath(sprintf('%s/../../../../vendor', __DIR__)))
            ->size('> 1K')
            ->name('*.txt')
            ->name('*.yaml')
            ->name('*.yml')
            ->name('*.json');

        foreach ($finder->files() as $file) {
            yield [FilePath::create($file), 'text/plain'];
        }
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     * @param string   $mediaType
     */
    public function testGuess(FilePath $file, string $mediaType)
    {
        $guesser = MediaTypeGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, $mediaType);
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     * @param string   $mediaType
     */
    public function testGuessWithFileBinaryResolver(FilePath $file, string $mediaType)
    {
        $resolvers = [new FileBinaryMediaTypeResolver()];
        $guesser = MediaTypeGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, $mediaType, $resolvers);
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     * @param string   $mediaType
     */
    public function testGuessWithFileInfoResolver(FilePath $file, string $mediaType)
    {
        $resolvers = [new FileinfoMediaTypeResolver()];
        $guesser = MediaTypeGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, $mediaType, $resolvers);
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     */
    public function testGuessWithNotSupportedResolver(FilePath $file)
    {
        $resolvers = [$this->createNotSupportedResolver()];
        $guesser = MediaTypeGuesser::getInstance();
        $guesser->resetResolver();
        $guesser->registerResolvers(...$resolvers);

        $this->assertSame($resolvers, $guesser->getResolvers());
        $this->assertNull($guesser->guess($file));
    }

    public function testGuessWithBlob()
    {
        $file = new FileBlob();
        $file->setBlob('content');

        $guesser = MediaTypeGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, 'text/plain');
    }

    public function testGuessThrowsWhenNotExisting()
    {
        $this->expectException(FileNotExistingException::class);

        $name = $this->createFunctionFile('file.ext');
        $file = new FilePath($name);
        unlink($name);

        $guesser = MediaTypeGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, 'text/plain');
    }

    public function testGuessThrowsWhenNotReadable()
    {
        $this->expectException(FileNotReadableException::class);

        $name = $this->createFunctionFile('file.ext');
        chmod($name, 0000);

        $file = new FilePath($name);

        $guesser = MediaTypeGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, 'text/plain');
    }

    /**
     * @param MediaTypeGuesser $guesser
     * @param FileInterface    $file
     * @param string           $mediaType
     * @param array|null       $resolvers
     */
    private function assertGuesserFunctions(MediaTypeGuesser $guesser, FileInterface $file, string $mediaType, array $resolvers = null): void
    {
        if (null !== $resolvers) {
            $guesser->resetResolver();
            $guesser->registerResolvers(...$resolvers);

            $this->assertSame($resolvers, $guesser->getResolvers());
        }

        $this->assertCount(null === $resolvers ? 2 : count($resolvers), $guesser->getResolvers());
        $this->assertSame($mediaType, $guesser->guess($file)->stringify());

        $this->assertSame($guesser, MediaTypeGuesser::getInstance());

        $guesser->resetResolver();

        $this->assertNull($guesser->guess($file));

        MediaTypeGuesser::setInstance();

        $this->assertNotSame($guesser, MediaTypeGuesser::getInstance());
    }

    private function createNotSupportedResolver(): MediaTypeResolverInterface
    {
        return new class() implements MediaTypeResolverInterface
        {
            use MediaTypeResolverTrait;

            /**
             * @return bool
             */
            public static function isSupported()
            {
                return false;
            }

            /**
             * @return null|MediaTypeMetadata
             */
            public function doResolveFile(): ?MediaTypeMetadata
            {
                return null;
            }
        };
    }
}
