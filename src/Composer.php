<?php

namespace Pkboom\MoveClass;

use Illuminate\Support\Str;
use RuntimeException;

class Composer
{
    public static function getNamespaces($composerJsonPath)
    {
        if (! file_exists($composerJsonPath)) {
            return [];
        }

        $basePath = Str::before($composerJsonPath, 'composer.json');

        $composer = json_decode(file_get_contents($composerJsonPath), true);

        $namespaces = array_filter(array_merge(
            $composer['autoload-dev']['psr-4'] ?? [],
            $composer['autoload']['psr-4'] ?? [],
        ));

        if (empty($namespaces)) {
            throw new RuntimeException('Unable to detect application namespace.');
        }

        $result = [];

        foreach ($namespaces as $namespace => $dir) {
            $result[trim($namespace, '\\')] = $basePath.trim($dir, '/');
        }

        return $result;
    }
}
