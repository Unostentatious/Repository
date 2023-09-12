<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Integration\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

final class UnostentatiousRepositoryProvider extends ServiceProvider
{
    private const INTERFACES = 'Interfaces';

    /**
     * @var \Psr\Log\LoggerInterface|mixed
     */
    private LoggerInterface $logger;

    /**
     * Default directory name of repositories placeholder.
     *
     * @var string
     */
    private string $repositoryDir = 'Repositories';

    /**
     * Application app path.
     *
     * @var string
     */
    protected string $appPath = '';

    /**
     * UnostentatiousRepositoryProvider constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->logger = $app->make(LoggerInterface::class);
        $this->appPath = base_path() . '/app';
    }

    /**
     * Create the configuration file on boot.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/unostent-repository.php' => \base_path('config/unostent-repository.php')
        ]);
    }

    /**
     * Register the repositories based on the configuration provided.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/unostent-repository.php', 'unostent-repository');

        $root = \config('unostent-repository.root', null);
        $destination = \config('unostent-repository.destination', null);
        $placeholder = \config('unostent-repository.placeholder', null);
        $directories = $this->buildDirectoryPaths($root, $destination, $placeholder);
        $rootNamespace = $this->resolveDefaultNamespace($root, $destination, $placeholder);

        if ($directories !== null) {
            foreach ($this->getFiles($directories) as $file) {
                $array = \explode('.php', basename($file));
                $string = \implode('', $array);

                $repository = \sprintf('%s\%s\%s', $rootNamespace, $this->repositoryDir, $string);
                $interface = \sprintf('%s\%s\Interfaces\%sInterface', $rootNamespace, $this->repositoryDir, $string);

                if ($this->app->has($interface) === true) {
                    continue;
                }

                $this->app->bind($interface, $repository);
            }
        }
    }

    /**
     * Return the root namespace provided from config.
     *
     * @param null|string $root
     * @param null|string $destination
     * @param null|string $placeholder
     *
     * @return string
     */
    private function resolveDefaultNamespace(
        ?string $root = null,
        ?string $destination = null,
        ?string $placeholder = null
    ): string {
        $rootNamespace = null;

        if ($root === $this->appPath) {
            $rootNamespace = 'App';
        }

        if ($root !== $this->appPath) {
            $rootNamespace = \explode(base_path() . '/', $root);
            $rootNamespace = implode('', $rootNamespace);
        }

        return \sprintf('%s%s%s',
            $rootNamespace,
            $destination ? \sprintf('\%s', Str::studly($destination)) : null,
            $placeholder ? \sprintf('\%s', Str::studly($placeholder)) : null
        );
    }

    /**
     * Build the directories for the repository and interface destinations.
     *
     * @param string $root
     * @param null|string $destination
     * @param null|string $placeholder
     *
     * @return null|string
     */
    private function buildDirectoryPaths(
        string $root,
        ?string $destination = null,
        ?string $placeholder = null
    ): ?string {
        $this->repositoryDir = $placeholder ?? $this->repositoryDir;

        $repositoryDirectory = \sprintf('%s/%s', $root, $this->repositoryDir);

        if ($destination !== null) {
            $repositoryDirectory = \sprintf(
                '%s/%s/%s',
                $root,
                $destination,
                $this->repositoryDir
            );
        }
        $interfaceDirectory = \sprintf('%s/%s', $repositoryDirectory, self::INTERFACES);
        try {
            // Check first if the specified directories are existing,
            // if they are not create them.
            if ($this->createDirectory($repositoryDirectory) === true) {
                \opendir($repositoryDirectory);
            }

            if ($this->createDirectory($interfaceDirectory) === true) {
                \opendir($interfaceDirectory);
            }
        } catch (\ErrorException $error) {
            // If this prompt's an exception, it means the directory is not yet created,
            // this suggests that the directory and config hasn't been established yet,
            // so in that sense, instead of throwing an exception,
            // just return null to notify the provider that there is no action needed to be done.
            $this->logger->error($error->getMessage());

            return null;
        }

        return $repositoryDirectory;
    }

    /**
     * Check if the specified path is an existing dir or not.
     *
     * @param string $path
     *
     * @return bool
     */
    private function createDirectory(string $path): bool
    {
        if (\is_dir($path) === true) {
            return true;
        }

        return \mkdir($path, 0755);
    }

    /**
     * Return the list of files within the given directory.
     *
     * @param string $directory
     *
     * @return string[]
     */
    private function getFiles(string $directory): array
    {
        $scanned = \array_diff(scandir($directory), ['..', '.']);
        $result = [];

        foreach ($scanned as $file) {
            $php = \explode('.', $file);
            if (isset($php[1]) === true && $php[1] === 'php') {
                \array_push($result, \sprintf('%s/%s', $directory, $file));
            }
        }

        return $result;
    }
}
