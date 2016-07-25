<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Temporary\Tests;

use SR\File\Temporary\TemporaryFileInfo;
use SR\File\Temporary\TemporaryFileRegistry;
use SR\Reflection\Inspect;

/**
 * Class TemporaryFileInfoTest.
 */
class TemporaryFileInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $file1 = TemporaryFileInfo::create();
        $file2 = TemporaryFileInfo::create();
        $this->assertTrue(file_exists($file1->getPathname()));
        $this->assertTrue(file_exists($file2->getPathname()));
        $this->assertCount(2, TemporaryFileRegistry::getFiles());

        TemporaryFileRegistry::deleteAll();
        $this->assertFalse(file_exists($file1->getPathname()));
        $this->assertFalse(file_exists($file2->getPathname()));
        $this->assertCount(0, TemporaryFileRegistry::getFiles());
    }
    public function testRemove()
    {
        $file1 = TemporaryFileInfo::create();
        $file2 = TemporaryFileInfo::create();

        $this->assertCount(2, TemporaryFileRegistry::getFiles());

        $file1->disableAutomaticDeletion();
        $this->assertCount(1, TemporaryFileRegistry::getFiles());
        $this->assertTrue(file_exists($file1->getPathname()));
        $this->assertTrue(file_exists($file2->getPathname()));

        TemporaryFileRegistry::deleteAll();

        $this->assertCount(0, TemporaryFileRegistry::getFiles());
        $this->assertTrue(file_exists($file1->getPathname()));
        $this->assertFalse(file_exists($file2->getPathname()));

        @unlink($file1->getPathname());

        $file1 = TemporaryFileInfo::create();
        $file2 = TemporaryFileInfo::create();

        $this->assertCount(2, TemporaryFileRegistry::getFiles());

        $file1->disableAutomaticDeletion();
        $file1->enableAutomaticDeletion();
        $this->assertCount(2, TemporaryFileRegistry::getFiles());
        $this->assertTrue(file_exists($file1->getPathname()));
        $this->assertTrue(file_exists($file2->getPathname()));

        TemporaryFileRegistry::deleteAll();

        $this->assertCount(0, TemporaryFileRegistry::getFiles());
        $this->assertFalse(file_exists($file1->getPathname()));
        $this->assertFalse(file_exists($file2->getPathname()));
    }
}

/* EOF */
