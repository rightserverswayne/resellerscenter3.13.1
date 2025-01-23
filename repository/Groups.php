<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

use MGModule\ResellersCenter\models\Group;
use \MGModule\ResellersCenter\repository\Resellers;

/**
 * Description of Groups
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Groups extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\Group';
    }
    
    /**
     * Create new Reseller Group
     * 
     * @since 3.0.0
     * @param type $name
     */
    public function createNew($name)
    {
        $group = new Group();
        $group->setName($name);
        $group->save();
        return $group;
    }
    
    /**
     * Get groups for datatable.
     * @TODO: update it to use DatatableDecorator class
     * 
     * @since 3.0.0
     * @param type $filter
     * @return type
     */
    public function getDataForTable($filter)
    {
        $query = Group::whereNull('deleted_at');
        if(!empty($filter))
        {
            $query->where("name","LIKE","%$filter%");
        }

        $groups = $query->get();

        $result = ["data" => []];
        foreach($groups as $group)
        {
            $row = [];
            
            $resellers = new Resellers();
            $row[] = "<td class='group-id-col'><data data-groupid='{$group->id}'></data> #{$group->id}</td>";
            $row[] = "<td>{$group->name}</td>";
            $row[] = "<td><button data-groupid='{$group->id}' data-groupname='{$group->name}' data-toggle='tooltip' title='".\MGModule\ResellersCenter\mgLibs\Lang::T('button','editGroupNameTooltip')."'  class='editGroupName btn btn-outline btn-default btn-primary btn-icon-only' style='padding: 1px 3px 0px 4px; margin: 1px !important; background-color: #777; border: none; top: -1px;'><icon class='fa fa-edit' style='margin-top: 2px;  text-shadow: 2px 2px 4px #333; color: #fff'></icon></button></td>";;
            $row[] = "<td><label class='label label-default pull-right' style='padding: 4px 7px; font-size:12px;'>{$resellers->getResellersNoByGroupId($group->id)}</label></td>";
            
            $result["data"][] = $row;
        }

        $result['recordsFiltered'] = $result['recordsTotal'] = $query->count();
        return $result;
    }

    public function getSettingsByGroupId($groupId):array
    {
        $settings = [];
        $model = $this->getModel();
        $group = $model->find($groupId);
        foreach ($group->settings as $setting) {
            $settings[$setting->setting] = $setting->value;
        }
        return $settings;
    }
    
    public function getContentDataForTable($type, $groupid, $dtRequest)
    {
        // ...existing code...
        $query = Capsule::table('tblproducts')
            ->select(
                'tblproducts.id as relid',
                'tblproducts.name as product_name',
                'tblproductgroups.name as product_group', // Join and select product_group
                'tblproducts.paytype as payment_type',
                'tblproducts.gid as group_id',
                'tblproducts.hidden as hidden'
            )
            ->join('tblproductgroups', 'tblproducts.gid', '=', 'tblproductgroups.id') // Join with tblproductgroups
            ->where('tblproducts.hidden', '=', 0)
            ->where('tblproducts.type', '=', $type)
            ->where('tblproducts.groupid', '=', $groupid);
        // ...existing code...
    }
}