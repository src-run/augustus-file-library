<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use SR\File\Metadata\ExtensionMetadata;
use SR\File\Tests\Fixture\Data\DataLoader;

/**
 * @covers \SR\File\Metadata\MetadataTrait
 * @covers \SR\File\Metadata\ExtensionMetadata
 */
class ExtensionMetadataTest extends TestCase
{
    /**
     * @return \Iterator
     */
    public function provideExtensionsData(): \Iterator
    {
        return self::fetchFixtureData();
    }

    /**
     * @dataProvider provideExtensionsData
     *
     * @param string $provided
     */
    public function testExtensions(string $provided)
    {
        $meta = ExtensionMetadata::create($provided);

        $this->assertTrue($meta->hasName());
        $this->assertSame($provided, $meta->getName());
        $this->assertSame($provided, $meta->stringify());
        $this->assertSame($provided, $meta->__toString());
        $this->assertTrue($meta->isValid());
        $this->assertTrue($meta->isMatch($provided));
        $this->assertFalse($meta->isMatch('foo-bar-baz'));
    }

    /**
     * @return \Iterator
     */
    public static function fetchFixtureData(): \Iterator
    {
        return (new DataLoader())(__CLASS__, 30);
    }
}
