<?php
declare(strict_types=1);

namespace Unostentatious\Repository\Integration\Laravel;

use Illuminate\Support\ServiceProvider;
use Unostentatious\Repository\Integration\Laravel\Exceptions\DirectoryNotFoundException;
use Unostentatious\Repository\Integration\Laravel\Exceptions\IncorrectClassStructureException;

final class UnostentatiousRepositoryProvider extends ServiceProvider
{
    /**
     * Default directory name of repositories placeholder.
     *
     * @var string
     */
    private string $repositoryDir = 'Repositories';

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

    /**
     * Build the directories for the repository and interface destinations.
     *
     * @param string $root
     * @param null|string $destination
     * @param null|string $placeholder
     *
     * @return mixed[]
     */
    private function buildDirectoryPaths(string $root, ?string $destination = null, ?string $placeholder = null): string
    {
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
            opendir($repositoryDirectory);
            opendir($interfaceDirectory);
        } catch (\ErrorException $error) {
            throw new DirectoryNotFoundException($error->getMessage(), $error->getCode());
        }

        return $repositoryDirectory;
    }
}