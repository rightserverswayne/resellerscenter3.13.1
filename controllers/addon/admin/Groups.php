<?php
namespace MGModule\ResellersCenter\Controllers\Addon\Admin;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Exceptions\ConsolidatedSettingException;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\GroupsSettings;
use MGModule\ResellersCenter\repository\Resellers as ResellersRepo;
use MGModule\ResellersCenter\repository\Groups as GroupsRepo;

use MGModule\ResellersCenter\repository\whmcs\Addons;
use MGModule\ResellersCenter\repository\whmcs\Products;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\repository\whmcs\DomainPricing;

use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\Counting;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\Request;

/**
 * Main configuration controller.
 * Here goes all actions from configuration page.
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Groups extends AbstractController
{
    public function indexHTML()
    {
        $vars = [];
        $products = new Products();
        $vars["products"] = $products->all();

        $addons = new Addons();
        $vars["addons"] = $addons->all();
        
        $tlds = new DomainPricing();
        $vars["tlds"] = $tlds->getAvailableTlds();
        
        $vars["counting_types"] = Counting::getAvailableCountingTypes();
        
        $currencies = new Currencies();
        $vars["currencies"] = $currencies->getAvailableCurrencies();
        
        return array(
            'tpl'   => 'base',
            'vars' => $vars
        );
    }
    
    public function createGroupJSON()
    {
        $name = Request::get('name');
        $settings = Request::get('settings');
        
        $groups = new GroupsRepo();
        $newGroup = $groups->createNew($name);

        $settingsRepo = new GroupsSettings();
        $settingsRepo->updateSettings($newGroup->id, $settings);
        
        EventManager::call("groupCreated", $newGroup->id, $name);
        return array("success" => Lang::T('group','create','form','success'));
    }
    
    public function editGroupNameJSON()
    {
        $id = Request::get('id');
        $name = Request::get('name');
        $settings = Request::get('settings');

        $repo = new GroupsRepo();
        $repo->update($id, array("name" => $name));

        $settingsRepo = new GroupsSettings();

        try {
            $settingsRepo->updateSettings($id, $settings);
            return array("success" => Lang::T('group','update','form','success'));
        } catch (ConsolidatedSettingException $e) {
            return array("error" => Lang::absoluteT('consolidatedInvoices','errorMessages', $e->getMessage()));
        } catch (\Exception $e) {
            return array("error" => Lang::T('group','update','form','failed'));
        }
    }
    
    public function deleteGroupJSON()
    {
        $groupid = Request::get("groupid");

        //Check if there are no resellers assigned to group
        $resellers = new ResellersRepo();
        if($resellers->getResellersNoByGroupId($groupid) == 0)
        {
            $groups = new GroupsRepo();
            $groups->delete($groupid);
        }
        else
        {
            return array("error" => Lang::T('group','delete','form','failed'));
        }
        
        EventManager::call("groupDeleted", $groupid);
        return array("success" => Lang::T('group','delete','form','success'));
    }
    
    public function getGroupsTableDataJSON()
    {
        $filter = Request::get("filter");
        
        $groups = new GroupsRepo();
        $result = $groups->getDataForTable($filter);
        
        return $result;
    }

    public function getGroupSettingsJSON()
    {
        $groupId = Request::get("groupId");
        $groupsRepo = new GroupsRepo();
        return $groupsRepo->getSettingsByGroupId($groupId);
    }
}
