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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function hasType(): bool
    {
        return null !== $this->getType();
    }

    public function isTypeMatch(string $type = null): bool
    {
        return $this->getType() === $type;
    }

    public function getSubType(): ?string
    {
        return $this->subType;
    }

    public function hasSubType(): bool
    {
        return null !== $this->getSubType();
    }

    public function isSubTypeMatch(string $subType = null): bool
    {
        return $this->getSubType() === $subType;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function hasPrefix(): bool
    {
        return null !== $this->getPrefix();
    }

    public function isPrefixMatch(string $prefix = null): bool
    {
        return $this->getPrefix() === $prefix;
    }

    public function isPrefixUnregistered(): bool
    {
        return $this->isPrefixMatch(self::PREFIX_UNREGISTERED);
    }

    public function isPrefixVendor(): bool
    {
        return $this->isPrefixMatch(self::PREFIX_VENDOR);
    }

    public function isPrefixPersonal(): bool
    {
        return $this->isPrefixMatch(self::PREFIX_PERSONAL);
    }

    public function isPrefixStandard(): bool
    {
        return false === $this->isPrefixUnregistered()
            && false === $this->isPrefixVendor()
            && false === $this->isPrefixPersonal();
    }

    public function getDeliminator(): ?string
    {
        return $this->deliminator;
    }

    public function hasDeliminator(): bool
    {
        return null !== $this->getDeliminator();
    }

    public function isDeliminatorMatch(string $deliminator = null): bool
    {
        return $this->getDeliminator() === $deliminator;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function hasSuffix(): bool
    {
        return null !== $this->getSuffix();
    }

    public function isSuffixMatch(string $suffix = null): bool
    {
        return $this->getSuffix() === $suffix;
    }

    public function isMatch(string $type = null, string $subType = null, string $prefix = null, string $suffix = null): bool
    {
        return true === $this->isTypeMatch($type ?: $this->getType())
            && true === $this->isSubTypeMatch($subType ?: $this->getSubType())
            && true === $this->isPrefixMatch($prefix ?: $this->getPrefix())
            && true === $this->isSuffixMatch($suffix ?: $this->getSuffix());
    }

    public function isValid(): bool
    {
        return true === $this->hasType()
            && true === $this->hasSubType()
            && true === self::isParsable($this->stringify());
    }

    /**
     * @param string $string
     */
    public static function explodeParsable(string $string = null): ?array
    {
        $matched = 1 === preg_match(
            '{^(?<type>[^/]+)/((?<prefix>vnd|prs|x)(?<deliminator>\.|\-))?(?<sub_type>[^+]+?)(\+(?<suffix>.+))?$}',
            $string ?? '', $matches
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

    private static function sanitizePrefix(string $prefix = null): ?string
    {
        if (null === $prefix || in_array($prefix, [self::PREFIX_UNREGISTERED, self::PREFIX_VENDOR, self::PREFIX_PERSONAL], true)) {
            return self::sanitize($prefix);
        }

        throw new InvalidArgumentException('Invalid mime type prefix "%s" provided (accepted values are "%s", "%s", and "%s").', $prefix, self::PREFIX_UNREGISTERED, self::PREFIX_VENDOR, self::PREFIX_PERSONAL);
    }

    private static function sanitizeDeliminator(string $deliminator = null): ?string
    {
        if (null === $deliminator || in_array($deliminator, ['.', '-'], true)) {
            return self::sanitize($deliminator);
        }

        throw new InvalidArgumentException('Invalid mime type deliminator "%s" provided (accepted values are "." and "-").', $deliminator);
    }
}
