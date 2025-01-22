<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\Domains;

use MGModule\ResellersCenter\Core\Helpers\Files;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

/**
 * Description of Registrar
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Registrar extends WhmcsObject
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var mixed
     */
    protected $config;

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\Models\Whmcs\Registrar::class;
    }

    /**
     * Registrar constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get value from database or (if not found) from config array
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if(empty($this->data))
        {
            $this->data = new \stdClass();
            $this->load();
        }

        $result = $this->data->{$name};
        if(empty($result))
        {
            $result = $this->config[$name];
        }

        return $result;
    }

    /**
     * Load registrar configuration from database and registrar .php files
     */
    protected function load()
    {
        //Load settings from configuration
        $classname = $this->getModelClass();
        $model     = new $classname();

        $this->data->name = $this->name;
        $records = $model->where("registrar", $this->name)->get();
        foreach($records as $record)
        {
            $this->data->{$record->setting} = decrypt($record->value);
        }

        //Load config from registrar file
        if(!function_exists("{$this->name}_getConfigArray()"))
        {
            require_once Files::getWhmcsPath("includes", "registrarfunctions.php");
            require_once Files::getWhmcsPath("modules", "registrars", "{$this->name}", "{$this->name}.php");
        }

        $records = call_user_func("{$this->name}_getConfigArray");
        foreach($records as $setting => $values)
        {
            $this->config[$setting] = $values["Value"];
        }
    }
}