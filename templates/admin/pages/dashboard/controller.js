{literal}
var RC_Dashboard = {
    
    init: function()
    {
    },
    
    setSkipDashboard: function()
    {
        JSONParser.request("setSkipDashboard", {null: null}, function(result)
        {
            if(typeof result != 'undefined') 
            {
                var url = window.location.href;

                url = url.substring(0, url.lastIndexOf("/") + 1);
                window.location.href = url + "addonmodules.php?module=ResellersCenter&mg-page=resellers"; 
            }
        });
    }
};
RC_Dashboard.init();
{/literal}