<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests\Stubs;

use Unostentatious\Repository\AbstractEloquentRepository;

class RepositoryStub extends AbstractEloquentRepository
{

    /**
     * @inheritDoc
     */
    protected function getModelClass(): string
    {
        return ModelStub::class;
    }
}