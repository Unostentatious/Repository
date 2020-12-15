<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Externals\Exceptions;

use Exception;
use Throwable;
use Unostentatious\Repository\Externals\Interfaces\BaseExceptionInterface;

abstract class AbstractBaseException extends Exception implements BaseExceptionInterface
{
    /**
     * @var mixed[]
     */
    protected array $messageParams;

    /**
     * BaseException constructor.
     *
     * @param null|string $message
     * @param null|mixed[] $messageParameters
     * @param null|int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        ?string $message = null,
        ?array $messageParameters = null,
        ?int $code = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message ?? '', $code ?? 0, $previous);

        $this->messageParams = $messageParameters ?? [];
    }

    /**
     * Return the resolved message parameters.
     *
     * @return mixed[]
     */
    final public function getMessageParameters(): array
    {
        return $this->messageParams;
    }
}