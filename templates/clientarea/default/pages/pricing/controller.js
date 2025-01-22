{literal}
var ResellersCenter_Pricing = 
{
    init: function()
    {
        ResellersCenter_Pricing.customDataTableSearch();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
        

    },
   
    addNewItem: function()
    {
        var activeTab = $("#RCPricing .nav-tabs .active").find("a").attr("href");
        if(activeTab == '#RCPricingProducts') {
            ResellersCenter_PricingProducts.openAddPricingModal();
        }
        else if(activeTab == '#RCPricingAddons') {
            ResellersCenter_PricingAddons.openAddPricingModal();
        }
        else if(activeTab == '#RCPricingDomains') {
            ResellersCenter_PricingDomains.openAddPricingModal();
        }
    },
    
    showSearch: function()
    {
        if($(".pricingSearch").is(":visible")) {
            $(".pricingSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".pricingSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $(".pricingSearch input").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function()
            {
                if(typeof(ResellersCenter_PricingProducts) != 'undefined') 
                {
                    ResellersCenter_PricingProducts.table.search(filter).draw();
                    ResellersCenter_PricingAddons.table.search(filter).draw();
                }
                
                if(typeof(ResellersCenter_PricingDomains) != 'undefined') 
                {
                    ResellersCenter_PricingDomains.table.search(filter).draw();
                }
                
            }, 500);
        });
    }
}
ResellersCenter_Pricing.init();
{/literal}