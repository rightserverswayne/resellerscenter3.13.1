<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Emails
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Emails extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Email';
    }
    
    public function addLog($clientid, $subject, $message, $to, $attachments = [])
    {
        $email = $this->getModel();
        $email->userid = $clientid;
        $email->subject = $subject;
        $email->message = html_entity_decode($message);
        $email->date = date("Y-m-d H:i:s");
        $email->to = $to;

        if (is_array($attachments)) {
            $attachments = '[' . implode(',', $attachments). ']' ;
        }

        $email->attachments = $attachments == null ? '[]' : $attachments;

        $email->save();
    }
}