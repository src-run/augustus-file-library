<?php

/*
 * This file is part of the `src-run/augustus-storage-object-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Spl\File\Tests;

use SR\Spl\File\Exception\NotReadableException;
use SR\Spl\File\SplFileInfo;

/**
 * Class SplFileInfoTest.
 *
 * @covers \SR\Spl\File\SplFileInfo
 */
class SplFileInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSizeReadable()
    {
        $file = new SplFileInfo(__FILE__);

        $this->assertRegExp('{[0-9]+\.[0-9]+\s[MKB]}', $file->getSizeReadable());
    }

    public function testDates()
    {
        $file = new SplFileInfo(__FILE__);

        $this->assertInstanceOf('\DateTime', $file->getTimeAccessed());
        $this->assertInstanceOf('\DateTime', $file->getTimeChanged());
        $this->assertInstanceOf('\DateTime', $file->getTimeModified());
    }

    public function testContents()
    {
        $file = new SplFileInfo(__FILE__);

        $this->assertSame(file_get_contents(__FILE__), $file->getContents());
    }

    public function testContentsInvalidFile()
    {
        $file = new SplFileInfo(__FILE__.'IDK');

        $this->expectException(NotReadableException::class);
        $file->getContents();
    }

    public function testRegister()
    {
        $file = new \SplFileInfo(__FILE__);

        SplFileInfo::register($file);

        $this->assertInstanceOf(SplFileInfo::class, $file->getFileInfo());
    }
}

/* EOF */
