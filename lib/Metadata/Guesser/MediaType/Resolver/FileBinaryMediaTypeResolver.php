<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Metadata\Guesser\MediaType\Resolver;

use SR\File\FilePathInterface;
use Symfony\Component\Process\Process;

class FileBinaryMediaTypeResolver implements MediaTypeResolverInterface
{
    use MediaTypeResolverTrait;

    /**
     * @var string[]
     */
    private array $command;

    public function __construct(array $command = ['file', '-b', '--mime'])
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

    protected function doResolveFile(FilePathInterface $file): ?string
    {
        $process = new Process([...$this->command, $file->getFile()->getPathname()]);
        $process->setTimeout(5);
        $process->run();

        return 0 === $process->getExitCode()
            && 1 === preg_match('{^(?<media_type>[a-z0-9\.-]+/[a-z0-9\.-]+);}i', trim($process->getOutput()), $match)
                ? ($match['media_type'] ?? null) : null;
    }
}
