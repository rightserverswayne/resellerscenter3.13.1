{literal}
var ResellersCeneter_Dashboard = 
{
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
                window.location.href = url + "index.php?m=ResellersCenter"; 
            }
        });   
    }
}
ResellersCeneter_Dashboard.init();
{/literal}