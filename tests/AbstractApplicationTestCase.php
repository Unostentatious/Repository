<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests;

use Laravel\Lumen\Application;

/**
 * @covers nothing
 */
abstract class AbstractApplicationTestCase extends AbstractTestCase
{

    /**
     * Assert that the given abstract is an instance within the application container.
     *
     * @param string $concrete
     * @param string $abstract
     *
     * @return void
     */
    protected function assertInstanceInApp(string $concrete, string $abstract): void
    {
        self::assertInstanceOf($concrete, $this->getApplication()->get($abstract));
    }

    /**
     * Get lumen application.
     *
     * @return \Laravel\Lumen\Application
     */
    protected function getApplication(): Application
    {
        return $this->createApplication();
    }
}