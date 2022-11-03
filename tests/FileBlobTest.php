<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests;

use SR\File\FileBlob;
use SR\File\FileInterface;

/**
 * @covers \SR\File\AbstractFile
 * @covers \SR\File\AbstractFileBlob
 * @covers \SR\File\FileBlob
 */
class FileBlobTest extends AbstractFileTest
{
    public function testImplementsFileInterface()
    {
        $this->assertInstanceOf(FileInterface::class, new FileBlob());
    }

    public static function provideBlobData(): \Iterator
    {
        foreach (self::getRandomContentData() as $citation => $string) {
            yield [$string, $citation];
        }
    }

    /**
     * @dataProvider provideBlobData
     */
    public function testGettersAndSetters(string $string)
    {
        $lines = explode(PHP_EOL, $string);
        $count = count($lines);
        $file = FileBlob::create($content = $string, 'image/jpeg', 'jpg');
        $time = new \DateTime('@' . time());

        $this->checkFileTimeMethods($file, $time);

        $this->assertValidUuid($file->getUuid());
        $this->assertSame($file->getUuid(), $file->getUuid());
        $this->assertNilUuid($file->getFileUuid());
        $this->assertValidUuid($file->getBlobUuid());

        $this->assertValidUuid((string) $file);
        $this->assertValidUuid($file->stringify());
        $this->assertValidUuid($file->getIdentity());
        $this->assertValidUuid((string) $file);

        $this->assertSame($string, $file->getBlob());
        $this->assertSame(hash('sha512', $string), $file->getBlobHash());
        $this->assertSame(mb_strlen($string), $file->getBlobLength());
        $this->assertSame($count, $file->getBlobLinesCount());
        $this->assertSame($lines, $file->getBlobLines());

        foreach ($file->yieldBlobLines() as $i => $l) {
            $this->assertSame($lines[$i], $l);
        }

        $this->assertTrue($file->getMediaType()->isMatch('image', 'jpeg'));
        $this->assertTrue($file->hasMediaType());
        $this->assertSame('image/jpeg', $file->stringifyMediaType());

        $this->assertTrue($file->getExtension()->isMatch('jpg'));
        $this->assertTrue($file->hasExtension());
        $this->assertSame('jpg', $file->stringifyExtension());

        $this->assertFalse($file->hasFile());
        $this->assertNull($file->getFile());
        $this->assertNull($file->getFileName());
        $this->assertNull($file->getFileBaseName());
        $this->assertNull($file->getFileExtension());
        $this->assertNull($file->getFileUrl());
        $this->assertCount($count, $file->getBlobLines());
        $this->assertEmpty($file->stringifyFile());
        $this->assertNull($file->getPathInfo(PATHINFO_BASENAME));
        $this->assertNull($file->getPathInfo(PATHINFO_DIRNAME));
        $this->assertNull($file->getPathInfo(PATHINFO_FILENAME));
        $this->assertNull($file->getPathInfo(PATHINFO_EXTENSION));

        $this->checkFileSizeMethods($file, $file->getBlob());
        $this->checkFileMediaTypeAndExtensionMethods();

        $this->assertNull($file->getPath());

        $copy = FileBlob::create($file->getBlob());
        $this->assertSame($file->getUuid()->toString(), $copy->getUuid()->toString());
        $alt = FileBlob::create($file->getBlob() . 'alt');
        $this->assertNotSame($file->getUuid()->toString(), $alt->getUuid()->toString());

        $file->setBlob('foobar');
        $this->assertSame('foobar', $file->getBlob());
        $this->assertSame(6, $file->getBlobLength());
        $file->setBlob('-baz', true);
        $this->assertSame('foobar-baz', $file->getBlob());
        $this->assertSame(10, $file->getBlobLength());

        $file = FileBlob::create();

        $this->assertNull($file->getBlob());
        $this->assertSame(0, $file->getBlobLength());
        $this->assertFalse($file->hasMediaType());
        $this->assertFalse($file->hasExtension());
    }
}
