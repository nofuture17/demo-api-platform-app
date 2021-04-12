<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * @codeCoverageIgnore
 */
final class FileDataInput
{
    public function __construct(
        /*
         * file name.
         */
        public string $name = '',
        /*
         * url or base64.
         */
        public string $content = ''
    ) {
    }
}
