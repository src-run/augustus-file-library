<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Spl\File;

use SR\Spl\File\Exception\NotReadableException;
use SR\Silencer\Call\CallDefinition;

/**
 * Class SplFileInfo.
 */
class SplFileInfo extends \SplFileInfo
{
    /**
     * @param \SplFileInfo $for
     *
     * @return \SplFileInfo
     */
    static function register(\SplFileInfo $for)
    {
        $for->setInfoClass(static::class);

        return $for;
    }

    /**
     * @param int $precision
     *
     * @return string
     */
    public function getSizeReadable(int $precision = 2): string
    {
        $format = ['B', 'K', 'M', 'G', 'T', 'P'];
        $factor = (int) floor((strlen($this->getSize()) - 1) / 3);

        return sprintf(
            "%.{$precision}f %s",
            $this->getSize() / pow(1024, $factor),
            $format[$factor] ?? $format[0]
        );
    }

    /**
     * @return \DateTime
     */
    public function getTimeModified(): \DateTime
    {
        return new \DateTime('@'.$this->getMTime());
    }

    /**
     * @return \DateTime
     */
    public function getTimeAccessed(): \DateTime
    {
        return new \DateTime('@'.$this->getATime());
    }

    /**
     * @return \DateTime
     */
    public function getTimeChanged(): \DateTime
    {
        return new \DateTime('@'.$this->getCTime());
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getContents(): string
    {
        $result = CallDefinition::create()->setInvokable(function ($path) {
            return file_get_contents($path);
        })->setValidator(function ($result) {
            return $result !== false && !is_null($result);
        })->invoke($this->getPathname());

        if (!$result->isValid()) {
            throw new NotReadableException('Failed to get file contents: %s', error_get_last()['message']);
        }

        return $result->getReturn();
    }
}

/* EOF */
