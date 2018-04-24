<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests\Fixture\Data;

/**
 * @internal
 */
final class DataLoader
{
    /**
     * @var array[]
     */
    private static $data = [];

    /**
     * @var string
     */
    private $dataRootPath;

    /**
     * @param string|null $dataRootPath
     */
    public function __construct(string $dataRootPath = null)
    {
        $this->dataRootPath = $dataRootPath ?: __DIR__;
    }

    /**
     * @param string      $testClassName
     * @param int|null    $limit
     * @param string|null $context
     * @param bool        $shuffle
     *
     * @throws \ReflectionException
     *
     * @return array|\Iterator
     */
    public function __invoke(string $testClassName, int $limit = null, string $context = null, bool $shuffle = true)
    {
        $r = new \ReflectionClass($testClassName);

        if (1 !== preg_match('{^.+?\\\Tests\\\(?<path>.+)\\\(?<file>[^\\\]+)$}', $r->getName(), $m)) {
            throw new \RuntimeException(sprintf('Failed to load data for "%s" test class!', $r->getName()));
        }

        $file = str_replace('\\', '/', sprintf(
            '%s/%s/%sData.php', $this->dataRootPath, $m['path'], $m['file']
        ));
        $name = sprintf('%s[limit:%s]', $file, null === $limit ? 'none' : $limit);

        if (!isset(self::$data[$name])) {
            if (!file_exists($file)) {
                throw new \RuntimeException(sprintf('Test fixture data file "%s" does not exist!', $file));
            }

            self::$data[$name] = (require $file)();
        }

        $d = self::$data[$name];
        $c = $context ?: 'default';

        if (null !== $c) {
            if (!isset($d[$c])) {
                throw new \RuntimeException(sprintf('Test fixture data file "%s" context "%s" not found!', $file, $c));
            }

            $d = $d[$c];
        }

        if ($shuffle) {
            shuffle($d);
        }

        if (null !== $limit && count($d) >= $limit) {
            $d = array_slice($d, 0, $limit);
        }

        foreach ($d as $i => $v) {
            yield $i => $v;
        }
    }
}
