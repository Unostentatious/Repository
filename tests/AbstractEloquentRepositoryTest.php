<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use Unostentatious\Repository\Tests\Stubs\ModelStub;
use Unostentatious\Repository\Tests\Stubs\RepositoryStub;

/**
 * @covers \Unostentatious\Repository\AbstractEloquentRepository
 */
final class AbstractEloquentRepositoryTest extends AbstractTestCase
{
    /**
     * Assert that the repository's all() method returns an array type.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testAllAndReturnArray(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(ModelStub::class, function (MockInterface $model): void {
            /** @var \Illuminate\Database\Eloquent\Collection $collection */
            $collection = $this->mock(Collection::class, function (MockInterface $collection): void {
                $collection->shouldReceive('getDictionary')->twice()->withNoArgs()->andReturn([]);
            });

            $model->shouldReceive('all')->twice()->withNoArgs()->andReturn($collection);
        });

        $repository = $this->createRepository($model);

        self::assertIsArray($repository->all());
        self::assertEquals([], $repository->all());
    }

    /**
     * Assert that the beginTransaction() method utilizes connection()->beginTransaction() method.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \Exception|\Throwable
     *
     * @noinspection PhpVoidFunctionResultUsedInspection
     */
    public function testBeginTransactionSuccess(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model): void {
            /** @var \Illuminate\Database\Connection $connection */
            $connection = $this->mock(Connection::class, function (MockInterface $connection): void {
                $connection->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
            });

            $model->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($connection);
        });

        $repository = $this->createRepository($model);

        self::assertNull($repository->beginTransaction());
    }

    /**
     * Assert that the commit() method utilizes connection()->commit() method.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \Exception|\Throwable
     *
     * @noinspection PhpVoidFunctionResultUsedInspection
     */
    public function testCommitSuccess(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model): void {
            /** @var \Illuminate\Database\Connection $connection */
            $connection = $this->mock(Connection::class, function (MockInterface $connection): void {
                $connection->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
            });

            $model->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($connection);
        });

        $repository = $this->createRepository($model);

        self::assertNull($repository->commit());
    }

    /**
     * Assert that the provided model has been deleted.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testDeleteModel(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(ModelStub::class, function (MockInterface $model): void {
            $model->shouldReceive('delete')->once()->withNoArgs()->andReturnNull();
        });

        $repository = $this->createRepository($model);

        $repository->delete($model);

        $this->addToAssertionCount(1);
    }

    /**
     * Assert that find() method returns the expected value.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testFindModelSuccess(): void
    {
        $returnedModel = new ModelStub();

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model) use ($returnedModel): void {
            $model->shouldReceive('find')->once()->with('id')->andReturn($returnedModel);
        });

        $repository = $this->createRepository($model);

        self::assertEquals(\spl_object_hash($repository->find('id')), \spl_object_hash($returnedModel));
    }

    /**
     * Assert that the rollback() method utilizes connection()->rollback() method.
     *
     * @return void
     *
     * @throws \ReflectionException
     * @throws \Exception|\Throwable
     *
     * @noinspection PhpVoidFunctionResultUsedInspection
     */
    public function testRollbackSuccess(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model): void {
            /** @var \Illuminate\Database\Connection $connection */
            $connection = $this->mock(Connection::class, function (MockInterface $connection): void {
                $connection->shouldReceive('rollback')->once()->withNoArgs()->andReturnNull();
            });

            $model->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($connection);
        });

        $repository = $this->createRepository($model);

        self::assertNull($repository->rollback());
    }

    /**
     * Assert that the save() method returns false upon failure.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testSaveModelFail(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model): void {
            $model->shouldReceive('save')->twice()->withNoArgs()->andReturnFalse();
        });

        $repository = $this->createRepository($model);

        self::assertIsBool($repository->save($model));
        self::assertEquals(false, $repository->save($model));
    }

    /**
     * Assert that the save() method returns true upon success.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testSaveModelSuccess(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model): void {
            $model->shouldReceive('save')->twice()->withNoArgs()->andReturnTrue();
        });

        $repository = $this->createRepository($model);

        self::assertIsBool($repository->save($model));
        self::assertEquals(true, $repository->save($model));
    }

    /**
     * Assert that the save() method executes upsert transaction,
     * and returns the number of affected rows upon success.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testSaveModelUpsertSuccess(): void
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $this->mock(Builder::class, function (MockInterface $builder): void {
            $builder
                ->shouldReceive('upsert')
                ->once()
                ->withArgs(
                    function ($upsertValues, $uniqueBy, $fieldToUpdate): bool {
                        return \is_array($upsertValues) && \is_array($uniqueBy) && \is_null($fieldToUpdate);
                    }
                )
                ->andReturn(0);
        });

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model) use ($builder): void {
            $model->shouldNotReceive('save');

            $model->shouldReceive('newModelQuery')
                ->once()
                ->andReturn($builder);
        });

        $repository = $this->createRepository($model);

        self::assertEquals(0, $repository->save($model, [], []));
    }

    /**
     * Assert that the transact() method executes beginTransaction() and commit() method upon success.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function testTransactSuccess(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model): void {
            /** @var \Illuminate\Database\Connection $connection */
            $connection = $this->mock(Connection::class, function (MockInterface $connection): void {
                $connection->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
                $connection->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
            });

            $model->shouldReceive('getConnection')->twice()->withNoArgs()->andReturn($connection);
        });

        $repository = $this->createRepository($model);

        $closure = function (): bool {
            return true;
        };

        self::assertTrue($repository->transact($closure));
    }

    /**
     * Assert that the transact() method executes beginTransaction() and commit() method upon success.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function testTransactThrowException(): void
    {
        $this->expectException(\Exception::class);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->mock(Model::class, function (MockInterface $model): void {
            /** @var \Illuminate\Database\Connection $connection */
            $connection = $this->mock(Connection::class, function (MockInterface $connection): void {
                $connection->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
                $connection->shouldNotReceive('commit');
            });

            $model->shouldReceive('getConnection')->twice()->withNoArgs()->andReturn($connection);
        });

        $repository = $this->createRepository($model);

        $closure = function (): bool {
            throw new \Exception('Exception test');
        };

        self::assertTrue($repository->transact($closure));
    }

    /**
     * Create an instance of the RepositoryStub.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param null|callable $expectation
     *
     * @return \Unostentatious\Repository\Tests\Stubs\RepositoryStub
     *
     * @throws \ReflectionException
     */
    private function createRepository(Model $model, ?callable $expectation = null): RepositoryStub
    {
        $repository = new \stdClass();

        if (isset($expectation) === false) {
            $repository = new RepositoryStub();
        }

        if (isset($expectation) === true) {
            $repository = $this->mock(RepositoryStub::class, $expectation);
        }

        $this->getMethodAsPublic(RepositoryStub::class, 'setModel')
            ->invoke($repository, $model);

        return $repository;
    }
}
