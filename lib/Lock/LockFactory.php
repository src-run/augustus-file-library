<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Lock;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\LockFactory as Factory;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\SemaphoreStore;

final class LockFactory
{
    /**
     * @var LoggerInterface
     */
    private static $logger;

    /**
     * @var PersistingStoreInterface|null
     */
    private static $store;

    /**
     * @var Factory|null
     */
    private static $factory;

    /**
     * Resets the lock state (including logger, store, and factory)
     */
    public static function reset(): void
    {
        self::$logger = null;
        self::$store = null;
        self::$factory = null;
    }

    public static function setLogger(LoggerInterface $logger = null): void
    {
        self::$factory = null;
        self::$logger = $logger;
    }

    public static function getLogger(): LoggerInterface
    {
        return self::$logger = self::$logger ?: new NullLogger();
    }

    public static function setStore(PersistingStoreInterface $store = null): void
    {
        self::$factory = null;
        self::$store = $store;
    }

    public static function getStore(): PersistingStoreInterface
    {
        return self::$store = self::$store ?: new SemaphoreStore();
    }

    public static function getFactory(): Factory
    {
        if (null === self::$factory) {
            self::$factory = new Factory(self::getStore());
            self::$factory->setLogger(self::getLogger());
        }

        return self::$factory;
    }

    /**
     * @param mixed $context
     */
    public static function create($context): ?Lock
    {
        return self::getFactory()->createLock(self::stringifyContext($context));
    }

    /**
     * @param mixed $context
     */
    public static function acquire($context): ?Lock
    {
        $lock = self::create($context);

        return $lock->acquire(false) ? $lock : null;
    }

    /**
     * @param mixed $context
     */
    public static function blocking($context): Lock
    {
        ($lock = self::create($context))->acquire(true);

        return $lock;
    }

    /**
     * @param mixed $context
     */
    private static function stringifyContext($context): string
    {
        return is_scalar($context) ? $context : self::stringifyComplexContext($context);
    }

    /**
     * @param object $context
     */
    private static function stringifyComplexContext($context): string
    {
        if (is_array($context)) {
            return implode(':', array_map(function ($value): string {
                return self::stringifyContext($value);
            }, $context));
        }

        if (method_exists($context, '__toString') && !empty($string = $context->__toString())) {
            return $string;
        }

        return sprintf('[%s]="%s"', get_class($context), $string ?? spl_object_hash($context));
    }
}
