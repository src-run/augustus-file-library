<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata;

use SR\File\Exception\Metadata\InvalidArgumentException;

/**
 * @internal
 */
trait MetadataTrait
{
    public function __toString(): string
    {
        return $this->stringify();
    }

    public static function create(string $string = null): self
    {
        if (null !== $sections = self::explodeParsable($string)) {
            return new self(...array_values($sections));
        }

        return new self();
    }

    abstract public function stringify(): string;

    public static function isParsable(string $string = null): bool
    {
        try {
            return true !== empty($string)
                && null !== self::explodeParsable($string);
        } catch (InvalidArgumentException $exception) {
            return false;
        }
    }

    /**
     * @param string $string
     */
    abstract public static function explodeParsable(string $string = null): ?array;

    private static function sanitize(string $string = null): ?string
    {
        if (null === $string || 1 !== preg_match('{(?<characters>[^a-z0-9\.-]+)}i', $string ?? '', $matches)) {
            return $string;
        }

        throw new InvalidArgumentException('Invalid character(s) "%s" provided in "%s" (accepted values are "[a-z0-9\.-]").', $matches['characters'], $string);
    }
}
