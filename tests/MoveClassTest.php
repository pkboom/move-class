<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class MoveClassTest extends TestCase
{
    /** @test */
    public function it_can_move_class()
    {
        $fooClass = $this->makeFooClass();

        file_put_contents(__DIR__.'/Temp/Baz/FooClass.php', $fooClass);

        $this->assertStringContainsString('namespace Tests\Temp\Foo',  $fooClass);

        $barClass = $this->makeBarClass();

        $this->assertStringContainsString('use Tests\Temp\Baz\FooClass',  $barClass);

        file_put_contents(__DIR__.'/Temp/Bar/BarClass.php', $barClass);

        shell_exec(__DIR__.'/../move-class');

        $fooClass = file_get_contents(__DIR__.'/Temp/Baz/FooClass.php');

        $this->assertStringContainsString('namespace Tests\Temp\Baz',  $fooClass);

        $barClass = file_get_contents(__DIR__.'/Temp/Bar/BarClass.php');

        $this->assertStringContainsString('use Tests\Temp\Baz\FooClass',  $barClass);

        @unlink(__DIR__.'/Temp/Bar/BarClass.php');
        @rmdir(__DIR__.'/Temp/Bar/');

        @unlink(__DIR__.'/Temp/Baz/FooClass.php');
        @rmdir(__DIR__.'/Temp/Baz/');
    }

    /** @test */
    public function it_can_change_a_classname_based_on_filename()
    {
        @mkdir(__DIR__.'/Temp/Foo');

        $content = $this->contentWithClassName('BarClass');

        $this->assertStringContainsString('class BarClass',  $content);

        file_put_contents(__DIR__.'/Temp/Foo/FooClass.php', $content);

        shell_exec(__DIR__.'/../move-class');

        $fooClass = file_get_contents(__DIR__.'/Temp/Foo/FooClass.php');

        $this->assertStringContainsString('class FooClass',  $fooClass);

        @unlink(__DIR__.'/Temp/Foo/FooClass.php');
        @rmdir(__DIR__.'/Temp/Foo/');
    }

    public function makeBarClass()
    {
        @mkdir(__DIR__.'/Temp/Bar/');

        $content = <<<CONTENT
<?php

namespace Tests\Temp\Bar;

use Tests\Temp\Baz\FooClass;

class BarClass
{
    public function someFunction()
    {
        FooClass::fooFunction();
    }
}
CONTENT;

        return $content;
    }

    public function makeFooClass()
    {
        @mkdir(__DIR__.'/Temp/Baz/');

        $content = <<<CONTENT
<?php

namespace Tests\Temp\Foo;

class FooClass
{
    public static function fooFunction()
    {
    }
}
CONTENT;

        return $content;
    }

    public function contentWithClassName($className)
    {
        $content = <<<CONTENT
<?php
class $className {}
CONTENT;

        return $content;
    }
}
