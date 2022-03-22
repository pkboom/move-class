<?php

namespace Pkboom\MoveClass;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class MoveClassCommand extends Command
{
    protected $laravelDirs = [
        'resources/views',
        'routes',
    ];

    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Move classes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new SymfonyStyle($input, $output);

        $results = $this->replaceNamespaceAndUse();

        if (empty($results)) {
            $output->text('Nothing changed.');

            return static::SUCCESS;
        }

        $output->title('Namespace');

        foreach ($results['namespace'] ?? [] as $oldNamespace => $newNamespace) {
            $output->text($oldNamespace.' => '.$newNamespace);
        }

        $output->title('Use');

        foreach ($results['use'] ?? [] as $oldUse => $newUse) {
            $output->text($oldUse.' => '.$newUse);
        }

        $output->newLine();

        $output->text('Classes moved safely.');

        return static::SUCCESS;
    }

    public function getNamespaces()
    {
        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);

        $autoloads = [$composer['autoload-dev']['psr-4'] ?? [], $composer['autoload']['psr-4'] ?? []];

        $namespaces = array_filter(array_merge($autoloads[0], $autoloads[1]));

        if (empty($namespaces)) {
            throw new RuntimeException('Unable to detect application namespace.');
        }

        $result = [];

        foreach ($namespaces as $namespace => $dir) {
            $result[trim($namespace, '\\')] = $this->basePath(trim($dir, '/'));
        }

        return $result;
    }

    public function basePath($path = '')
    {
        return getcwd().($path !== '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function replaceUse($files, $oldUse, $newUse)
    {
        foreach ($files as $value) {
            $content = file_get_contents($value->getRealPath());

            $content = str_replace('use '.$oldUse, 'use '.$newUse, $content);

            file_put_contents($value->getRealPath(), $content);
        }
    }

    public function replaceNamespaceAndUse()
    {
        $results = [];

        foreach ($this->getNamespaces() as $namespace => $dir) {
            $files = Finder::create()->in($dir)->files()->name('*.php')->ignoreDotFiles(true);

            foreach ($files as $file) {
                $content = file_get_contents($file->getRealPath());

                preg_match('/namespace\s+([\w\\\]+);/', $content, $match);

                if (empty($match)) {
                    continue;
                }

                $namespaceFromFile = str_replace($namespace, '', $match[1]);

                $namespaceFromPath = str_replace('/', '\\', str_replace($dir, '', $file->getPath()));

                if ($namespaceFromPath === $namespaceFromFile) {
                    continue;
                }

                $namespacePieces = array_map(function ($value) {
                    return ucfirst($value);
                }, explode('\\', trim($namespaceFromPath, '\\')));

                $newNamespace = $namespace.'\\'.implode('\\', $namespacePieces);

                $content = str_replace('namespace '.$match[1], 'namespace '.$newNamespace, $content);

                file_put_contents($file->getRealPath(), $content);

                $results['namespace'][$match[1]] = $newNamespace;

                $newUse = $newNamespace.'\\'.str_replace('.php', '', $file->getBasename());

                $oldUse = $namespace.$namespaceFromFile.'\\'.str_replace('.php', '', $file->getBasename());

                $this->replaceUse($files, $oldUse, $newUse);

                foreach ($this->laravelDirs as $laravelDir) {
                    if (file_exists($this->basePath($laravelDir))) {
                        $laravelFiles = Finder::create()->in($this->basePath($laravelDir))->files()->name('*.php')->ignoreDotFiles(true);

                        $this->replaceUse($laravelFiles, $oldUse, $newUse);
                    }
                }

                $results['use'][$oldUse] = $newUse;
            }
        }

        return $results;
    }
}
