<?php

namespace App\Helpers;

class ValidationErrrorHandler
{
    public static function handle($error_obj)
    {
        $erroredFields = [];

        foreach ($error_obj->keys() as $field) {
            array_push($erroredFields, $error_obj->first($field));
        }

        return $erroredFields;
    }
}