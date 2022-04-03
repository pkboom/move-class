<?php

namespace Pkboom\MoveClass;

class Content
{
    public $path;

    public $content;

    public function __construct($path)
    {
        $this->path = $path;

        $this->content = file_get_contents($path);
    }

    public static function create($path)
    {
        return new static($path);
    }

    public function namespace()
    {
        preg_match('/namespace\s+([\w\\\]+);/', $this->content, $match);

        return $match[1] ?? [];
    }

    public function replaceClassNameWith($filename)
    {
        $this->content = str_replace("class {$this->className()}", "class $filename", $this->content);

        file_put_contents($this->path, $this->content);
    }

    public function className()
    {
        preg_match('/class\s+([\w\\\]+)/', $this->content, $match);

        return $match[1] ?? [];
    }

    public function replaceNamespaceWith($namespace)
    {
        $this->content = str_replace("namespace {$this->namespace()}", "namespace $namespace", $this->content);

        file_put_contents($this->path, $this->content);
    }

    public function replaceUse($oldUse, $newUse)
    {
        dump($this->path);
        dump($oldUse, $newUse);

        $this->content = str_replace("use $oldUse", "use $newUse", $this->content);

        file_put_contents($this->path, $this->content);
    }
}
