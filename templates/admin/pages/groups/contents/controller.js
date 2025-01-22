{literal}
var RC_ConfigurationSettings = {
    
    groupid: null,
    productsTable: null,
    
    init: function()
    {       
    },
    
    showConfigurationBox: function()
    {
        $(".groups").removeClass("col-sm-12");
        $(".groups").addClass("col-lg-3").addClass("col-md-4").addClass("col-xs-12");
        
        $("#configurationBox").show("slide", { direction: "left" }, 500);
        
        RC_ConfigurationSettings.groupid = $("#groupList .active data").data("groupid");
        
        //Lets init controlers for every panel
        RC_SettingsProducts.init();
        RC_SettingsAddons.init();
        RC_SettingsDomains.init();
    },
    
    showSearchInput: function(trigger)
    {
        var searchBox = $(trigger).parent().parent().find(".contentsListSearch");
        if($(searchBox).is(":visible")) {
            $(searchBox).hide("slide", { direction: "right" }, 250);
        }
        else {
            $(searchBox).show("slide", { direction: "right" }, 250);
        }
    },
    
    initSelect2Fields: function()
    {
        $(".select2").select2({
            placeholder: "{/literal}{html_entity_decode($MGLANG->T('from','select','placeholder'))}{literal}",
            allowClear: true
        });
    },
}
RC_ConfigurationSettings.init();
{/literal}