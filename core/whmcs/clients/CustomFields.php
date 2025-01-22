<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Clients;
use MGModule\ResellersCenter\repository\whmcs\CustomFields as CustomFieldsRepo;

/**
 * Description of CustomFields
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CustomFields
{
    protected $client;
    
    protected $fields;
    
    public static function getAvailable()
    {
        $repo = new CustomFieldsRepo();
        return $repo->getClientFields(true);
    }

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    public function __get($id)
    {
        $this->fields = $this->fields ?: $this->load();
        return $this->fields[$id];
    }
    
    public function __set($id, $value)
    {
        $field = $this->{$id};
        $model = $field->getValueModel($this->client->id);
        
        $model->value = $value;
        $model->save();
    }
    
    public function find($name)
    {
        $this->fields = $this->fields ?: $this->load();
        $result = null;
        foreach($this->fields as $field)
        {
            $systemname = substr($field->fieldname, 0, strpos($field->fieldname, "|"));
            if($systemname == $name)
            {
                $result = $field;
                break;
            }
        }
        
        return $result;
    }
    
    protected function load()
    {
        $fields = self::getAvailable();
        
        $result = [];
        foreach($fields as $field)
        {
            $field->value = $field->getValueByRelid($this->client->id);
            $result[$field->id] = $field;
        }
        
        return $result;
    }
}