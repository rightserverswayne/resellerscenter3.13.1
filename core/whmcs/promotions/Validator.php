<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions;

use MGModule\ResellersCenter\Core\Helpers\Files;

/**
 * Description of Validator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Validator
{
    /**
     * @var Promotion
     */
    protected $promotion;

    /**
     * Validator constructor.
     *
     * @param Promotion $promotion
     */
    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
    }

    /**
     * Run rules validation
     *
     * @param $product
     * @return bool
     */
    public function check($product)
    {
        $rules      = $this->getRules();
        $isValid    = true;

        foreach($rules as $rule)
        {
            $isValid = $rule->run($product);
            if(!$isValid)
            {
                break;
            }
        }

        return $isValid;
    }

    /**
     * Get implemented promotion rules
     *
     * @return array
     */
    protected function getRules()
    {
        $result = [];

        $dir    = Files::getPath("core", "whmcs", "promotions", "validation", "rules", DS);
        $files  = glob("{$dir}*.{php}", GLOB_BRACE);
        foreach($files as $file)
        {
            $namespace = "\\MGModule\\ResellersCenter\\Core\\Whmcs\\Promotions\\Validation\\Rules\\";
            $classname = substr(basename($file), 0, -4);

            $class      = "{$namespace}{$classname}";
            $result[]   = new $class($this->promotion);
        }

        return $result;
    }
}