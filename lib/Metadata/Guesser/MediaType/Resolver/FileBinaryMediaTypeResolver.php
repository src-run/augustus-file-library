<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\MediaType\Resolver;

use SR\File\FilePathInterface;
use Symfony\Component\Process\Process;

class FileBinaryMediaTypeResolver implements MediaTypeResolverInterface
{
    use MediaTypeResolverTrait;

    /**
     * @var string
     */
    private $command;

    /**
     * @param string $command
     */
    public function __construct(string $command = 'file -b --mime %s 2> /dev/null')
    {
        $this->command = $command;
    }

    /**
     * @return bool
     */
    public static function isSupported()
    {
        return '\\' !== DIRECTORY_SEPARATOR && class_exists(Process::class);
    }

    /**
     * @param FilePathInterface $file
     *
     * @return null|string
     */
    protected function doResolveFile(FilePathInterface $file): ?string
    {
        $process = new Process(sprintf($this->command, escapeshellarg($file->getFile()->getPathname())));
        $process->setTimeout(5);
        $process->run();

        return 0 === $process->getExitCode()
            && 1 === preg_match('{^(?<media_type>[a-z0-9\.-]+/[a-z0-9\.-]+);}i', trim($process->getOutput()), $match)
                ? ($match['media_type'] ?? null) : null;
    }
}
