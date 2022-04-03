<?php

namespace Pkboom\MoveClass;

class NamespaceClass
{
    public $namespace;

    public $match;

    public function __construct($namespace, $content)
    {
        $this->namespace = $namespace;

        preg_match('/namespace\s+([\w\\\]+);/', $content, $match);

        $this->match = $match;
    }

    public static function create($namespace, $content)
    {
        return new static($namespace, $content);
    }

    public function namespaceChanged($namespaceFromPath)
    {
        if (empty($this->match)) {
            return false;
        }

        if ($namespaceFromPath === $this->namespaceFromFile()) {
            return false;
        }

        return true;
    }

    public function namespaceFromFile()
    {
        return str_replace($this->namespace, '', $this->match[1]);
    }

    public function newNamespace($namespace, $namespaceFromPath)
    {
        $namespacePieces = array_filter(array_map(function ($value) {
            return ucfirst($value);
        }, explode('\\', trim($namespaceFromPath, '\\'))));

        if (empty($namespacePieces)) {
            return $namespace;
        }

        return $namespace.'\\'.implode('\\', $namespacePieces);
    }
}
