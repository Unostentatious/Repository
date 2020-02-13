<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Interfaces;

use Closure;

interface ConnectionRepositoryInterface extends ModelRepositoryInterface
{
    /**
     * Start a new database transaction.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function beginTransaction(): void;

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback the active database transaction.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function rollback(): void;

    /**
     * Transaction execution, init transaction for procedures that requires commit or rollback during exception.
     *
     * @param \Closure $func
     *
     * @return bool|mixed
     *
     * @throws \Throwable
     */
    public function transact(Closure $func);
}