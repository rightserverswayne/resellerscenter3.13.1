<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\Repository\Source\RepositoryException;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class EmailTemplates extends AbstractRepository
{
    const TYPES = ["general", "product", "domain", "support", "invoice", "invite", "user"];
    
    const MSG_INVOICE = "invoice";
    const MSG_INVITE = "invite";
    const MSG_SUPPORT = "support";
    const MSG_GENERAL = "general";
    const MSG_PRODUCT = "product";
    const MSG_DOMAIN = "domain";
    const MSG_ADMIN = "admin";
    const MSG_USER = "user";
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\EmailTemplate';
    }
    
    public function getAllDefaults()
    {
        $model = $this->getModel();
        $templates = $model->where("language", "")->get();
        
        return $templates;
    }
    
    public function getByName($name, $language = null)
    {
        $model = $this->getModel();
        
        if($language === null) {
            $template = $model->where("name", $name)->get();
        }
        else
        {
            $template = $model->where("name", $name)->where("language", $language)->first();
            if(empty($template))
            {
                $template = $model->where("name", $name)->where("language", "")->first();
            }
        }
        
        return $template;
    }

    public function getTemplatesByType($type)
    {
        if(!in_array($type, self::TYPES)){
            throw new RepositoryException("invalid_type");
        }
        
        $query = DB::table("tblemailtemplates");
        $query->where("type", $type);
        
        $result = $query->get();
        return $result;
    }
    
    public function getTemplatesSortedByType($language = "")
    {
        $templates = $this->where("language", $language)->orderBy("name")->get();
        
        $result = array();
        foreach($templates as $template)
        {
            //Skip templates that cannot be branded
            if($template->name == "Clients Only Bounce Message")
            {
                continue;
            }
            
            $result[$template->type][] = $template;
        }
        
        return $result;        
    }
}
