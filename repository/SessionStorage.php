<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;


/**
 * Description of SessionStorage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class SessionStorage extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\Session';
    }

    public function createNew($key, $time, $session)
    {
        //Create new
        $storage = $this->getModel();
        $storage->key = $key;
        $storage->time = $time;
        $storage->value = $session;

        /* Inserting the same time on any server (DateTimeZone default setting can be various) */
        $storage->created_at = (new \DateTime('now', new \DateTimeZone('utc')))->format('Y-m-d H:i:s');

        $storage->save();
    }

    public function deleteByKey($key)
    {
        $storage = $this->getModel();
        $storage->where("key", $key)->delete();
    }

    public function getStoredByKey($key)
    {
        $storage = $this->getModel();
        $session = $storage->where("key", $key)->first();

        $convertedCreatedAtDate = null;
        if($session) {
            /* Loading information about created_at record - its in UTC Time since 28 line in this file */
            $convertedCreatedAtDate = (new \DateTime(
                $session->created_at->format('Y-m-d H:i:s'),
                new \DateTimeZone('utc')
            ));
        }

        //We do not need this session any more
        $storage->where("key", $key)->delete();

        //Check if session is still valid
        if(($convertedCreatedAtDate ? $convertedCreatedAtDate->getTimestamp() : false) + $session->time < time() && !empty($session)) {
            return false;
        }

        return $session;
    }
}
