{literal}
var RC_JSONParser;

var RCAdminAreaController = 
{
    page: null,
    
    init: function()
    {
        var url =  window.location.href;
        url = url.substring(0, url.lastIndexOf("/") + 1);
              
        RC_JSONParser = $.extend({}, JSONParser);
        RC_JSONParser.create(url + 'addonmodules.php?module=ResellersCenter');
        this.page = this.getCurrentPageFromPath();
        if(this.page == "clients.php")
        {
            this.addRCLabelsToClients();
        }
        if(this.page == "clientsservices.php")
        {
            this.blockUpgradeDowngrade();
        }

        this.hideAbordedByHookMsg();
    },
    
    blockUpgradeDowngrade: function()
    {
        RC_JSONParser.request("isResellerService|Services",{json: 1, serviceid: this.getParamValueFromURL("id")}, function(result)
        {
            if(result === true)
            {
                //WHMCS 7.10
                $('button[href^="clientsupgrade.php"]').replaceWith("<span class='label label-info'>{/literal}{$MGLANG->absoluteT('AdminArea','label','blockUpgradeDowngradeButton')}{literal}</span>");

                //WHMCS 8.0+
                $('a[href^="clientsupgrade.php"]').replaceWith("<span class='label label-info'>{/literal}{$MGLANG->absoluteT('AdminArea','label','blockUpgradeDowngradeButton')}{literal}</span>");
            }
        });
    },
    
    addRCLabelsToClients: function()
    {
        RC_JSONParser.request("getResellers|Resellers", {json: 1}, function(result)
        {
            $.each(result, function(index, obj){
                $("#sortabletbl0").find("td:nth-child(2) a").each(function(index, element)
                {
                    if($(element).text() == obj.client_id) 
                    {
                        $(element).parent().append(" <div class='label label-info'>Reseller</div>");
                    }
                });
            });
        });
    },
    
    getIdsFormClientsTable: function(table)
    {
        var clientids = [];
        table.find("td:nth-child(2) a").each(function(index, element)
        {
            var id = $(element).text();
            clientids.push(id);
        });
        
        return clientids;
    },
    
    getCurrentPageFromPath: function()
    {
        var path = window.location.pathname;
        var array = path.split("/");         
        
        return array[array.length - 1];
    },
    
    getParamValueFromURL: function(name, url)
    {
        if(typeof(url) == 'undefined')
        {
            url = location.href;
        }
        
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( url );
        
        return results == null ? null : results[1];
    },
    
    hideAbordedByHookMsg: function()
    {
        if(sentByResellersCenter)
        {
            $("p:contains('Email Send Aborted By Hook')").remove();
            $("#profileContent .errorbox:contains('Email Send Aborted By Hook')").remove();
        }
    }
}

$(document).ready(function(){
    RCAdminAreaController.init();
})
{/literal}