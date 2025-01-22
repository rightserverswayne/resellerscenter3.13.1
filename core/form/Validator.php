<?php
namespace MGModule\ResellersCenter\core\form;

/**
 * Description of Validator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Validator 
{
    private $rules  = array();
    private $value  = null;
    private $passed = false;
    
    public function __construct($rules)
    {
        $this->prepare($rules);
    }
    
    public function validate($value)
    {
        $this->value = $value;
        $this->run();
        
        return $this->getErrors();
    }
        
    private function prepare($rules)
    {
        foreach($rules as $rule)
        {
            $vars = explode(':', $rule);
            
            //There is no params for the rule
            if(count($vars) == 1)
            {
                $this->rules[] = array(
                    "rule" => $rule,
                    "vars" => null,
                );
            }
            else
            {
                $rule = $vars[0];
                unset($vars[0]);

                $this->rules[] = array (
                    "rule" => $rule,
                    "vars" => $vars,
                );
            }
        }
    }

    private function run()
    {
        foreach($this->rules as &$rule)
        {
            $func = $rule["rule"];
            if(method_exists($this, $func))
            {
                $rule["passed"] = $this->$func($rule["vars"]);
            }
            else
            {
                $rule["passed"] = call_user_func($func, $this->value, $rule["vars"]);
            }
        }
        
        $this->passed = true;
        return $this->passed;
    }
    
    private function getErrors()
    {
        $errors = array();
        foreach($this->rules as $rule)
        {
            if(! $rule["passed"])
            {
                $errors[] = $rule["rule"];
            }
        }
        
        return $errors;
    }

    private function required()
    {
        if(isset($this->value) && is_array($this->value))
        {
            return true;
        }

        if(strlen(trim($this->value)))
        {
            return true;
        }
        return false;
    }

    private function email()
    {
        if(filter_var($this->value, FILTER_VALIDATE_EMAIL))
        {
            return true;
        }
        return false;
    }

    private function int()
    {
        if(filter_var($this->value, FILTER_VALIDATE_INT))
        {
            return true;
        }
        return false;
    }

    private function domain()
    {
        $pieces = explode(".",$this->value);
        foreach($pieces as $piece)
        {
            if (!preg_match('/^[a-z\d][a-z\d-]{0,62}$/i', $piece) || preg_match('/-$/', $piece) )
            {
                return false;
            }
        }
        return true;
    }

    private function datetime()
    {
        if($this->value == date('Y-m-d H:i:s', strtotime($this->value)))
        {
            return true;
        }
        return false;
    }

    private function date()
    {
        if($this->value == date('Y-m-d', strtotime($this->value)))
        {
            return true;
        }
        
        return false;
    }
    
    private function numeric()
    {
        return is_numeric($this->value);
    }
    
    private function aboveEq($vars)
    {
        if((double)$this->value >= (double)$vars[0]){
            return true;
        }
        
        return false;
    }
    
    private function above($vars)
    {
        if((double)$this->value > (double)$vars[0]){
            return true;
        }
        
        return false;
    }
    
    private function belowEq($vars)
    {
        if((double)$this->value <= (double)$vars[0]){
            return true;
        }
        
        return false;
    }
    
    private function below($vars)
    {
        if((double)$this->value < (double)$vars[0]){
            return true;
        }
        
        return false;
    }
}
