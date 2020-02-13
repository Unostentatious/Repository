<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests\Stubs;

use Unostentatious\Repository\AbstractUnostentatiousRepository;

class RepositoryStub extends AbstractUnostentatiousRepository
{

    /**
     * @inheritDoc
     */
    protected function getModelClass(): string
    {
        return ModelStub::class;
    }
}