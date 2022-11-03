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

use SR\File\Exception\FileOperationException;
use SR\File\FileInterface;
use SR\File\FileTemp;

/**
 * @covers \SR\File\AbstractFile
 * @covers \SR\File\AbstractFilePath
 * @covers \SR\File\FileTemp
 */
class FileTempTest extends AbstractFileTest
{
    public function testInstanceOfFileInterface()
    {
        $this->assertInstanceOf(FileInterface::class, new FileTemp());
    }

    public function testAcquireAndRelease()
    {
        $temporary = new FileTemp();

        $this->assertSame(sys_get_temp_dir(), $temporary->getRoot());
        $this->assertStringStartsWith('imagine-bundle', $temporary->getName());
        $this->assertFalse($temporary->hasFile());
        $this->assertFalse($temporary->isFileExisting());
        $this->assertFalse($temporary->isFileReadable());
        $this->assertFalse($temporary->isFileWritable());
        $this->assertFalse($temporary->hasBlob());
        $this->assertNull($temporary->getBlob());

        $temporary->acquire();

        $this->assertTrue($temporary->hasFile());
        $this->assertStringStartsWith(sys_get_temp_dir(), $temporary->getFile()->getPathname());
        $this->assertStringStartsWith('imagine-bundle', $temporary->getFile()->getFilename());
        $this->assertTrue($temporary->isFileExisting());
        $this->assertTrue($temporary->isFileReadable());
        $this->assertTrue($temporary->isFileWritable());
        $this->assertTrue($temporary->hasBlob());
        $this->assertSame('', $temporary->getBlob());

        $temporary->setBlob('foobar');

        $this->assertTrue($temporary->hasFile());
        $this->assertTrue($temporary->isFileExisting());
        $this->assertTrue($temporary->isFileReadable());
        $this->assertTrue($temporary->isFileWritable());
        $this->assertSame('foobar', $temporary->getBlob());
        $this->assertTrue($temporary->hasBlob());
        $this->assertFileExists($file = $temporary->getFile()->getPathname());

        $temporary->release();

        $this->assertFalse($temporary->hasFile());
        $this->assertFalse($temporary->isFileExisting());
        $this->assertFalse($temporary->isFileReadable());
        $this->assertFalse($temporary->isFileWritable());
        $this->assertFalse($temporary->hasBlob());
        $this->assertNull($temporary->getBlob());
        $this->assertFileDoesNotExist($file);
    }

    public function testAutomaticallyAcquiredOnSetContents()
    {
        $temporary = new FileTemp();

        $this->assertFalse($temporary->isAcquired());
        $temporary->setBlob('foobar');
        $this->assertTrue($temporary->isAcquired());
        $this->assertSame('foobar', $temporary->getBlob());
        $temporary->release();
    }

    public function testThrowsOnSetTmpContextWhileAcquired()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('failed to change context descriptor');

        $temporary = new FileTemp();
        $temporary->acquire();
        $temporary->setName('foobar');
    }

    public function testThrowsOnSetPathPrefixWhileAcquired()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('failed to change path prefix');

        $temporary = new FileTemp();
        $temporary->acquire();
        $temporary->setRoot('foobar');
    }

    public static function provideBlobData(): \Iterator
    {
        foreach (self::getRandomContentData(20) as $blob) {
            yield [$blob];
        }
    }

    /**
     * @dataProvider provideBlobData
     */
    public function testStaticConstruction(string $blob): void
    {
        $temp = FileTemp::create(null, null, $blob);

        $this->assertSame($blob, $temp->getBlob());
        $this->assertSame(file_get_contents($temp->stringifyFile()), $temp->getBlob());
    }
}
