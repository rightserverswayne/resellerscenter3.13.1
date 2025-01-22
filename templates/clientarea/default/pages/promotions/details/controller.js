{literal}
var ResellersCenter_PromotionsDetails = 
{
    table: null,
    init: function()
    {
        $(".select2").select2({placeholder: "{/literal}{html_entity_decode($MGLANG->absoluteT('form','select','placeholder'))}{literal}"});
        $(".checkbox-switch").bootstrapSwitch();
        
        this.resellerContentsHandler();
        this.upgradeConfigOptionsHandler();
        
        this.sendSaveButtonHandler();
        this.dateTimePickerHandler();
        this.upgradesHandler();
    },
    
    resellerContentsHandler: function()
    {
        $("[name='promotion[appliesto][]'], [name='promotion[requires][]']").select2({
            ajax: 
            {
                url: 'index.php?m=ResellersCenter&mg-page=promotions&mg-action=getResellerContents&json=1',
                processResults: function (data) 
                {
                    var data = JSONParser.getJSON(data);
                    var groups = [];
                    var result = [];
                    
                    $.each(data.data, function(index, value)
                    {
                        if(groups.indexOf(value.groupname) === -1) 
                        {
                            groups.push(value.groupname);                                                       
                            result.push({id: 'group', text: value.groupname, children: []});
                        }
                        
                        var text = '#' + value.id + ' ' + value.name;
                        
                        if(value.groupname == "Addons")
                        {
                            value.id = "A"+value.id;
                        }
                        else if(value.groupname == "Domains")
                        {
                            text = value.extension;
                            value.id = "D"+value.extension;
                        }
                        
                        result.push({id: value.id, text: text});
                    });
                    
                    return {results: result};
                },
                delay: 250,
            },
            width: '100%',
            placeholder: "{/literal}{$MGLANG->absoluteT('form','select','placeholder')}{literal}",
        });
    },
    
    upgradeConfigOptionsHandler: function()
    {
        $("[name='promotion[upgradeconfig][configoptions][]']").select2({
            ajax: 
            {
                url: 'index.php?m=ResellersCenter&mg-page=promotions&mg-action=getConfigOptions&json=1',
                processResults: function (data) 
                {
                    var data = JSONParser.getJSON(data);
                    var groups = [];
                    var result = [];
                    
                    $.each(data.data, function(index, value)
                    {
                        if(groups.indexOf(value.name) === -1) 
                        {
                            groups.push(value.name);                                                       
                            result.push({id: 'group', text: value.name, children: []});
                        }
                        
                        $.each(value.options, function(index, option)
                        {
                            var text = '#' + option.id + ' ' + option.optionname;
                            result.push({id: option.id, text: text});
                        });
                    });
                    
                    return {results: result};
                },
                delay: 250,
            },
            width: '100%',
            placeholder: "{/literal}{$MGLANG->absoluteT('form','select','placeholder')}{literal}",
        });
    },
    
    sendSaveButtonHandler: function()
    {
        $(".savePromotionBtn").on("click", function(e)
        {
            e.preventDefault();
            
            var data = $("#RCEditPromotionFrom").serialize();
            JSONParser.request("save", data, function(result)
            {
                if(result["success"])
                {
                    //Redirect to promotions list
                    window.location.href = "index.php?m=ResellersCenter&mg-page=promotions";
                }
            });
        });
    },
    
    generateRandomPromotionCode: function()
    {
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        var res = [];
        for(var i = 0; i < 10; i++)
        {
            res.push(possible.charAt(Math.floor(Math.random() * possible.length)));
        }

        var text = res.join("");
        $("[name='promotion[code]']").val(text);
    },
    
    upgradesHandler: function()
    {
        $("#RCEditPromotionFrom [name='promotion[upgrades]']").on("switchChange.bootstrapSwitch", function(event, state)
        {
            $("#RCEditPromotionFrom .upgradeOptions").slideToggle(500);
        });
    },
    
    dateTimePickerHandler: function()
    {
        //Set start dates
        var today = new Date();
        $('.startDate').datetimepicker({
            format: "YYYY-MM-DD",
        });
        
        $('.expiryDate').datetimepicker({
            format: "YYYY-MM-DD",
            useCurrent: false //Important! See issue #1075
        });
       
        $(".startDate").on("dp.change", function (e) 
        {
            $('.expiryDate').data("DateTimePicker").minDate(e.date);
        });
               
        $(".expiryDate").on("dp.change", function (e) 
        {
            $('.startDate').data("DateTimePicker").maxDate(e.date);
        });
    },
}
ResellersCenter_PromotionsDetails.init();
{/literal}