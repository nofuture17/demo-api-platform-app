<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class ImagesFixture extends Fixture
{
    public const SUCCESS_COUNT = 15;
    public const FAILED_COUNT = 18;

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::SUCCESS_COUNT; ++$i) {
            $manager->persist((new Image())->setName('success'.$i));
        }
        for ($i = 0; $i < self::FAILED_COUNT; ++$i) {
            $manager->persist((new Image())->setError('error')->setName('failed'.$i));
        }

        $manager->flush();
    }
}
