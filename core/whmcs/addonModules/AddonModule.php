<?php
namespace MGModule\ResellersCenter\Core\Whmcs\AddonModules;
use MGModule\ResellersCenter\Repository\Whmcs\AddonModules;

/**
 * Description of AddonModule
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AddonModule
{
    public $name;
    
    protected $configuration;

    public function __construct($name = "ResellersCenter")
    {
        $this->name = $name;
    }
    
    public function __get($name)
    {
        if(empty($this->configuration))
        {
            $this->load();
        }
        
        return $this->configuration[$name];
    }

    public function __set($name, $value)
    {
        if(empty($this->configuration))
        {
            $this->load();
        }

        $this->configuration[$name] = $value;
    }

    public function save()
    {
        //Remove old configuration
        $repo = new AddonModules();
        $repo->where("name", "ResellersCenter")->delete();

        //Save new configuration
        foreach($this->configuration as $name => $value)
        {
            $repo->create([
                "name"      => $this->name,
                "setting"   => $name,
                "value"     => $value
            ]);
        }
    }

    protected function load()
    {
        $repo = new AddonModules();
        $configuration = $repo->getByName($this->name);
        
        foreach($configuration as $config)
        {
            $this->configuration[$config->setting] = $config->value;
        }
    }
}
