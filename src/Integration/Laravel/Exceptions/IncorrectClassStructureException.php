<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Integration\Laravel\Exceptions;

use Exception;
use Unostentatious\Repository\Interfaces\UnostentatiousRepositoryExceptionInterface;

final class IncorrectClassStructureException extends Exception implements UnostentatiousRepositoryExceptionInterface
{
    // Body not needed.
}