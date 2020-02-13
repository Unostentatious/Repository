<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests\Integration\Laravel;

use Illuminate\Container\EntryNotFoundException;
use Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException;
use Unostentatious\Repository\Integration\Laravel\UnostentatiousRepositoryProvider;
use Unostentatious\Repository\Tests\AbstractApplicationTestCase;
use Unostentatious\Repository\Tests\Integration\Laravel\Stubs\Classes\Interfaces\RepositoryStubInterface;
use Unostentatious\Repository\Tests\Integration\Laravel\Stubs\Classes\RepositoryStub;

final class UnostentatiousRepositoryProviderTest extends AbstractApplicationTestCase
{
    /**
     * Assert that the EntryNotFoundException is thrown since is no class registered in the container.
     *
     * @return void
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     */
    public function testEmptyDirectory(): void
    {
        $this->expectException(EntryNotFoundException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        /** @var \Illuminate\Support\Facades\Config $config */
        $config = \config();
        $config->set('unostent-repository.root', base_path());
        $config->set('unostent-repository.destination', 'Integration/Laravel/Stubs');
        $config->set('unostent-repository.placeholder', 'EmptyDirectory');

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $provider = new UnostentatiousRepositoryProvider($app);
        $provider->boot();
        $provider->register();

        // This triggers the exception
        $this->assertInstanceInApp(RepositoryStub::class, RepositoryStubInterface::class);
    }

    /**
     * Assert that the classes residing in the configured directories.
     *
     * @return void
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     */
    public function testRegisterAlreadyRegisteredRepositoriesSuccess(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        /** @var \Illuminate\Support\Facades\Config $config */
        $config = \config();
        $config->set('unostent-repository.root', base_path());
        $config->set('unostent-repository.destination', 'Integration/Laravel/Stubs');
        $config->set('unostent-repository.placeholder', 'Classes');

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $provider = new UnostentatiousRepositoryProvider($app);
        $provider->boot();
        $provider->register();
        $provider->register();

        $this->assertInstanceInApp(RepositoryStub::class, RepositoryStubInterface::class);
    }

    /**
     * Assert that the classes residing in the configured directories
     * has been registered in the container.
     *
     * @return void
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     */
    public function testRegisterRepositoriesSuccess(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        /** @var \Illuminate\Support\Facades\Config $config */
        $config = \config();
        $config->set('unostent-repository.root', base_path());
        $config->set('unostent-repository.destination', 'Integration/Laravel/Stubs');
        $config->set('unostent-repository.placeholder', 'Classes');

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $provider = new UnostentatiousRepositoryProvider($app);
        $provider->boot();
        $provider->register();

        $this->assertInstanceInApp(RepositoryStub::class, RepositoryStubInterface::class);
    }

    /**
     * Assert that the classes residing in the configured directories.
     *
     * @return void
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     */
    public function testSkipRegisteringNonClass(): void
    {
        $this->expectException(IncorrectClassStructureException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        /** @var \Illuminate\Support\Facades\Config $config */
        $config = \config();
        $config->set('unostent-repository.root', base_path());
        $config->set('unostent-repository.destination', 'Integration/Laravel/Stubs');
        $config->set('unostent-repository.placeholder', 'Functions');

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $provider = new UnostentatiousRepositoryProvider($app);
        $provider->boot();
        $provider->register();
    }
}