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
     * @inheritDoc
     */
    public function all(): array
    {
        return \array_values($this->model->all()->getDictionary());
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception|\Throwable
     */
    public function beginTransaction(): void
    {
        $this->model->getConnection()->beginTransaction();
    }

    /**
     * @inheritDoc
     *
     * @throws \Throwable
     */
    public function commit(): void
    {
        $this->model->getConnection()->commit();
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public function find($identifier): ?Model
    {
        return $this->model->find($identifier);
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception|\Throwable
     */
    public function rollback(): void
    {
        $this->model->getConnection()->rollBack();
    }

    /**
     * @inheritDoc
     */
    public function save(
        $object,
        ?array $upsertValues = null,
        ?array $uniqueBy = null,
        ?array $fieldToUpdate = null
    ) {
        if (\is_array($object) === false) {
            $object = [$object];
        }

        /** @var \Illuminate\Database\Eloquent\Model $obj */
        foreach ($object as $obj) {
            if ($upsertValues !== null && $uniqueBy !== null) {
                /** @var \Illuminate\Database\Eloquent\Builder $builder */
                $builder = $obj->newModelQuery();

                return $builder->upsert($upsertValues, $uniqueBy, $fieldToUpdate);
            }

            return $obj->save();
        }

        return false;
    }

    /**
     * @inheritDoc
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
