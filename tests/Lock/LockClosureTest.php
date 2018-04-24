<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Lock;

use SR\File\Lock\LockClosure;
use SR\File\Lock\LockFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \SR\File\Lock\LockClosure
 */
class LockClosureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        LockFactory::reset();
    }

    public function testAction()
    {
        $result = LockClosure::blocking($this, function (): string {
            return 'foobar';
        });

        $this->assertSame('foobar', $result);
    }
}
