<?php
/**
 * Created by PhpStorm.
 * User: mjwunderlich
 * Date: 9/27/16
 * Time: 12:14 PM
 */

namespace App\Exceptions;


use Exception;

class HttpException extends \Exception
{
  public function __construct($message, $code, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}
