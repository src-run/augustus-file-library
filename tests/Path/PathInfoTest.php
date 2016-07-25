<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Path\Tests;

use SR\Path\PathInfo;

/**
 * Class PathInfoTest.
 */
class PathInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testAbsoluteToRelativePath()
    {
        $path = '/foo/bar/file.ext';
        $relativeTo = '/foo/foo/';

        $this->assertSame('../bar/file.ext', PathInfo::absoluteToRelative($path, $relativeTo));

        $path = '/foo/bar/file.ext';
        $relativeTo = '/bar/foo/';

        $this->assertSame('../../foo/bar/file.ext', PathInfo::absoluteToRelative($path, $relativeTo));

        $path = '/foo/bar/file.ext';
        $relativeTo = 'bar/foo/';

        $this->expectException('SR\Exception\InvalidArgumentException');
        $this->assertSame('../../foo/bar/file.ext', PathInfo::absoluteToRelative($path, $relativeTo));
        $this->assertSame('../../foo/bar/file.ext', PathInfo::absoluteToRelative($path));
    }
}

/* EOF */
