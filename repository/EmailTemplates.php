<?php

namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\models\EmailTemplate;
use MGModule\ResellersCenter\models\whmcs\EmailTemplate as WhmcsEmailTemplate;

/**
 * Description of Invoices
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class EmailTemplates extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\EmailTemplate';
    }

    /**
     * Get all language version of the template
     *
     * @param int         $resellerid
     * @param string      $name
     * @param string|null $language
     *
     * @return string
     */
    public function getByName($resellerid, $name, $language = null)
    {
        $model = $this->getModel();
        if($language === null) {
            $template = $model->where("reseller_id", $resellerid)->where("name", $name)->get();
        }
        else 
        {
            $template = $model->where("reseller_id", $resellerid)->where("name", $name)->where("language", $language)->first();
            if(empty($template)) {
                $template = $model->where("reseller_id", $resellerid)->where("name", $name)->where("language", "default")->first();
            }
        }
        
        return $template;
    }
    
    public function saveTemplates($resellerId, $name, $templates, $langForClone = null)
    {
        $clonedTemplate = null;
        $model = $this->getModel();
        $model->where("reseller_id", $resellerId)->where("name", $name)->delete();

        if ($langForClone && !empty($templates[$langForClone])) {
            $result = WhmcsEmailTemplate::where('name', $name)->where('language', $langForClone)->first();
            if ($result->exists) {
                $templates[$langForClone]['subject'] = $result->subject;
                $templates[$langForClone]['message'] = $result->message;
                $clonedTemplate = $templates[$langForClone];
            }
        }
        
        foreach ($templates as $language => $data) {
            $this->createNew($resellerId, $name, $data["subject"], $data["message"], $language);
        }
        return $clonedTemplate;
    }
    
    public function createNew($resellerid, $name, $subject, $message, $language)
    {
        $model = new EmailTemplate();
        $model->reseller_id = $resellerid;
        $model->name = $name;
        $model->subject = $subject;
        $model->message = $message;
        $model->language = $language;
        
        $model->save();
        return $model;
    }
}