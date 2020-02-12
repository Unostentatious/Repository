<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests;

use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractTestCase extends TestCase
{
    /**
     * Make protected and private method accessible.
     *
     * @param string $className
     * @param string $methodName
     *
     * @return \ReflectionMethod
     *
     * @throws \ReflectionException
     */
    protected function getMethodAsPublic(string $className, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Make a mock of the provided class, add the optional expectations.
     *
     * @param string|object $class
     * @param null|callable $expectations
     *
     * @return \Mockery\LegacyMockInterface
     */
    protected function mock($class, ?callable $expectations = null): LegacyMockInterface
    {
        $mock = Mockery::mock($class);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}