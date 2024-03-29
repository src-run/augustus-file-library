<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File;

use SR\File\Exception\FileNotExistingException;
use SR\File\Exception\FileNotReadableException;

/**
 * @internal
 */
trait FilePathCastTrait
{
    protected static function castToFilePath(FileInterface $file, bool $checkExists = true, bool $checkReadable = true, bool $allowTemporary = true): ?FilePathInterface
    {
        if (true === $allowTemporary && $file instanceof FileBlob) {
            $temp = FileTemp::create('cast-to-file-path');
            $temp->acquire();
            $temp->setBlob($file->getBlob());

            $file = $temp;
        }

        if (!$file instanceof FilePathInterface) {
            return null;
        }

        if (true === $checkExists && false === $file->isFileExisting()) {
            throw new FileNotExistingException('Invalid file "%s" for resolver "%s": file is not existing.', $file->getIdentity(), __CLASS__);
        }

        if (true === $checkReadable && false === $file->isFileReadable()) {
            throw new FileNotReadableException('Invalid file "%s" for resolver "%s": file is not readable.', $file->getIdentity(), __CLASS__);
        }

        return $file;
    }
}
