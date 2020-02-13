<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use Unostentatious\Repository\Tests\Stubs\ModelStub;
use Unostentatious\Repository\Tests\Stubs\RepositoryStub;

final class AbstractUnostentatiousRepositoryTest extends AbstractTestCase
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
                $collection->shouldReceive('getDictionary')->once()->withNoArgs()->andReturn([]);
            });

            $model->shouldReceive('all')->once()->withNoArgs()->andReturn($collection);
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
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
            $model->shouldReceive('save')->once()->withNoArgs()->andReturnFalse();
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
            $model->shouldReceive('save')->once()->withNoArgs()->andReturnTrue();
        });

        $repository = $this->createRepository($model);

        self::assertIsBool($repository->save($model));
        self::assertEquals(true, $repository->save($model));
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
            $model->shouldReceive('save')->once()->withNoArgs()->andReturnTrue();
        });

        $transaction = function () use ($model): bool {
            return $model->save();
        };

        $repositoryMock = function (MockInterface $repository) use ($transaction): void {
            $repository->shouldReceive('transact')->once()->withArgs($transaction);
            $repository->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturnNull();
            $repository->shouldReceive('commit')->once()->withNoArgs()->andReturnNull();
        };

        $repository = $this->createRepository($model, $repositoryMock);

        self::assertNull($repository->transact($transaction));
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
        $repository = new RepositoryStub();

        if (isset($expectation) === true) {
            $repository = $this->mock(RepositoryStub::class, $expectation);
        }

        $this->getMethodAsPublic(RepositoryStub::class, 'setModel')->invoke($repository, $model);

        return $repository;
    }
}