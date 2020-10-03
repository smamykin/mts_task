<?php declare(strict_types=1);

namespace App\ValueObject;

use App\Exception\OutOfBoundsException;

final class ActionType
{
    const IN = 'in';
    const OUT = 'out';

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (!in_array($type, [self::IN, self::OUT])) {
            throw new OutOfBoundsException('Доступны только два типа действий: in(заезд), out(выезд).');
        }
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->getType();
    }

    private function getType(): string
    {
        return $this->type;
    }
}
