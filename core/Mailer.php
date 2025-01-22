<?php

namespace MGModule\ResellersCenter\core;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\whmcs\Emails;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use \PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $mailer;

    public static function sendMail(Reseller $reseller, $template, $receiver, $sender = [], $cc = [], $params = [], $attachments = [])
    {
        $mail = new Mailer($reseller);

        if (!empty($sender)) {
            $mail->setSender($sender["email"], $sender["name"]);
        } else {
            $mail->setSender($template->fromemail, $template->fromname);
        }
        if (!empty($cc) || $template->copyto) {
            $mail->addCC($cc);
            $mail->addCC($template->copyto);
        }

        if (!empty($attachments)) {
            $mail->addAttachments($attachments);
        }

        //Apply header and footer
        $message = $mail->addGlobalWrappers($template->message, $reseller);
        $mail->addReciever($receiver["email"], $receiver["name"]);
        $mail->setSubjectAndBody($template->subject, $message, $params, $template->plaintext);

        $result = $mail->send();

        //Add email logs
        if ($result) {
            $emails = new Emails();
            $emails->addLog($receiver["userid"], $mail->mailer->Subject, $mail->mailer->Body, $receiver["email"], $attachments);
        }
    }

    /**
     * Create mailer object with settings based on WHMCS configuration
     *
     * @global type $CONFIG
     */
    public function __construct(Reseller $reseller)
    {
        $mailer = new PHPMailer();

//        $mailer->SMTPDebug = 2;
//        $mailer->Debugoutput = 'html';

        $mailer = $reseller->hasCustomMailBox() ? $this->setCustomConfig($mailer, $reseller) : $this->setWhmcsConfig($mailer);

        $mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $this->mailer = $mailer;
    }

    public static function checkConnection(MailConfiguration $configuration)
    {
        $mailer = self::getAndConfigureMailer($configuration);

        if (!$mailer->smtpConnect()) {
            throw new \Exception(Lang::absoluteT('testConnectionMessages', 'connectionError'));
        }

        return true;
    }

    public static function sendTestEmail(MailConfiguration $configuration)
    {
        $reseller = ResellerHelper::getLogged();

        if (!$reseller->exists) {
            throw new \Exception(Lang::absoluteT('testConnectionMessages', 'sendTestEmailFailed'));
        }

        if (!$reseller->settings->private->email) {
            throw new \Exception(Lang::absoluteT('testConnectionMessages', 'setEmailAddressFirst'));
        }

        $mail = new Mailer($reseller);
        $mail->mailer = self::getAndConfigureMailer($configuration);
        $mail->setSender($reseller->settings->private->email, "Test Sender");

        $message = Lang::absoluteT("testConnectionEmailMessage");
        $subject = Lang::absoluteT("testConnectionEmailSubject");

        $mail->addReciever($reseller->client->email);
        $mail->setSubjectAndBody($subject, $message,  []);

        if (!$mail->send()) {
            throw new \Exception(Lang::absoluteT('testConnectionMessages', 'sendTestEmailFailed'));
        }

        return true;
    }

    private static function getAndConfigureMailer(MailConfiguration $configuration):PHPMailer
    {
        global $CONFIG;
        $config = $configuration;

        $mailer = new PHPMailer();
        $mailer->isSMTP();
        $mailer->SMTPAuth   = true;
        $mailer->Username   = $config->getUsername();
        $mailer->Password   = $config->getPassword();
        $mailer->Host = $config->getHostname();
        $mailer->Port = $config->getPort();
        $mailer->SMTPSecure = $config->getSecure();
        $mailer->CharSet = $CONFIG['Charset'];
        $mailer->Timeout = 30;
        $mailer->SMTPDebug = 0;

        return $mailer;
    }

    private function setCustomConfig($mailer, Reseller $reseller)
    {
        global $CONFIG;
        $config = $reseller->getMailConfig();
        $mailer->isSMTP();
        $mailer->SMTPAuth   = true;
        $mailer->Username   = $config->getUsername();
        $mailer->Password   = $config->getPassword();
        $mailer->Host = $config->getHostname();
        $mailer->Port = $config->getPort();
        $mailer->SMTPSecure = $config->getSecure();
        $mailer->CharSet = $CONFIG['Charset'];

        return $mailer;
    }

    private function setWhmcsConfig($mailer)
    {
        global $CONFIG;
        $whmcs8Mailer = [];
        if (Whmcs::isVersion('8.0')) {
            $whmcs8Mailer = json_decode(\decrypt($CONFIG['MailConfig']), false);
        }

        if ( $this->isSMTPSelected() ) {
            $mailer->isSMTP();
            if(!empty($whmcs8Mailer->configuration->username ?: $CONFIG['SMTPUsername'])
                && !empty($whmcs8Mailer->configuration->password ?: $CONFIG['SMTPPassword']))
            {
                $mailer->SMTPAuth   = true;
                $mailer->Username   = $whmcs8Mailer->configuration->username ?: $CONFIG['SMTPUsername'];
                $mailer->Password   = $whmcs8Mailer->configuration->password ?: decrypt($CONFIG['SMTPPassword']);
            } else {
                $mailer->SMTPAuth   = false;
            }
            $mailer->Host = $whmcs8Mailer->configuration->host ?: $CONFIG['SMTPHost'];
            $mailer->Port = $whmcs8Mailer->configuration->port ?: $CONFIG['SMTPPort'];

            if ($whmcs8Mailer->configuration->secure || $CONFIG['SMTPSSL']) {
                $mailer->SMTPSecure = $whmcs8Mailer->configuration->secure ?: $CONFIG['SMTPSSL'];
            }
        } else {
            $mailer->isMail();
        }
        $mailer->CharSet = $CONFIG['Charset'];

        return $mailer;
    }

    /**
     * Set sender for email.
     * If addres was not provided then default WHMCS address will be used
     *
     * @param type $address
     * @param type $name
     */
    public function setSender($address, $name)
    {
        global $CONFIG;

        if (empty($address)) {
            $address = $CONFIG["Email"];
        }

        if (empty($name)) {
            $name = $CONFIG["CompanyName"];
        }

        $this->mailer->setFrom($address, $name, false);
        $this->mailer->AddReplyTo($address, $name);
    }

    /**
     * Add CC to email message
     * Email address can be provided as string or as array
     *
     * @param type $cc
     */
    public function addCC($cc)
    {
        if (!is_array($cc)) {
            $cc = explode(',', $cc);
        }

        foreach ($cc as $email) {
            $this->mailer->AddCC($email);
        }
    }

    /**
     * Add attachments to email.
     *
     * @param type $attachments
     */
    public function addAttachments($attachments)
    {
        global $whmcs;
        $path = $whmcs->getAttachmentsDir();
        $attachments = empty($attachments) ? [] : $attachments;
        foreach ($attachments as $file) {
            $filename = substr($file, strpos($file,'_')+1);
            $this->mailer->AddAttachment($path.DS.$file, $filename);
        }
    }

    /**
     * Add email recievers
     *
     * @param type $recievers
     */
    public function addReciever($recievers)
    {
        if (!is_array($recievers)) {
            $recievers = explode(",", $recievers);
        }

        foreach ($recievers as $reciever) {
            if (!is_array($reciever)) {
                $reciever = explode(":", $reciever);
            }
            $this->mailer->addAddress($reciever[0], $reciever[1]);
        }
    }

    /**
     * Set Subject and Body for email
     *
     * @param type $subject
     * @param type $body
     */
    public function setSubjectAndBody($subject, $body, $params, $plaintext = false)
    {
        global $templates_compiledir;

        //Parse message
        $smarty = new \Smarty();
        $smarty->setCompileDir($templates_compiledir);
        foreach ($params as $key => $value) {
            $smarty->assign($key, $value);
        }

        $this->mailer->Subject = $smarty->fetch('string:'.$subject);
        $body = $smarty->fetch('string:'.html_entity_decode($body, ENT_QUOTES));

        if ($plaintext) {
            $body = str_ireplace(array("<br />","<br>","<br/>"), "\n", $body);
            $body = str_ireplace("\t", "", $body);
            $this->mailer->Body = $body;
        } else {
            $this->mailer->MsgHTML(html_entity_decode($body));
        }
    }

    /**
     * Send prepared email message
     *
     * @return type
     */
    public function send()
    {
        return $this->mailer->send();
    }

    /**
     * Add global CSS, header and footer
     *
     * @param $body
     * @param Reseller $reseller
     * @return string
     */
    public function addGlobalWrappers($body, Reseller $reseller)
    {
        $css = $header = $footer = "";
        if ($reseller->settings->admin->allowEmailGlobalsEdit) {
            $css    = $reseller->settings->private->emailGlobalCSS;
            $header = $reseller->settings->private->emailGlobalHeader;
            $footer = $reseller->settings->private->emailGlobalFooter;
        }

        global $CONFIG;
        if (empty($css)) {
            $css = $CONFIG["EmailCSS"];
        }
        if (empty($header)) {
            $header = $CONFIG["EmailGlobalHeader"];
        }
        if (empty($footer)) {
            $footer = $CONFIG["EmailGlobalFooter"];
        }

        $css = "{literal}{$css}{/literal}";
        if (strpos($header, "[EmailCSS]") !== false) {
            $header = str_replace("[EmailCSS]", "{$css}", $header);
        } else {
            $header = "<style>{$css}</style>{$header}";
        }

        return $header . $body . $footer;
    }

    /**
     * @return bool
     */
    private function isSMTPSelected(): bool
    {
        $mailType = $GLOBALS['CONFIG']['MailType'];

        if ( Whmcs::isVersion('8.0.0') ) {
            $mailConfig = json_decode(\decrypt($GLOBALS['CONFIG']['MailConfig']), false);
            $mailType   = $mailConfig->module;
        }

        return strtolower($mailType) === 'smtp' || strtolower($mailType) === 'smtpmail';
    }
}
