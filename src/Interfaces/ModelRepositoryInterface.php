<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface ModelRepositoryInterface
{
    /**
     * Return a list of objects from the repository.
     *
     * @return object[]
     */
    public function all(): array;

    /**
     * Delete given object(s).
     *
     * @param Model|Model[] $object
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete($object): void;

    /**
     * Find object for given identifier, return null if not found.
     *
     * @param int|string $identifier
     *
     * @return null|Model
     */
    public function find($identifier): ?Model;

    /**
     * Save given object(s) and return true, if upsert values are present,
     * this will indicate that the upsert transaction should be implemented,
     * then return the number of affected rows.
     *
     * @param Model|Model[] $object The object or list of objects to save.
     * @param null|mixed[] $upsertValues the upsert values that will indicate upsert transaction needs to happen.
     * @param null|string[] $uniqueBy
     * @param null|string[] $fieldToUpdate
     *
     * @return bool|int
     */
    public function save(
        $object,
        ?array $upsertValues = null,
        ?array $uniqueBy = null,
        ?array $fieldToUpdate = null
    );
}
