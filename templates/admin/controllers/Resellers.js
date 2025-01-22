{literal}

var RC_JSONParser;

var ResellersController =
{
    page: null,
    params: "",

    init: function()
    {
        var baseUrl =  window.location.href;
        
        if (typeof(whmcsUrl) == 'undefined') {
            url = baseUrl.substring(0, baseUrl.lastIndexOf("/") + 1) + 'addonmodules.php?module=ResellersCenter';
        } else {
            url = whmcsUrl + '/admin/addonmodules.php?module=ResellersCenter'
        }
        
        RC_JSONParser = $.extend({}, JSONParser);
        RC_JSONParser.create(url);

        this.page = this.getCurrentPageFromPath();
        this.params = this.getParamValueFromURL('rp', baseUrl);

        if (this.page == "clients.php") {
            this.addResellerColumnToClientTable();
        } else if (this.page == "index.php" && this.params) {
            if (this.params.includes("services")) {
                this.addResellerColumnToServiceTable('getProductsWithReseller');
            } else if (this.params.includes("domains")) {
                this.addResellerColumnToServiceTable('getDomainsWithReseller');
            } else if (this.params.includes("addons")) {
                this.addResellerColumnToServiceTable('getAddonsWithReseller')
            }
        }
    },

    addResellerColumnToClientTable: function()
    {
        var table = $("#sortabletbl0").find("tr").first();
        var td = table.find("th").last().clone();
        td.html("{/literal}{$MGLANG->absoluteT('AdminArea','reseller')}{literal}");
        table.append(td);

        $("#sortabletbl0").find("td:nth-child(2) a").each(function(index, element)
        {
            row = $(element).parent().parent();
            td = row.find("td:last").clone();
            td.html("");
            row.append(td);
        });

        RC_JSONParser.request("getClientsWithReseller|AdminAreaIntegration", {json: 1}, function(result)
        {
            $.each(result, function(index, obj){
                $("#sortabletbl0").find("td:nth-child(2) a").each(function(index, element)
                {
                    if ($(element).text() == obj.client_id) {
                        url = "clientssummary.php?userid="+obj.reseller_id;
                        $(element).parent().parent().find("td:last").html(
                            "<a href='"+url+"'>"+obj.resellerInfo+"</a>");
                    }
                });
            });
        });
    },

    addResellerColumnToServiceTable: function(methodName)
    {
        var table = $("#sortabletbl0").find("tr").first();
        var td = table.find("th").last().clone();
        td.html("{/literal}{$MGLANG->absoluteT('AdminArea','reseller')}{literal}");
        $(td).insertBefore( table.find("th").last());

        $("#sortabletbl0").find("td:nth-child(2) a").each(function(index, element)
        {
            row = $(element).parent().parent();
            td = row.find("td:last").clone();
            td.html("");
            $(td).insertBefore( row.find("td:last"));
        });

        RC_JSONParser.request(methodName+"|AdminAreaIntegration", {json: 1}, function(result)
        {
            $.each(result, function(index, obj){
                $("#sortabletbl0").find("td:nth-child(2) a").each(function(index, element)
                {
                    if ($(element).text() == obj.relid) {
                        url = "clientssummary.php?userid="+obj.reseller_id;
                        $(element).parent().parent().find("td:last").prev().html(
                            "<a href='"+url+"'>"+obj.firstname+" "+obj.lastname+"</a>");
                    }
                });
            });
        });
    },

    getCurrentPageFromPath: function()
    {
        var path = window.location.pathname;
        var array = path.split("/");

        return array[array.length - 1];
    },

    getParamValueFromURL: function(name, url)
    {
        if (typeof(url) == 'undefined') {
            url = location.href;
        }

        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( url );

        return results == null ? null : results[1];
    }
};

$(document).ready(function(){
    ResellersController.init();
})

{/literal}
