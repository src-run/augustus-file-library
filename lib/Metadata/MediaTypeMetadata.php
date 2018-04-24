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

class MediaTypeMetadata
{
    use MetadataTrait;

    /**
     * @var string
     */
    private const PREFIX_UNREGISTERED = 'x';

    /**
     * @var string
     */
    private const PREFIX_VENDOR = 'vnd';

    /**
     * @var string
     */
    private const PREFIX_PERSONAL = 'prs';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $subType;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @var string|null
     */
    private $deliminator;

    /**
     * @var string|null
     */
    private $suffix;

    /**
     * @param string|null $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     * @param string|null $deliminator
     */
    public function __construct(
        string $type = null,
        string $subType = null,
        string $prefix = null,
        string $suffix = null,
        string $deliminator = null
    ) {
        $this->type = self::sanitize($type);
        $this->subType = self::sanitize($subType);
        $this->prefix = self::sanitizePrefix($prefix);
        $this->suffix = self::sanitize($suffix);
        $this->deliminator = self::sanitizeDeliminator($deliminator);
    }

    /**
     * @return string
     */
    public function stringify(): string
    {
        return $this->hasType() && $this->hasSubType() ? vsprintf('%s/%s%s%s%s', [
            $this->getType(),
            $this->hasPrefix() ? $this->getPrefix() : '',
            $this->hasPrefix() && $this->hasDeliminator() ? $this->getDeliminator() : '',
            $this->getSubType(),
            $this->hasSuffix() ? sprintf('+%s', $this->getSuffix()) : '',
        ]) : '';
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function hasType(): bool
    {
        return null !== $this->getType();
    }

    /**
     * @param string|null $type
     *
     * @return bool
     */
    public function isTypeMatch(string $type = null): bool
    {
        return $this->getType() === $type;
    }

    /**
     * @return null|string
     */
    public function getSubType(): ?string
    {
        return $this->subType;
    }

    /**
     * @return bool
     */
    public function hasSubType(): bool
    {
        return null !== $this->getSubType();
    }

    /**
     * @param string|null $subType
     *
     * @return bool
     */
    public function isSubTypeMatch(string $subType = null): bool
    {
        return $this->getSubType() === $subType;
    }

    /**
     * @return null|string
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @return bool
     */
    public function hasPrefix(): bool
    {
        return null !== $this->getPrefix();
    }

    /**
     * @param string|null $prefix
     *
     * @return bool
     */
    public function isPrefixMatch(string $prefix = null): bool
    {
        return $this->getPrefix() === $prefix;
    }

    /**
     * @return bool
     */
    public function isPrefixUnregistered(): bool
    {
        return $this->isPrefixMatch(self::PREFIX_UNREGISTERED);
    }

    /**
     * @return bool
     */
    public function isPrefixVendor(): bool
    {
        return $this->isPrefixMatch(self::PREFIX_VENDOR);
    }

    /**
     * @return bool
     */
    public function isPrefixPersonal(): bool
    {
        return $this->isPrefixMatch(self::PREFIX_PERSONAL);
    }

    /**
     * @return bool
     */
    public function isPrefixStandard(): bool
    {
        return false === $this->isPrefixUnregistered()
            && false === $this->isPrefixVendor()
            && false === $this->isPrefixPersonal();
    }

    /**
     * @return null|string
     */
    public function getDeliminator(): ?string
    {
        return $this->deliminator;
    }

    /**
     * @return bool
     */
    public function hasDeliminator(): bool
    {
        return null !== $this->getDeliminator();
    }

    /**
     * @param string|null $deliminator
     *
     * @return bool
     */
    public function isDeliminatorMatch(string $deliminator = null): bool
    {
        return $this->getDeliminator() === $deliminator;
    }

    /**
     * @return null|string
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @return bool
     */
    public function hasSuffix(): bool
    {
        return null !== $this->getSuffix();
    }

    /**
     * @param string|null $suffix
     *
     * @return bool
     */
    public function isSuffixMatch(string $suffix = null): bool
    {
        return $this->getSuffix() === $suffix;
    }

    /**
     * @param string|null $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return bool
     */
    public function isMatch(string $type = null, string $subType = null, string $prefix = null, string $suffix = null): bool
    {
        return true === $this->isTypeMatch($type ?: $this->getType())
            && true === $this->isSubTypeMatch($subType ?: $this->getSubType())
            && true === $this->isPrefixMatch($prefix ?: $this->getPrefix())
            && true === $this->isSuffixMatch($suffix ?: $this->getSuffix());
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return true === $this->hasType()
            && true === $this->hasSubType()
            && true === self::isParsable($this->stringify());
    }

    /**
     * @param string $string
     *
     * @return null|array
     */
    public static function explodeParsable(string $string = null): ?array
    {
        $matched = 1 === preg_match(
            '{^(?<type>[^/]+)/((?<prefix>vnd|prs|x)(?<deliminator>\.|\-))?(?<sub_type>[^+]+?)(\+(?<suffix>.+))?$}',
            $string, $matches
        );

        $section = function (string $index) use ($matches): ?string {
            return empty($matches[$index]) ? null : $matches[$index];
        };

        return $matched ? [
            'type' => self::sanitize($matches['type']),
            'subType' => self::sanitize($matches['sub_type']),
            'prefix' => self::sanitizePrefix($section('prefix')),
            'suffix' => self::sanitize($section('suffix')),
            'deliminator' => self::sanitizeDeliminator($section('deliminator')),
        ] : null;
    }

    /**
     * @param string|null $prefix
     *
     * @return null|string
     */
    private static function sanitizePrefix(string $prefix = null): ?string
    {
        if (null === $prefix || in_array($prefix, [self::PREFIX_UNREGISTERED, self::PREFIX_VENDOR, self::PREFIX_PERSONAL], true)) {
            return self::sanitize($prefix);
        }

        throw new InvalidArgumentException(
            'Invalid mime type prefix "%s" provided (accepted values are "%s", "%s", and "%s").', $prefix,
            self::PREFIX_UNREGISTERED, self::PREFIX_VENDOR, self::PREFIX_PERSONAL
        );
    }

    /**
     * @param string|null $deliminator
     *
     * @return null|string
     */
    private static function sanitizeDeliminator(string $deliminator = null): ?string
    {
        if (null === $deliminator || in_array($deliminator, ['.', '-'], true)) {
            return self::sanitize($deliminator);
        }

        throw new InvalidArgumentException(
            'Invalid mime type deliminator "%s" provided (accepted values are "." and "-").', $deliminator
        );
    }
}
