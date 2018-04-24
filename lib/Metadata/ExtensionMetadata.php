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

class ExtensionMetadata
{
    use MetadataTrait;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        $this->name = self::sanitize($name);
    }

    /**
     * @return string
     */
    public function stringify(): string
    {
        return $this->hasName() ? $this->getName() : '';
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasName(): bool
    {
        return null !== $this->getName();
    }

    /**
     * @param string|null $name
     *
     * @return bool
     */
    public function isNameMatch(string $name = null): bool
    {
        return $this->getName() === $name;
    }

    /**
     * @param string|null $name
     *
     * @return bool
     */
    public function isMatch(string $name = null): bool
    {
        return true === $this->isNameMatch($name);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return true === $this->hasName()
            && true === self::isParsable($this->stringify());
    }

    /**
     * @param string $string
     *
     * @return null|array
     */
    public static function explodeParsable(string $string = null): ?array
    {
        return [
            'name' => self::sanitize($string),
        ];
    }
}
