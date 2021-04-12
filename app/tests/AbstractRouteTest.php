<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractRouteTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('router:match');
        $this->commandTester = new CommandTester($command);
    }

    protected static function assertInfo(string $info, string $controller, string $resource): void
    {
        self::assertStringContainsString("_controller: {$controller}()", $info);
        self::assertStringContainsString("_api_resource_class: {$resource}", $info);
    }

    protected function getRouteInfo(string $path, string $method): string
    {
        $this->commandTester->execute(['path_info' => $path, '--method' => $method]);

        return $this->commandTester->getDisplay();
    }
}
