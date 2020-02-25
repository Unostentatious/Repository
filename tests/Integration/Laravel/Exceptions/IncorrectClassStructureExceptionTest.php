<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests\Integration\Laravel\Exceptions;

use Unostentatious\Repository\Externals\Exceptions\AbstractBaseException;
use Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException;
use Unostentatious\Repository\Tests\AbstractTestCase;

/**
 * @covers \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
 */
final class IncorrectClassStructureExceptionTest extends AbstractTestCase
{
    /**
     * Test exception error codes.
     *
     * @return void
     */
    public function testErrorCodes(): void
    {
        $exception = new IncorrectClassStructureException();

        self::assertEquals(AbstractBaseException::DEFAULT_ERROR_CODE_CRITICAL, $exception->getErrorCode());
        self::assertEquals(AbstractBaseException::DEFAULT_ERROR_SUB_CODE, $exception->getErrorSubCode());
        self::assertEquals(AbstractBaseException::DEFAULT_STATUS_CODE_CRITICAL, $exception->getStatusCode());
    }
}