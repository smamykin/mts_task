<?php

namespace App\Tests\unit\ValueObject;

use App\ValueObject\ActionType;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class ActionTypeTest extends TestCase
{
    /**
     * @param $type
     * @dataProvider providerSuccess
     */
    public function testCreate($type) {
        $obj = new ActionType($type);
        $this->assertSame($type, (string)$obj);
    }

    /**
     * @param $type
     * @dataProvider providerFail
     */
    public function testCreateWithWrongType($type) {
        $this->expectException(OutOfBoundsException::class);
        new ActionType($type);
    }

    public function providerSuccess()
    {
        return [
            [ActionType::OUT,],
            [ActionType::IN,],
        ];
    }

    public function providerFail()
    {
        return [
            ['not_existed_type',],
            [uniqid(),],
            ['',],
        ];
    }
}
