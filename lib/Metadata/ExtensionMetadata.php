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

class ExtensionMetadata
{
    use MetadataTrait;

    /**
     * @var string|null
     */
    private $name;

    public function __construct(string $name = null)
    {
        $this->name = self::sanitize($name);
    }

    public function stringify(): string
    {
        return $this->hasName() ? $this->getName() : '';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function hasName(): bool
    {
        return null !== $this->getName();
    }

    public function isNameMatch(string $name = null): bool
    {
        return $this->getName() === $name;
    }

    public function isMatch(string $name = null): bool
    {
        return true === $this->isNameMatch($name);
    }

    public function isValid(): bool
    {
        return true === $this->hasName()
            && true === self::isParsable($this->stringify());
    }

    /**
     * @param string $string
     */
    public static function explodeParsable(string $string = null): ?array
    {
        return [
            'name' => self::sanitize($string),
        ];
    }
}
