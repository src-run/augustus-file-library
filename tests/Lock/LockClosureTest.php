<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Lock;

use PHPUnit\Framework\TestCase;
use SR\File\Lock\LockClosure;
use SR\File\Lock\LockFactory;

/**
 * @covers \SR\File\Lock\LockClosure
 */
class LockClosureTest extends TestCase
{
    protected function setUp(): void
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
