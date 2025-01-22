<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\core\mergeFields\AbstractField;

class ResellerIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        return [];
    }
}