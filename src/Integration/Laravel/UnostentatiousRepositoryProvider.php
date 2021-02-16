<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Integration\Laravel;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException;

final class UnostentatiousRepositoryProvider extends ServiceProvider
{
    private LoggerInterface $logger;

    /**
     * Default directory name of repositories placeholder.
     *
     * @var string
     */
    private string $repositoryDir = 'Repositories';

    /**
     * UnostentatiousRepositoryProvider constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->logger = $app->make(LoggerInterface::class);

        $this->publishes([
            __DIR__ . '/config/unostent-repository.php' => \base_path('config/unostent-repository.php')
        ]);
    }

    /**
     * Register the repositories based on the configuration provided.
     *
     * @return void
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/unostent-repository.php', 'unostent-repository');

        $directories = $this->buildDirectoryPaths(
            \config('unostent-repository.root', null),
            \config('unostent-repository.destination', null),
            \config('unostent-repository.placeholder', null)
        );

        if ($directories !== null) {
            foreach ($this->getFiles($directories) as $file) {
                $tokens = $this->getFileTokens($file);

                [$interface, $repository] = $this->getRepoAndInterface($tokens);

                // If it is already in the container, skip it.
                if ($this->app->has($interface) === true) {
                    continue;
                }

                $this->app->bind($interface, $repository);
            }
        }
    }

    /**
     * Return the classes with it's corresponding interfaces.
     *
     * @param mixed[] $tokens
     *
     * @return mixed[]
     */
    protected function getRepoAndInterface(array $tokens): array
    {
        $class = $namespace = '';

        for ($it = 0; \count($tokens) > $it; $it++) {
            // Check if token index is a namespace.
            if ($tokens[$it][0] === T_NAMESPACE) {
                for ($j = $it + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespace .= '\\' . $tokens[$j][1];
                    } else {
                        if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }
            }

            // Check if token index is a class.
            if ($tokens[$it][0] === T_CLASS) {
                for ($j = $it + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j] === '{') {
                        $class = $tokens[$it + 2][1];
                    }
                }
            }
        }

        return [
            \sprintf('%s\%s\%s%s', ltrim($namespace, "\'"), 'Interfaces', $class, 'Interface'),
            \sprintf('%s\%s', ltrim($namespace, "\'"), $class)
        ];
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
        $interfaceDirectory = \sprintf('%s/%s', $repositoryDirectory, 'Interfaces');

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
     * Return the file as php tokens.
     *
     * @param string $file
     *
     * @return mixed[]
     *
     * @throws \Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException
     */
    private function getFileTokens(string $file): array
    {
        $tokens = [];
        $resource = \fopen($file, 'r');
        $buffer = '';

        while (\feof($resource) === false) {
            $buffer .= \fread($resource, 512);
            $tokens = \token_get_all($buffer);

            if (\strpos($buffer, '{') === false) {
                throw new IncorrectClassStructureException('Class is incorrectly structured.');
            }
        }

        return $tokens;
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
