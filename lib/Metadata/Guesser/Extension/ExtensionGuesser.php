<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\Extension;

use SR\File\FileInterface;
use SR\File\Metadata\ExtensionMetadata;
use SR\File\Metadata\Guesser\Extension\Resolver\ExtensionResolverInterface;
use SR\File\Metadata\Guesser\Extension\Resolver\FileNameExtensionResolver;
use SR\File\Metadata\Guesser\Extension\Resolver\MediaTypeExtensionResolver;

final class ExtensionGuesser
{
    /**
     * @var ExtensionGuesser
     */
    private static $instance = null;

    /**
     * @var ExtensionResolverInterface[]
     */
    protected $resolvers = [];

    /**
     * @param ExtensionResolverInterface ...$resolvers
     */
    public function __construct(ExtensionResolverInterface ...$resolvers)
    {
        $this->registerResolvers(...($resolvers ?: array_map(function (string $resolver): ExtensionResolverInterface {
            return new $resolver();
        }, self::getDefaultResolverNames())));
    }

    /**
     * @return string[]
     */
    public static function getDefaultResolverNames(): array
    {
        return [
            MediaTypeExtensionResolver::class,
            FileNameExtensionResolver::class,
        ];
    }

    /**
     * Returns the singleton instance.
     *
     * @return self
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param ExtensionGuesser|null $guesser
     */
    public static function setInstance(ExtensionGuesser $guesser = null): void
    {
        self::$instance = $guesser;
    }

    /**
     * @return ExtensionResolverInterface[]
     */
    public function getResolvers(): array
    {
        return $this->resolvers;
    }

    /**
     * @param ExtensionResolverInterface ...$resolvers
     *
     * @return self
     */
    public function registerResolvers(ExtensionResolverInterface ...$resolvers): self
    {
        array_unshift($this->resolvers, ...$resolvers);

        return $this;
    }

    /**
     * @return self
     */
    public function resetResolver(): self
    {
        $this->resolvers = [];

        return $this;
    }

    /**
     * @param FileInterface $file
     *
     * @return null|ExtensionMetadata
     */
    public function guess(FileInterface $file): ?ExtensionMetadata
    {
        foreach ($this->resolvers as $resolver) {
            if (null !== $extensionMetadata = $resolver->resolve($file)) {
                return $extensionMetadata;
            }
        }

        return null;
    }
}
