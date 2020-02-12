<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests;

use Laravel\Lumen\Application;

abstract class AbstractApplicationTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private Application $app;

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
        if (isset($this->app) === true) {
            return $this->app;
        }

        return $this->app = new Application(__DIR__);
    }
}