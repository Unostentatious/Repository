<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Integration\Laravel\Exceptions;

use Unostentatious\Repository\Externals\Exceptions\AbstractBaseException;
use Unostentatious\Repository\Interfaces\UnostentatiousRepositoryExceptionInterface;

final class IncorrectClassStructureException extends AbstractBaseException implements UnostentatiousRepositoryExceptionInterface
{
    /**
     * @inheritDoc
     */
    public function getErrorCode(): int
    {
        return self::DEFAULT_ERROR_CODE_CRITICAL;
    }

    /**
     * @inheritDoc
     */
    public function getErrorSubCode(): int
    {
        return self::DEFAULT_ERROR_SUB_CODE;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return self::DEFAULT_STATUS_CODE_CRITICAL;
    }
}