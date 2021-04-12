<?php

declare(strict_types=1);

namespace App\Tests;

final class Helper
{
    public static function generateImageId(): int
    {
        return random_int(1, 1000);
    }
}
