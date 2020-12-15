<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Interfaces;

interface UnostentatiousRepositoryExceptionInterface
{
    /**
     * Return the error code.
     *
     * @return int
     */
    public function getErrorCode(): int;

    /**
     * Return the error sub-code.
     *
     * @return int
     */
    public function getErrorSubCode(): int;

    /**
     * Return the error response status code.
     *
     * @return int
     */
    public function getStatusCode(): int;
}