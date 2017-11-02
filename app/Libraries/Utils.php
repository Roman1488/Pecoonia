<?php

namespace App\Libraries;
use App\Exceptions\HttpException;

/**
 * Created by PhpStorm.
 * User: mjwunderlich
 * Date: 9/28/16
 * Time: 17:35
 */
class Utils
{
    static $errors = [
        'not_found' => ['%s not found', 404],
        'not_logged_in' => ['User not logged in', 401],
        'user_exists'=> ['User with email %s already exists', 406],
        'does_not_exist' => ['%s does not exist', 404],
        'invalid_value' => ['%s is empty or invalid', 406],
        'invalid_values' => ['%s are empty or invalid', 406],
        'value_not_found' => ['%s can\'t be obtained from context, must be specified', 406],
        'custom' => ['%s', 400],
        'custom_system' => ['%s. Please contact admin', 500],[]
    ];

    /**
     * @param       $error_type
     * @param array $data
     *
     * @return string
     * @throws \Exception
     */
    public static function error($error_type, $data = [])
    {
        if (!isset(self::$errors[$error_type]))
            throw new \Exception("Error message type $error_type not found");

        return vsprintf(self::$errors[$error_type][0], is_array($data) ? $data : [$data]);
    }

    /**
     * @param       $error_type
     * @param array $data
     * @param       $exception_class
     *
     * @return \Exception
     */
    public static function throwError($error_type, $data = [], $exception_class = HttpException::class)
    {
        $reflectionClass = new \ReflectionClass($exception_class);
        $params = $reflectionClass->getConstructor()->getParameters();
        if ($params[1]->name == 'code') {
            // Exception type takes a code as second parameter
            return new $exception_class(self::error($error_type, $data), self::$errors[$error_type][1]);
        }
        elseif ($params[2]->name == 'code') {
            // Exception type takes a code as third parameter
            return new $exception_class(self::error($error_type, $data), null, self::$errors[$error_type][1]);
        }
        else {
            // Exception type doesn't take a code
            return new $exception_class(self::error($error_type, $data));
        }
    }

    /**
     * @param $items
     * @param $fields
     */
    public static function removeFields(&$items, $fields)
    {
        if (!is_array($fields))
            $fields = [$fields];

        if (is_array($items) || get_class($items) == 'Illuminate\Database\Eloquent\Collection') {
            foreach ($items as &$item) {
                foreach ($fields as $field) {
                    unset($item->$field);
                }
            }
        }
        elseif (is_object($items)) {
            foreach ($fields as $field) {
                unset($items->$field);
            }
        }
    }
}
