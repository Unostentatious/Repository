<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Integration\Laravel\Exceptions;

use Symfony\Component\Finder\Exception\DirectoryNotFoundException as SymfonyDirectoryNotFoundException;
use Unostentatious\Repository\Interfaces\UnostentatiousRepositoryExceptionInterface;

final class DirectoryNotFoundException extends SymfonyDirectoryNotFoundException implements UnostentatiousRepositoryExceptionInterface
{
    // Body not needed.
}