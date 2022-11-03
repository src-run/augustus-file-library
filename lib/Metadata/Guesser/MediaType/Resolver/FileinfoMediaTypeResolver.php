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

use SR\File\FilePath;
use SR\File\FilePathInterface;
use SR\Utilities\EngineQuery;

class FileinfoMediaTypeResolver implements MediaTypeResolverInterface
{
    use MediaTypeResolverTrait;

    /**
     * @var FilePath
     */
    private $magic;

    /**
     * @param string $magicFilePath {@see http://www.php.net/manual/en/function.finfo-open.php}
     */
    public function __construct(string $magicFilePath = null)
    {
        $this->magic = new FilePath($magicFilePath);
    }

    /**
     * @return bool
     */
    public static function isSupported()
    {
        return EngineQuery::extensionLoaded('fileinfo')
            && class_exists(\finfo::class);
    }

    protected function doResolveFile(FilePathInterface $file): ?string
    {
        $info = new \finfo(
            FILEINFO_MIME_TYPE, $this->magic->isFileReadable() ? $this->magic->getFile()->getPathname() : null
        );

        return $info ? ($info->file($file->getFile()->getPathname()) ?: null) : null;
    }
}
