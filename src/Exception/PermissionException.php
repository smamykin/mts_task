<?php declare(strict_types=1);

namespace App\Exception;

/**
 * Если нужно будет выбросить исключение с сообщением не содержащим технической информации, которую можно показать
 * клиенту.
 *
 * Выбрасывается в случае попытки совершить какое-то неразрешенное действие.
 */
class PermissionException extends \RuntimeException implements ExceptionInterface
{

}
