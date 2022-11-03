<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use SR\File\Exception\Metadata\InvalidArgumentException;
use SR\File\Metadata\MediaTypeMetadata;
use SR\File\Tests\Fixture\Data\DataLoader;

/**
 * @covers \SR\File\Metadata\MetadataTrait
 * @covers \SR\File\Metadata\MediaTypeMetadata
 */
class MediaTypeMetadataTest extends TestCase
{
    /**
     * @return \Iterator|string[]
     */
    public static function provideContentTypeData(): \Iterator
    {
        foreach (self::fetchFixtureData() as $data) {
            yield $data;
        }
    }

    /**
     * @dataProvider provideContentTypeData
     */
    public function testContentTypes(string $provided, string $type, string $subType, string $prefix = null, string $suffix = null, string $prefixDeliminator = null)
    {
        $this->assertTrue(MediaTypeMetadata::isParsable($provided));

        $meta = MediaTypeMetadata::create($provided);

        $this->assertTrue($meta->isValid());
        $this->assertSame($provided, $meta->stringify());
        $this->assertSame($provided, $meta->__toString());
        $this->assertSame($provided, (string) $meta);

        $this->assertTrue($meta->hasType());
        $this->assertSame($type, $meta->getType());
        $this->assertTrue($meta->isTypeMatch($type));
        $this->assertFalse($meta->isTypeMatch('foobar'));

        $this->assertTrue($meta->hasSubType());
        $this->assertSame($subType, $meta->getSubType());
        $this->assertTrue($meta->isSubTypeMatch($subType));
        $this->assertFalse($meta->isSubTypeMatch('foobar'));

        if (null !== $prefix) {
            $this->assertTrue($meta->hasPrefix());
            $this->assertSame($prefix, $meta->getPrefix());
            $this->assertTrue($meta->isPrefixMatch($prefix));
            $this->assertFalse($meta->isPrefixMatch('foobar'));
            $this->assertTrue($meta->hasDeliminator());
            $this->assertSame($prefixDeliminator, $meta->getDeliminator());
            $this->assertTrue($meta->isDeliminatorMatch($prefixDeliminator));
            $this->assertFalse($meta->isDeliminatorMatch('foobar'));
        } else {
            $this->assertFalse($meta->hasPrefix());
            $this->assertNull($meta->getPrefix());
            $this->assertTrue($meta->isPrefixMatch(null));
            $this->assertFalse($meta->isPrefixMatch('foobar'));
            $this->assertFalse($meta->hasDeliminator());
            $this->assertNull($meta->getDeliminator());
            $this->assertTrue($meta->isDeliminatorMatch(null));
            $this->assertFalse($meta->isDeliminatorMatch('foobar'));
        }

        if (null !== $suffix) {
            $this->assertTrue($meta->hasSuffix());
            $this->assertSame($suffix, $meta->getSuffix());
            $this->assertTrue($meta->isSuffixMatch($suffix));
            $this->assertFalse($meta->isSuffixMatch('foobar'));
        } else {
            $this->assertFalse($meta->hasSuffix());
            $this->assertNull($meta->getSuffix());
            $this->assertTrue($meta->isSuffixMatch(null));
            $this->assertFalse($meta->isSuffixMatch('foobar'));
        }
    }

    /**
     * @return \Iterator|string[]
     */
    public static function provideInvalidContentTypeData(): \Iterator
    {
        yield [null];
        yield ['foobar'];
    }

    /**
     * @dataProvider provideInvalidContentTypeData
     */
    public function testInvalidContentTypes(string $provided = null)
    {
        $this->assertNull(MediaTypeMetadata::explodeParsable($provided));
        $this->assertFalse(MediaTypeMetadata::isParsable($provided));

        $meta = MediaTypeMetadata::create($provided);

        $this->assertFalse($meta->hasType());
        $this->assertFalse($meta->hasSubType());
        $this->assertFalse($meta->hasPrefix());
        $this->assertFalse($meta->hasSuffix());
    }

    /**
     * @return \Iterator|string[]|bool[]
     */
    public static function provideVendorTypeData(): \Iterator
    {
        yield ['foo/bar'];
        yield ['foo/bar+baz'];
        yield ['foo/x.bar', 'x'];
        yield ['foo/x.bar+baz', 'x'];
        yield ['foo/vnd.bar', 'vnd'];
        yield ['foo/vnd.bar+baz', 'vnd'];
        yield ['foo/prs.bar', 'prs'];
        yield ['foo/prs.bar+baz', 'prs'];
    }

    /**
     * @dataProvider provideVendorTypeData
     */
    public function testVendorTypes(string $provided, string $vendor = null)
    {
        $mime = MediaTypeMetadata::create($provided);

        $this->assertSame($vendor, $mime->getPrefix());

        switch ($vendor) {
            case 'x':
                $this->assertFalse($mime->isPrefixStandard());
                $this->assertTrue($mime->isPrefixUnregistered());
                $this->assertFalse($mime->isPrefixVendor());
                $this->assertFalse($mime->isPrefixPersonal());
                break;

            case 'vnd':
                $this->assertFalse($mime->isPrefixStandard());
                $this->assertFalse($mime->isPrefixUnregistered());
                $this->assertTrue($mime->isPrefixVendor());
                $this->assertFalse($mime->isPrefixPersonal());
                break;

            case 'prs':
                $this->assertFalse($mime->isPrefixStandard());
                $this->assertFalse($mime->isPrefixUnregistered());
                $this->assertFalse($mime->isPrefixVendor());
                $this->assertTrue($mime->isPrefixPersonal());
                break;

            default:
                $this->assertTrue($mime->isPrefixStandard());
                $this->assertFalse($mime->isPrefixUnregistered());
                $this->assertFalse($mime->isPrefixVendor());
                $this->assertFalse($mime->isPrefixPersonal());
        }
    }

    public static function provideEquivalenceData(): \Iterator
    {
        return self::fetchFixtureData();
    }

    /**
     * @dataProvider provideEquivalenceData
     */
    public function testIsEquivalent(string $provided = null, string $type, string $subType, string $prefix = null, string $suffix = null)
    {
        $meta = MediaTypeMetadata::create($provided);

        $this->assertTrue($meta->isMatch($type, $subType, $prefix, $suffix));

        $this->assertTrue($meta->isMatch($type));
        $this->assertTrue($meta->isMatch($type, $subType));
        $this->assertTrue($meta->isMatch($type, $subType, $prefix));
        $this->assertTrue($meta->isMatch($type, $subType, $prefix, $suffix));
        $this->assertTrue($meta->isMatch($type, null, $prefix));
        $this->assertTrue($meta->isMatch($type, null, null, $suffix));
    }

    public function testToStringOnNullFileType()
    {
        $this->assertEmpty((string) MediaTypeMetadata::create());
        $this->assertEmpty(MediaTypeMetadata::create()->__toString());
        $this->assertEmpty(MediaTypeMetadata::create()->stringify());
    }

    public function provideThrowsOnInvalidMimeTypesData(): \Iterator
    {
        yield ['foo$'];
        yield ['foo', 'bar$'];
        yield ['foo', 'bar', 'baz'];
        yield ['foo', 'bar', 'x', 'baz$'];
        yield ['foo', 'bar', 'x', 'baz', 'qux'];
    }

    /**
     * @dataProvider provideThrowsOnInvalidMimeTypesData
     */
    public function testThrowsOnInvalidMimeTypes(string $type, string $subType = null, string $prefix = null, string $suffix = null, string $deliminator = null)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('{Invalid (mime type )?(prefix|deliminator|character\(s\)) "[^"]+" provided (in "[^"]+" )?\(accepted values are ".+"\)\.}');

        new MediaTypeMetadata($type, $subType, $prefix, $suffix, $deliminator);
    }

    public function provideNonParsableData()
    {
        yield ['foo$/bar'];
        yield ['foo/bar$'];
        yield ['foo/bar+baz$'];
        yield ['foo/x.bar+baz$'];
    }

    /**
     * @dataProvider provideNonParsableData
     */
    public function testNonParable(string $provided)
    {
        $this->assertFalse(MediaTypeMetadata::isParsable($provided));
    }

    public static function fetchFixtureData(): \Iterator
    {
        return (new DataLoader())(__CLASS__, 30);
    }
}
