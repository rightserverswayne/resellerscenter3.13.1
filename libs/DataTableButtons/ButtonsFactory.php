<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons;

class ButtonsFactory
{
    const BUTTONS_SUFFIX = "Buttons";
    const BUTTONS_DIR = "MGModule\ResellersCenter\libs\DataTableButtons\Buttons\\";

    public static function createByName($name): ButtonInterface
    {
        $className = self::BUTTONS_DIR . $name . self::BUTTONS_SUFFIX;
        if (class_exists($className)) {
            $object = new $className();
            if ($object instanceof ButtonInterface) {
                return $object;
            } else {
                throw new \Exception("Table Buttons Create Failed. ". $name . " is wrong class name.");
            }
        } else {
            throw new \Exception("Table Buttons Create Failed. ". $name . " not found.");
        }
    }
}