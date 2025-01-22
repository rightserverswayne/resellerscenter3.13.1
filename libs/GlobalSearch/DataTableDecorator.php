<?php

namespace MGModule\ResellersCenter\libs\GlobalSearch;

use MGModule\ResellersCenter\core\datatable\DatatableDecorator as CoreDataTableDecorator;
use MGModule\ResellersCenter\mgLibs\Smarty;

class DataTableDecorator extends CoreDataTableDecorator
{
    protected function parseButtons($row)
    {
        $buttons = '';
        foreach($this->buttons[$row['type']] as $button)
        {
            if($this->skipButton($button["if"], $row)){
                continue;
            }

            if($button["href"]) {
                $button["link"] = $this->generateLink($row, $button["href"]);
            }

            $data  = '';
            foreach($button["data"] as $name => $valueKey)
            {
                $data .= "data-{$name}='{$row[$valueKey]}'";
            }

            //Use <a> tag instead of <button>
            $button = array_merge($button, array("data" => $data));
            $buttons .= $button["href"] ? Smarty::I()->view("href", $button) : Smarty::I()->view("button", $button);
        }


        return "<td class='datatableActions'>{$buttons}</td>";
    }
}