<?php

namespace MGModule\ResellersCenter\core\mergeFields;

use MGModule\ResellersCenter\mgLibs\exceptions\factories\IncorrectClassFoundException;
use MGModule\ResellersCenter\mgLibs\exceptions\factories\NotExistException;

class FieldsFactory
{
    const FIELDS_DIR = 'fields';
    const CLASS_SUFFIX = 'IdField';

    public static function create($fieldName):AbstractField
    {
        $fieldName = ucfirst(str_replace('id', '', strtolower($fieldName)).self::CLASS_SUFFIX);
        $className = __NAMESPACE__.'\\'.self::FIELDS_DIR .'\\'.$fieldName;

        if ( !class_exists($className) ) {
            throw new NotExistException();
        }

        $fieldObject = new $className();

        if (!is_subclass_of($fieldObject,AbstractField::Class )) {
            throw new IncorrectClassFoundException();
        }

        return $fieldObject;
    }
}