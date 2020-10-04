<?php declare(strict_types=1);

namespace App\Exception;

/**
 * Если нужно будет выбросить исключение с сообщением не содержащим технической информации, которую можно показать
 * клиенту.
 *
 * Выбрасывать если аргумент функции некоректный.
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{

}
