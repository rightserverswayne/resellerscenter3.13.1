<?php
namespace MGModule\ResellersCenter\core;

/**
 * Description of FileUploader
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class FileUploader
{
    public $allowedFormats = array();
    
    private $inputName;
    
    private $sizeMax;
    
    private $uploadDir;
    
    private $targetName;
    
    public function __construct($inputName, $filename, $dir = '../storage/', $maxSize = null, $formats = array()) 
    {
        $this->inputName = $inputName;
        $this->sizeMax = $maxSize;
        $this->allowedFormats = $formats;
        $this->uploadDir = $dir;
        $this->targetName = $filename;
    }
    
    public function saveFileData($data)
    {
        //Add directory seperator at the end of path
        if(substr($this->uploadDir, -1) != DS) {
            $this->uploadDir .= DS;
        }
        
        $fullname = $this->uploadDir . $this->filename;
        file_put_contents($fullname, $data);
    }
    
    /**
     * Check if uploaded file is an image
     * 
     * @return boolean
     */
    public function isImage()
    {
        $check = getimagesize($_FILES[$this->inputName]["tmp_name"]);
        if($check !== false) 
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    
    public function isPDF()
    {
        if($_FILES[$this->inputName]["type"] == "application/pdf")
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Check size of uploaded image
     * 
     * @param type $width
     * @param type $height
     */
    public function isMatchSize($width, $height)
    {
        $size = getimagesize($_FILES[$this->inputName]["tmp_name"]);
        if($size[0] > $width && $width !== null) 
        {
            return false;
        }
        elseif($size[1] > $height && $height !== null)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    /**
     * Try to upload file
     * 
     * @return boolean
     * @throws \Exception
     */
    public function upload()
    {
        try 
        {
            //Check if file meet all requirements
            $this->verify();
            
            //move uploaded file
            $target = $this->uploadDir . $this->targetName;
            if(move_uploaded_file($_FILES[$this->inputName]["tmp_name"], $target)) 
            {
                return "success";
            } 
            else 
            {
                throw new \Exception("There was an error while uploading file");
            }
        }
        catch (\Exception $ex)
        {
            return $ex->getMessage();
        }
    }
    
    /**
     * Verify file using filters set before. 
     * If filters are not set file will pass verification.
     * 
     * @throws Exception
     */
    private function verify()
    {
        //Check Max Size
        if(!empty($this->max) && $_FILES[$this->inputName]["size"] > $this->max) 
        {
            throw new Exception("Your file is too large");
        }
        
        //Check file type
        $type = pathinfo(basename($_FILES[$this->inputName]["name"]), PATHINFO_EXTENSION);
        if(!empty($this->allowedFormats) && !in_array($type, $this->allowedFormats)) 
        {
            $formats = implode(",", $this->allowedFormats); 
            throw new Exception("Only {$formats} files are allowed");
        }
    }
    
}
