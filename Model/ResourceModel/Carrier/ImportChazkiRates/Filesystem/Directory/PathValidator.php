<?php
/**
 * Copyright © 2020 Chazki. All rights reserved.
 *
 * @category Class
 * @package  Chazki_ChazkiArg
 * @author   Chazki
 */

declare(strict_types=1);

namespace Chazki\ChazkiArg\Model\ResourceModel\Carrier\ImportChazkiRates\Filesystem\Directory;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\PathValidatorInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Phrase;

class PathValidator implements PathValidatorInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @inheritDoc
     */
    public function validate(
        string $directoryPath,
        string $path,
        ?string $scheme = null,
        bool $absolutePath = false
    ): void {
        $realDirectoryPath = $this->driver->getRealPathSafety($directoryPath);
        if ($realDirectoryPath[-1] !== DIRECTORY_SEPARATOR) {
            $realDirectoryPath .= DIRECTORY_SEPARATOR;
        }
        if (!$absolutePath) {
            $actualPath = $this->driver->getRealPathSafety(
                $this->driver->getAbsolutePath(
                    $realDirectoryPath,
                    $path,
                    $scheme
                )
            );
        } else {
            $actualPath = $this->driver->getRealPathSafety($path);
        }

        if (mb_strpos($actualPath, $realDirectoryPath) !== 0
            && $path .DIRECTORY_SEPARATOR !== $realDirectoryPath
        ) {
            throw new ValidatorException(
                new Phrase(
                    'Path "%1" cannot be used with directory "%2"',
                    [$path, $directoryPath]
                )
            );
        }
    }
}
