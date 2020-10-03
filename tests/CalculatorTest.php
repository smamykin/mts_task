<?php

namespace App\Tests;

use Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        include_once __DIR__ . '/Calculator.php';
    }

    public function testAdd(): void
    {
        $this->assertSame(8, (new Calculator())->add(3, 5));
    }
}
