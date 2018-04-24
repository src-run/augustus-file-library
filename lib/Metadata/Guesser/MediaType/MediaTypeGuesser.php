<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\MediaType;

use SR\File\FileInterface;
use SR\File\Metadata\Guesser\MediaType\Resolver\FileBinaryMediaTypeResolver;
use SR\File\Metadata\Guesser\MediaType\Resolver\FileinfoMediaTypeResolver;
use SR\File\Metadata\Guesser\MediaType\Resolver\MediaTypeResolverInterface;
use SR\File\Metadata\MediaTypeMetadata;

final class MediaTypeGuesser
{
    /**
     * @var MediaTypeGuesser
     */
    private static $instance = null;

    /**
     * @var MediaTypeResolverInterface[]
     */
    protected $resolvers = [];

    /**
     * @param MediaTypeResolverInterface ...$resolvers
     */
    public function __construct(MediaTypeResolverInterface ...$resolvers)
    {
        $this->registerResolvers(...($resolvers ?: array_map(function (string $resolver): MediaTypeResolverInterface {
            return new $resolver();
        }, self::getDefaultResolverNames())));
    }

    /**
     * @return string[]
     */
    public static function getDefaultResolverNames(): array
    {
        return [
            FileinfoMediaTypeResolver::class,
            FileBinaryMediaTypeResolver::class,
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
     * @param MediaTypeGuesser|null $guesser
     */
    public static function setInstance(MediaTypeGuesser $guesser = null): void
    {
        self::$instance = $guesser;
    }

    /**
     * @return MediaTypeResolverInterface[]
     */
    public function getResolvers(): array
    {
        return $this->resolvers;
    }

    /**
     * @param MediaTypeResolverInterface ...$resolvers
     *
     * @return self
     */
    public function registerResolvers(MediaTypeResolverInterface ...$resolvers): self
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
     * @return null|MediaTypeMetadata
     */
    public function guess(FileInterface $file): ?MediaTypeMetadata
    {
        foreach ($this->resolvers as $resolver) {
            if (null !== $mediaTypeMetadata = $resolver->resolve($file)) {
                return $mediaTypeMetadata;
            }
        }

        return null;
    }
}
