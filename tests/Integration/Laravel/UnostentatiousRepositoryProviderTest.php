<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Tests\Integration\Laravel;

use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException;
use Unostentatious\Repository\Integration\Laravel\UnostentatiousRepositoryProvider;
use Unostentatious\Repository\Tests\AbstractApplicationTestCase;
use Unostentatious\Repository\Tests\Integration\Laravel\Stubs\Classes\Interfaces\RepositoryStubInterface;
use Unostentatious\Repository\Tests\Integration\Laravel\Stubs\Classes\RepositoryStub;

/**
 * @covers \Unostentatious\Repository\Integration\Laravel\UnostentatiousRepositoryProvider
 */
final class UnostentatiousRepositoryProviderTest extends AbstractApplicationTestCase
{

    /**
     * Assert that the EntryNotFoundException is thrown since is no class registered in the container.
     *
     * @return void
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
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

        $provider = new UnostentatiousRepositoryProvider($app);
        $provider->boot();
        $provider->register();

        // This triggers the exception
        $this->assertInstanceInApp(RepositoryStub::class, RepositoryStubInterface::class);
    }

    /**
     * Assert the result when the directory placeholder is not existing.
     *
     * @return void
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testNonExistingDirectory(): void
    {
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->mock(
            LoggerInterface::class,
            function (MockInterface $logger): void {
                $logger->shouldReceive('error')
                    ->once()
                    ->andReturnUsing(
                        function ($errorMessage): bool {
                            return \is_string($errorMessage);
                        }
                    );
            }
        );

        /** @var \Illuminate\Contracts\Container\Container $config */
        $config = $this->mock(
            Container::class,
            function (MockInterface $container): void {
                $container
                    ->shouldReceive('set')
                    ->once()
                    ->withAnyArgs();

                $container
                    ->shouldReceive('get')
                    ->once()
                    ->withAnyArgs()
                    ->andReturn([]);
            }
        );

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->mock(
            Application::class,
            function (MockInterface $application) use ($config, $logger): void {
                $application
                    ->shouldReceive('make')
                    ->with('config')
                    ->once()
                    ->andReturn($config);

                $application
                    ->shouldReceive('make')
                    ->once()
                    ->withArgs(
                        function ($loggerInterface): bool {
                            return $loggerInterface === LoggerInterface::class;
                        }
                    )
                    ->andReturnUsing(
                        function ($loggerInterface) use ($logger): LoggerInterface {
                            return $logger;
                        }
                    );
            }
        );

        /** @var \Illuminate\Support\Facades\Config $config */
        $config = \config();
        $config->set('unostent-repository.root', base_path());
        $config->set('unostent-repository.destination', 'Integration/Laravel/NonExisting');
        $config->set('unostent-repository.placeholder', 'Classes');

        $provider = new UnostentatiousRepositoryProvider($app);
        $provider->boot();
        $provider->register();
    }
}
