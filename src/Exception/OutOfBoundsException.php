<?php declare(strict_types=1);

namespace App\Exception;

/**
 * Если нужно будет выбросить исключение с сообщением не содержащим технической информации, которую можно показать
 * клиенту.
 *
 * Вырасывается если обработать входные данные не возможно из-за отсутствия реализации подходящего обработчика
 * или когда данные указаны неверные.
 */
class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{

}
