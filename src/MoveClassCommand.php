<?php

namespace Pkboom\MoveClass;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class MoveClassCommand extends Command
{
    private $results = [];

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

        $this->replaceNamespaceAndUse();

        if (empty($this->results)) {
            $output->text('Nothing changed.');

            return static::SUCCESS;
        }

        $this->displayResults($output);

        $output->newLine();

        $output->text('Classes moved safely.');

        return static::SUCCESS;
    }

    public function replaceNamespaceAndUse()
    {
        foreach (Composer::getNamespaces($this->basePath('composer.json')) as $namespace => $dir) {
            $files = Finder::create()->in($dir)->files()->name('*.php')->ignoreDotFiles(true);

            foreach ($files as $file) {
                $content = Content::create($file->getRealPath());

                $this->replaceClassName($content, $file);

                if (empty($match = $content->namespace())) {
                    continue;
                }

                $namespaceFromFile = str_replace($namespace, '', $match);

                $namespaceFromPath = str_replace('/', '\\', str_replace($dir, '', $file->getPath()));

                if ($namespaceFromPath === $namespaceFromFile) {
                    continue;
                }

                $namespacePieces = array_map(function ($value) {
                    return ucfirst($value);
                }, explode('\\', trim($namespaceFromPath, '\\')));

                $newNamespace = $namespace.'\\'.implode('\\', $namespacePieces);

                $content->replaceNamespaceWith($newNamespace);

                $this->results['namespace'][] = "$match => $newNamespace";

                $newUse = $newNamespace.'\\'.str_replace('.php', '', $file->getBasename());

                $oldUse = $namespace.$namespaceFromFile.'\\'.str_replace('.php', '', $file->getBasename());

                $this->replaceUse($files, $oldUse, $newUse);

                foreach ($this->laravelDirs as $laravelDir) {
                    if (file_exists($this->basePath($laravelDir))) {
                        $laravelFiles = Finder::create()->in($this->basePath($laravelDir))->files()->name('*.php')->ignoreDotFiles(true);

                        $this->replaceUse($laravelFiles, $oldUse, $newUse);
                    }
                }

                $this->results['use'][] = "$oldUse => $newUse";
            }
        }
    }

    public function replaceUse($files, $oldUse, $newUse)
    {
        foreach ($files as $file) {
            $content = Content::create($file->getRealPath());

            $content->replaceUse($oldUse, $newUse);
        }
    }

    public function basePath($path = '')
    {
        return getcwd().($path !== '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function replaceClassName($content, $file)
    {
        $className = $content->className();

        $filename = str_replace('.php', '', $file->getBasename());

        if (isset($className) && $filename !== $className) {
            $content->replaceClassNameWith($filename);

            $this->results['class'][] = "$className => $filename";
        }
    }

    public function displayResults($output)
    {
        foreach ($this->results as $key => $result) {
            $output->title(ucfirst($key));

            foreach ($result ?? [] as $change) {
                $output->text($change);
            }
        }
    }
}
