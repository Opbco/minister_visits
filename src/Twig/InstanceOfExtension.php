<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceOfExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceOf']),
        ];
    }

    public function isInstanceOf($var, $instance)
    {
        $reflectionClass = new \ReflectionClass($instance);
        return $reflectionClass->isInstance($var);
    }
}
