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

use SR\File\Lock\LockFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Lock\StoreInterface;

/**
 * @covers \SR\File\Lock\LockFactory
 */
class LockFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        LockFactory::reset();
    }

    /**
     * @return \Iterator
     */
    public static function provideContextStringifyData(): \Iterator
    {
        yield ['a-string'];
        yield [100];
        yield [self::createStringCastableObject()];
        yield [['a', 'simple', 'array']];
        yield [['a', 'complex', 'array', ['with', 'inner', 'array', [self::createStringCastableObject()]]]];
    }

    /**
     * @dataProvider provideContextStringifyData
     *
     * @param $context
     */
    public function testCreate($context)
    {
        $mainLock = LockFactory::create($context);

        $this->assertFalse($mainLock->isAcquired());
        $this->assertTrue($mainLock->acquire());
        $this->assertTrue($mainLock->isAcquired());

        for ($i = 0; $i < 10; ++$i) {
            $loopLock = LockFactory::create($context);

            $this->assertInstanceOf(Lock::class, $loopLock);
            $this->assertFalse($loopLock->isAcquired());
            $this->assertFalse($loopLock->acquire());

            $loopLock->release();
        }

        $mainLock->release();
    }

    /**
     * @dataProvider provideContextStringifyData
     *
     * @param $context
     */
    public function testAcquire($context)
    {
        for ($i = 0; $i < 10; ++$i) {
            $lock1 = LockFactory::acquire($context);

            $this->assertInstanceOf(Lock::class, $lock1);
            $this->assertTrue($lock1->isAcquired());

            $lock1->release();
            $lock2 = LockFactory::acquire($context);

            $this->assertInstanceOf(Lock::class, $lock2);
            $this->assertTrue($lock2->isAcquired());

            $lock3 = LockFactory::acquire($context);

            $this->assertNull($lock3);
            $this->assertTrue($lock2->isAcquired());

            $lock2->release();
        }
    }

    /**
     * @dataProvider provideContextStringifyData
     *
     * @param $context
     */
    public function testBlocking($context)
    {
        for ($i = 0; $i < 10; ++$i) {
            $lock1 = LockFactory::blocking($context);

            $this->assertInstanceOf(Lock::class, $lock1);
            $this->assertTrue($lock1->isAcquired());

            $lock2 = LockFactory::acquire($context);

            $this->assertNull($lock2);
            $this->assertTrue($lock1->isAcquired());

            $lock1->release();
        }
    }

    public function testStaticState()
    {
        $l = new NullLogger();
        $s = new SemaphoreStore();

        $this->assertNotSame($l, LockFactory::getLogger());
        $this->assertInstanceOf(LoggerInterface::class, LockFactory::getLogger());
        $this->assertNotSame($s, LockFactory::getStore());
        $this->assertInstanceOf(StoreInterface::class, LockFactory::getStore());
        $this->assertInstanceOf(Factory::class, $f = LockFactory::getFactory());

        LockFactory::setLogger($l);

        $this->assertSame($l, LockFactory::getLogger());
        $this->assertNotSame($f, $f = LockFactory::getFactory());
        $this->assertInstanceOf(LoggerInterface::class, LockFactory::getLogger());
        $this->assertInstanceOf(StoreInterface::class, LockFactory::getStore());
        $this->assertInstanceOf(Factory::class, LockFactory::getFactory());

        LockFactory::setStore($s);

        $this->assertSame($s, LockFactory::getStore());
        $this->assertNotSame($f, $f = LockFactory::getFactory());
        $this->assertInstanceOf(LoggerInterface::class, LockFactory::getLogger());
        $this->assertInstanceOf(StoreInterface::class, LockFactory::getStore());
        $this->assertInstanceOf(Factory::class, LockFactory::getFactory());
    }

    /**
     * @dataProvider provideContextStringifyData
     *
     * @param $context
     */
    public function testContextStringify($context): void
    {
        $lock1 = LockFactory::acquire($context);
        $lock2 = LockFactory::acquire($context);

        $this->assertTrue($lock1->isAcquired());
        $this->assertNull($lock2);

        $lock1->release();
    }

    public function testComplexObjectContexts()
    {
        $obj = new class() {
        };

        $lock1 = LockFactory::acquire($obj);
        $lock2 = LockFactory::acquire($obj);

        $this->assertTrue($lock1->isAcquired());
        $this->assertNull($lock2);

        $lock1->release();

        $obj = self::createStringCastableObject(true);

        $lock1 = LockFactory::acquire($obj);
        $lock2 = LockFactory::acquire($obj);

        $this->assertTrue($lock1->isAcquired());
        $this->assertTrue($lock2->isAcquired());

        $lock1->release();
        $lock2->release();
    }

    /**
     * @return object
     */
    private static function createStringCastableObject(bool $iterateStringify = false)
    {
        return new class($iterateStringify) {
            /**
             * @var int
             */
            public static $i = 0;

            /**
             * @var bool
             */
            private $iterateStringify;

            /**
             * @param bool $iterateStringify
             */
            public function __construct(bool $iterateStringify)
            {
                $this->iterateStringify = $iterateStringify;
            }

            /**
             * @return string
             */
            public function __toString(): string
            {
                return vsprintf('class-string-%s [%s]', [
                    $this->iterateStringify ? self::$i++ : 'no-iteration',
                    spl_object_hash($this),
                ]);
            }
        };
    }
}
