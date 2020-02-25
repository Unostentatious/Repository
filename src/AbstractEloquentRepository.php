<?php
declare(strict_types=1);

namespace Unostentatious\Repository;

use Illuminate\Database\Eloquent\Model;
use Unostentatious\Repository\Interfaces\ConnectionRepositoryInterface;

abstract class AbstractEloquentRepository implements ConnectionRepositoryInterface
{
    /** @var \Illuminate\Database\Eloquent\Model $model */
    protected Model $model;

    /**
     * AbstractEloquentRepository constructor.
     *
     * @throws \ReflectionException
     *
     * @noinspection PhpParamsInspection
     */
    public function __construct()
    {
        $this->setModel((new \ReflectionClass($this->getModelClass()))->newInstance());
    }

    /**
     * Return a list of objects from the repository.
     *
     * @return object[]
     */
    public function all(): array
    {
        return \array_values($this->model->all()->getDictionary());
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function beginTransaction(): void
    {
        $this->model->getConnection()->beginTransaction();
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->model->getConnection()->commit();
    }

    /**
     * Delete given object(s).
     *
     * @param Model|Model[] $object
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete($object): void
    {
        if (\is_array($object) === false) {
            $object = [$object];
        }

        foreach ($object as $obj) {
            $obj->delete();
        }
    }

    /**
     * Find object for given identifier, return null if not found.
     *
     * @param int|string $identifier
     *
     * @return null|Model
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public function find($identifier): ?Model
    {
        return $this->model->find($identifier);
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function rollback(): void
    {
        $this->model->getConnection()->rollBack();
    }

    /**
     * Save given object(s).
     *
     * @param Model|Model[] $object The object or list of objects to save
     *
     * @return bool
     */
    public function save($object): bool
    {
        if (\is_array($object) === false) {
            $object = [$object];
        }

        foreach ($object as $obj) {
            if ($obj->save() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Init transaction for procedures that requires commit or rollback during exception.
     *
     * @param \Closure $func
     *
     * @return bool|mixed
     *
     * @throws \Throwable
     */
    public function transact(\Closure $func)
    {


        $this->beginTransaction();

        try {
            $return = \call_user_func($func);

            $this->commit();


            return $return ?? true;
        } catch (\Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
    }

    /**
     * Return the selected model class.
     *
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * Set the selected model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    private function setModel(Model $model): void
    {
        $this->model = $model;
    }
}