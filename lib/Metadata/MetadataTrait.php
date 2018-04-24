<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
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
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->stringify();
    }

    /**
     * @param string|null $string
     *
     * @return self
     */
    public static function create(string $string = null): self
    {
        if (null !== $sections = self::explodeParsable($string)) {
            return new self(...array_values($sections));
        }

        return new self();
    }

    /**
     * @return string
     */
    abstract public function stringify(): string;

    /**
     * @param string|null $string
     *
     * @return bool
     */
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
     *
     * @return null|array
     */
    abstract public static function explodeParsable(string $string = null): ?array;

    /**
     * @param string|null $string
     *
     * @return null|string
     */
    private static function sanitize(string $string = null): ?string
    {
        if (null === $string || 1 !== preg_match('{(?<characters>[^a-z0-9\.-]+)}i', $string, $matches)) {
            return $string;
        }

        throw new InvalidArgumentException(
            'Invalid character(s) "%s" provided in "%s" (accepted values are "[a-z0-9\.-]").',
            $matches['characters'], $string
        );
    }
}
