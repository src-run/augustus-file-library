<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Path;

use SR\Exception\InvalidArgumentException;

/**
 * Class Path.
 */
class PathInfo
{
    /**
     * @param string      $originalPath
     * @param string|null $comparedPath
     *
     * @return string
     */
    public static function absoluteToRelative($originalPath, $comparedPath = null)
    {
        if ($comparedPath === null) {
            $comparedPath = getcwd();
        }

        if (substr($originalPath, 0, 1) !== '/' || substr($comparedPath, 0, 1) !== '/') {
            throw InvalidArgumentException::create()
                ->setMessage('File path (%s) and new relative path (%s) must be absolute pathnames.')
                ->with($originalPath, $comparedPath);
        }

        $originalParts = static::toArray($originalPath);
        $comparedParts = static::toArray($comparedPath);
        $commPartCount = static::partsDiffCount($originalPath, $comparedPath);
        $diffParts = [];

        if (count($comparedParts) > $commPartCount) {
            $diffParts = array_fill(0, count($comparedParts) - $commPartCount, '..');
        }

        if (count($originalParts) > $commPartCount) {
            $diffParts = array_merge($diffParts, array_slice($originalParts, $commPartCount));
        }

        return implode(DIRECTORY_SEPARATOR, $diffParts);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public static function toArray($path)
    {
        return explode(DIRECTORY_SEPARATOR, rtrim($path, DIRECTORY_SEPARATOR));
    }

    /**
     * @param string $original
     * @param string $compared
     *
     * @return int
     */
    private static function partsDiffCount($original, $compared)
    {
        $originalParts = static::toArray($original);
        $comparedParts = static::toArray($compared);
        $count = 0;

        for ($i = 0; $i < max(count($originalParts), count($comparedParts)); ++$i) {
            if (!isset($originalParts[$i]) || !isset($comparedParts[$i])) {
                break;
            }

            if ($originalParts[$i] !== $comparedParts[$i]) {
                break;
            }

            ++$count;
        }

        return $count;
    }
}

/* EOF */
