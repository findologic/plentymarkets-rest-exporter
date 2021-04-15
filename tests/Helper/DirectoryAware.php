<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Tests\Helper;

use InvalidArgumentException;

trait DirectoryAware
{
    /**
     * Deletes all given directories recursively.
     *
     * @param string[] $paths
     * @param bool $throwIfNonExistent If set to true, an exception is thrown if a directory doesnt exist, otherwise
     * it will be silently ignored.
     */
    protected function deleteDirectories(array $paths, bool $throwIfNonExistent = false): void
    {
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                if ($throwIfNonExistent) {
                    throw new InvalidArgumentException(sprintf(
                        'Given directory "%s" can not be deleted, as it does not exist',
                        $path
                    ));
                }

                continue;
            }

            // PHP is really bad at deleting files recursively. Therefore we go the system approach.
            exec(sprintf('rm -rf "%s"', $path));
        }
    }

    /**
     * Creates all given directory paths.
     *
     * @param string[] $paths
     * @param bool $throwIfNonExistent If set to true, an exception is thrown if a directory doesnt exist, otherwise
     * it will be silently ignored.
     */
    protected function createDirectories(array $paths, bool $throwIfNonExistent = false): void
    {
        foreach ($paths as $path) {
            if (is_dir($path)) {
                if ($throwIfNonExistent) {
                    throw new InvalidArgumentException(sprintf(
                        'Given directory "%s" can not be created, as it already exists',
                        $path
                    ));
                }

                continue;
            }

            mkdir($path);
        }
    }
}
