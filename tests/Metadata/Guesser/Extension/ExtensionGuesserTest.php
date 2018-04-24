<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Metadata\Guesser\Extension;

use PHPUnit\Framework\TestCase;
use SR\File\FilePath;
use SR\File\Metadata\Guesser\Extension\ExtensionGuesser;
use SR\File\Metadata\Guesser\Extension\Resolver\ExtensionResolverInterface;
use SR\File\Metadata\ExtensionMetadata;
use SR\File\Metadata\Guesser\Extension\Resolver\ExtensionResolverTrait;
use SR\File\Metadata\Guesser\Extension\Resolver\FileNameExtensionResolver;
use SR\File\Metadata\Guesser\Extension\Resolver\MediaTypeExtensionResolver;
use SR\File\Metadata\Guesser\MediaType\MediaTypeGuesser;
use Symfony\Component\Finder\Finder;

/**
 * @covers \SR\File\FilePathCastTrait
 * @covers \SR\File\Metadata\Guesser\Extension\ExtensionGuesser
 * @covers \SR\File\Metadata\Guesser\Extension\Resolver\FileNameExtensionResolver
 * @covers \SR\File\Metadata\Guesser\Extension\Resolver\MediaTypeExtensionResolver
 * @covers \SR\File\Metadata\Guesser\Extension\Resolver\ExtensionResolverTrait
 */
class ExtensionGuesserTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        ExtensionGuesser::setInstance();
        MediaTypeGuesser::setInstance();
    }

    public function tearDown()
    {
        parent::tearDown();

        ExtensionGuesser::setInstance();
        MediaTypeGuesser::setInstance();
    }

    public static function provideFileData(): \Iterator
    {
        $guesser = MediaTypeGuesser::getInstance();
        $extNorm = function ($extension) {
            switch ($extension) {
                case 'php':
                case 'xml';
                    return $extension;

                default:
                    return 'txt';
            }
        };

        $finder = new Finder();
        $finder
            ->in(realpath(sprintf('%s/../../../../lib', __DIR__)))
            ->size('> 1K')
            ->name('*.php');

        foreach ($finder->files() as $file) {
            yield [new FilePath($file, $guesser->guess(new FilePath($file))), $extNorm('php'), pathinfo($file, PATHINFO_EXTENSION)];
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
            yield [new FilePath($file, $guesser->guess(new FilePath($file))), $extNorm(pathinfo($file, PATHINFO_EXTENSION)), pathinfo($file, PATHINFO_EXTENSION)];
        }

        $finder = new Finder();
        $finder
            ->in(realpath(sprintf('%s/../../../../vendor', __DIR__)))
            ->size('> 1K')
            ->name('*.xml');

        foreach ($finder->files() as $file) {
            yield [new FilePath($file, $guesser->guess(new FilePath($file))), $extNorm('xml'), pathinfo($file, PATHINFO_EXTENSION)];
        }
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     * @param string   $extension
     */
    public function testGuess(FilePath $file, string $extension)
    {
        $guesser = ExtensionGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, $extension);
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     * @param string   $extension
     */
    public function testGuessWithFileNameResolver(FilePath $file, string $extensionNorm, string $extension)
    {
        $resolvers = [new FileNameExtensionResolver()];
        $guesser = ExtensionGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, $extension, $resolvers);
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     * @param string   $extension
     */
    public function testGuessWithMediaTypeResolver(FilePath $file, string $extension)
    {
        $resolvers = [new MediaTypeExtensionResolver()];
        $guesser = ExtensionGuesser::getInstance();

        $this->assertGuesserFunctions($guesser, $file, $extension, $resolvers);
    }

    /**
     * @dataProvider provideFileData
     *
     * @param FilePath $file
     */
    public function testGuessWithNotSupportedResolver(FilePath $file)
    {
        $resolvers = [$this->createNotSupportedResolver()];
        $guesser = ExtensionGuesser::getInstance();
        $guesser->resetResolver();
        $guesser->registerResolvers(...$resolvers);

        $this->assertSame($resolvers, $guesser->getResolvers());
        $this->assertNull($guesser->guess($file));
    }

    /**
     * @param ExtensionGuesser $guesser
     * @param FilePath         $file
     * @param string           $extension
     * @param array|null       $resolvers
     */
    private function assertGuesserFunctions(ExtensionGuesser $guesser, FilePath $file, string $extension, array $resolvers = null): void
    {
        if (null !== $resolvers) {
            $guesser->resetResolver();
            $guesser->registerResolvers(...$resolvers);

            $this->assertSame($resolvers, $guesser->getResolvers());
        }

        $this->assertCount(null === $resolvers ? 2 : count($resolvers), $guesser->getResolvers());
        $this->assertSame($extension, $guesser->guess($file)->stringify());

        $this->assertSame($guesser, ExtensionGuesser::getInstance());

        $guesser->resetResolver();

        $this->assertNull($guesser->guess($file));

        ExtensionGuesser::setInstance();

        $this->assertNotSame($guesser, ExtensionGuesser::getInstance());
    }

    private function createNotSupportedResolver(): ExtensionResolverInterface
    {
        return new class() implements ExtensionResolverInterface
        {
            use ExtensionResolverTrait;

            /**
             * @return bool
             */
            public static function isSupported()
            {
                return false;
            }

            /**
             * @return null|ExtensionMetadata
             */
            public function doResolveFile(): ?ExtensionMetadata
            {
                return null;
            }
        };
    }
}
