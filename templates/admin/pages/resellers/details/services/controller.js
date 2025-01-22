{literal}
var RC_ResellersServices = {
    
    hostingTable: null,
    addonsTable: null,
    domainsTable: null,
    activeServiceid: null,
    
    init: function()
    {
        RC_ResellersServices.loadHostingTable();
        RC_ResellersServices.customDataTableSearch();
    },
    
    refreshHandlers: function()
    {
        RC_ResellersServices.openConfigModal();
        RC_ResellersServices.openReassignModal();
        RC_ResellersServices.openDeleteModal();
    },
    
    /**
     * Add
     */
    openAddModal: function()
    {
        $("#serviceAddForm [name='relid']").val(null).trigger("change");

        $("#serviceAddForm [name='type']").unbind("change");
        $("#serviceAddForm [name='type']").select2();

        $("#serviceAddForm [name='type']").on("change", function()
        {
            $("#serviceAddForm [name='relid']").val(null).trigger("change");

            var type = $(this).val();
            $("#serviceAddForm [name='relid']").select2({
                ajax: {
                url: 'addonmodules.php?module=ResellersCenter&mg-page=services&mg-action=getNotAssigned&type='+type+'&json=1',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var items = [];
                    $.each(result.data, function(index, value)
                    {
                        var text = '';
                        if(type == 'addon') {
                            text = "#"+value.id+" "+value.name+" ("+value.domain+")";
                        }
                        else if(type == 'domain') {
                            text = "#"+value.id+" "+value.domain;
                        }
                        else if(type == 'hosting') {
                            text = "#"+value.id+" "+value.product_name+" ("+value.domain+")";
                        }

                        items.push({id: value.id, text: text});
                    });
                    return {results: items};
                },
                delay: 250
                },
                placeholder: "{/literal}{$MGLANG->T('form','select','placeholder')}{literal}",
            });
        });

        $("#serviceAddForm [name='type']").trigger("change");
        $("#serviceAddModal").modal("show");
    },
    
    submitAddForm: function()
    {
        var form = $("#serviceAddForm").serialize();

        JSONParser.request("assignToReseller|Services", form, function() {
            RC_ResellersServices.drawTables();
            $("#serviceAddModal").modal("hide");
        });
    },

    drawTables: function()
    {
        if (RC_ResellersServices.hostingTable) RC_ResellersServices.hostingTable.draw();
        if (RC_ResellersServices.addonsTable) RC_ResellersServices.addonsTable.draw();
        if (RC_ResellersServices.domainsTable) RC_ResellersServices.domainsTable.draw();
    },
    
    /**
     * Reassign
     */
    openReassignModal: function()
    {
        $(".openReassignService").unbind("click");
        $(".openReassignService").on("click", function()
        {
            var resellerid = $("#serviceReassignForm [name='resellerid']").val();
            
            $("#serviceReassignForm [name='clientid']").select2({
                ajax: {
                    url: 'addonmodules.php?module=ResellersCenter&mg-page=clients&mg-action=getAsssigned&resellerid='+resellerid+'&json=1',
                    processResults: function (data) {
                        var result = JSONParser.getJSON(data);
                        var items = [];
                        $.each(result.data, function(index, value){
                            var client = "#"+value.id+" "+value.firstname+" "+value.lastname;
                            items.push({id: value.id, text: client});
                        });
                        return {results: items};
                    },
                    delay: 250
                },
                placeholder: "{/literal}{$MGLANG->T('form','select','placeholder')}{literal}",
            });
            
            RC_ResellersServices.activeServiceid = $(this).data("serviceid");
            $("#serviceReassignModal").modal("show");
        });
    },
    
    submitReassignForm: function()
    {
        var form = $("#serviceReassignForm").serialize();
        form += "&serviceid="+RC_ResellersServices.activeServiceid;
        
        JSONParser.request("reassignToClient|Services", form, function(){
            $("#serviceReassignModal").modal("hide");
        });
    },
    
    /**
     * Configuration
     */
    openConfigModal: function()
    {
        $(".openConfigService").unbind("click");
        $(".openConfigService").on("click", function(){
            var serviceid = $(this).data("serviceid");
            JSONParser.request("getServiceDetails|Services",{serviceid: serviceid}, function(result){
                if(result.type == 'addon'){
                    $("#serviceConfigForm [name='price']").val(result.recurring);
                    $("#serviceConfigForm [name='billingcycle']").val(result.billingcycle);
                    
                    $("#serviceConfigForm [name='registrationperiod']").val('');
                    $("#serviceConfigForm [name='registrationperiod']").parent().hide();
                    $("#serviceConfigForm [name='billingcycle']").parent().show();
                }
                else if(result.type == 'domain'){
                    $("#serviceConfigForm [name='price']").val(result.recurringamount);
                    $("#serviceConfigForm [name='registrationperiod']").val(result.registrationperiod);
                    
                    $("#serviceConfigForm [name='billingcycle']").val('');
                    $("#serviceConfigForm [name='billingcycle']").parent().hide();
                    $("#serviceConfigForm [name='registrationperiod']").parent().show();
                }
                else if(result.type == 'hosting'){
                    $("#serviceConfigForm [name='price']").val(result.amount);
                    $("#serviceConfigForm [name='billingcycle']").val(result.billingcycle);
                    
                    $("#serviceConfigForm [name='registrationperiod']").val('');
                    $("#serviceConfigForm [name='registrationperiod']").parent().hide();
                    $("#serviceConfigForm [name='billingcycle']").parent().show();
                }
                
                RC_ResellersServices.activeServiceid = serviceid;
                $("#serviceConfigForm .select2").select2();
                $("#serviceConfigModal").modal("show");
            });
        });
    },
    
    submitConfigForm: function()
    {
        var form = $("#serviceConfigForm").serialize();
        form += "&serviceid="+RC_ResellersServices.activeServiceid;
        
        JSONParser.request("updatePricing|Services", form, function(){
            RC_ResellersServices.drawTables();
            $("#serviceConfigModal").modal("hide");
        });
    },
    
    /**
     * Delete
     */
    openDeleteModal: function()
    {
        $(".openDeleteService").unbind("click");
        $(".openDeleteService").on("click", function(){
            RC_ResellersServices.activeServiceid = $(this).data("serviceid");
            $("#serviceDeleteModal").modal("show");
        });
    },
    
    submitDeleteForm: function()
    {
        JSONParser.request(
            "deleteFromReseller|Services", 
            {assignationid: RC_ResellersServices.activeServiceid}, 
            function(){
                RC_ResellersServices.drawTables();
                $("#serviceDeleteModal").modal("hide");
            }
        );
    },
    
    /**
     * Table
     */
    loadHostingTable: function()
    {
        if (this.hostingTable) return;

        RC_ResellersServices.hostingTable = $("#hostingList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=services&mg-action=getAssignedHostingForTable&json=1&datatable=1",
            fnDrawCallback: function(){
                RC_ResellersServices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var resellerid = $("#hostingList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "id",       orderable: true, sortable: false, targets: 0 },
                { data: "product",  orderable: true, sortable: false, targets: 0 },
                { data: "domain",   orderable: true, sortable: false, targets: 0 },
                { data: "client",   orderable: true, sortable: false, targets: 0 },
                { data: "price",    orderable: true, sortable: false, targets: 0 },
                { data: "billingcycle",   orderable: true, sortable: false, targets: 0 },
                { data: "actions",  orderable: false, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-4 text-center"i><"col-sm-4"p>>>',
            oLanguage: {
                sEmptyTable: "{/literal}{$MGLANG->absoluteT('datatable','emptytable')}{literal}",
                sInfo : "{/literal}{$MGLANG->absoluteT('datatable','info')}{literal}",
                sInfoEmpty: "{/literal}{$MGLANG->absoluteT('datatable','infoempty')}{literal}",
                sInfoFiltered: "{/literal}{$MGLANG->absoluteT('datatable','infofiltered')}{literal}",
                sProcessing: "",
                sLengthMenu: "{/literal}{$MGLANG->absoluteT('datatable','lengthMenu')}{literal}",
                oPaginate: {
                    sNext: "{/literal}{$MGLANG->absoluteT('datatable','next')}{literal}",
                    sPrevious: "{/literal}{$MGLANG->absoluteT('datatable','previous')}{literal}",
                }
            }
        });
    },
    
    loadAddonsTable: function()
    {
        if (this.addonsTable) return;

        RC_ResellersServices.addonsTable = $("#addonList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=services&mg-action=getAssignedAddonsForTable&json=1&datatable=1",
            fnDrawCallback: function(){
                RC_ResellersServices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var resellerid = $("#addonList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "id",        orderable: true, sortable: false, targets: 0 },
                { data: "addon",     orderable: true, sortable: false, targets: 0 },
                { data: "domain",    orderable: true, sortable: false, targets: 0 },
                { data: "client",    orderable: true, sortable: false, targets: 0 },
                { data: "price", orderable: true, sortable: false, targets: 0 },
                { data: "billingcycle",   orderable: true, sortable: false, targets: 0 },
                { data: "actions",   orderable: false, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-4 text-center"i><"col-sm-4"p>>>',
            oLanguage: {
                sEmptyTable: "{/literal}{$MGLANG->absoluteT('datatable','emptytable')}{literal}",
                sInfo : "{/literal}{$MGLANG->absoluteT('datatable','info')}{literal}",
                sInfoEmpty: "{/literal}{$MGLANG->absoluteT('datatable','infoempty')}{literal}",
                sInfoFiltered: "{/literal}{$MGLANG->absoluteT('datatable','infofiltered')}{literal}",
                sProcessing: "",
                sLengthMenu: "{/literal}{$MGLANG->absoluteT('datatable','lengthMenu')}{literal}",
                oPaginate: {
                    sNext: "{/literal}{$MGLANG->absoluteT('datatable','next')}{literal}",
                    sPrevious: "{/literal}{$MGLANG->absoluteT('datatable','previous')}{literal}",
                }
            }
        });
    },
    
    loadDomainsTable: function()
    {
        if (this.domainsTable) return;

        RC_ResellersServices.domainsTable = $("#domainList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&mg-page=services&mg-action=getAssignedDomainsForTable&json=1&datatable=1",
            fnDrawCallback: function(){
                RC_ResellersServices.refreshHandlers();
            },
            fnServerParams: function(data) {
                var resellerid = $("#domainList").data("resellerid");
                data.push({ name: "resellerid", value: resellerid});
            },
            columns: [
                { data: "id",        orderable: true, sortable: false, targets: 0 },
                { data: "domain",    orderable: true, sortable: false, targets: 0 },
                { data: "client",    orderable: true, sortable: false, targets: 0 },
                { data: "price",     orderable: true, sortable: false, targets: 0 },
                { data: "period",    orderable: true, sortable: false, targets: 0 },
                { data: "actions",   orderable: false, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 'tr<"table-bottom"<"row"<"col-sm-4"L><"col-sm-4 text-center"i><"col-sm-4"p>>>',
            oLanguage: {
                sEmptyTable: "{/literal}{$MGLANG->absoluteT('datatable','emptytable')}{literal}",
                sInfo : "{/literal}{$MGLANG->absoluteT('datatable','info')}{literal}",
                sInfoEmpty: "{/literal}{$MGLANG->absoluteT('datatable','infoempty')}{literal}",
                sInfoFiltered: "{/literal}{$MGLANG->absoluteT('datatable','infofiltered')}{literal}",
                sProcessing: "",
                sLengthMenu: "{/literal}{$MGLANG->absoluteT('datatable','lengthMenu')}{literal}",
                oPaginate: {
                    sNext: "{/literal}{$MGLANG->absoluteT('datatable','next')}{literal}",
                    sPrevious: "{/literal}{$MGLANG->absoluteT('datatable','previous')}{literal}",
                }
            }
        });
    },
    
    showSearch: function()
    {
        if($(".servicesListSearch").is(":visible")) {
            $(".servicesListSearch").hide("slide", { direction: "right" }, 250);
        }
        else {
            $(".servicesListSearch").show("slide", { direction: "right" }, 250);
        }
    },
    
    customDataTableSearch: function()
    {
        var timer = null;
        $("#servicesListFilter").keyup(function(){
            clearTimeout(timer);
            
            var filter = $(this).val();
            timer = setTimeout(function(){
                if (RC_ResellersServices.hostingTable) RC_ResellersServices.hostingTable.search(filter).draw();
                if (RC_ResellersServices.addonsTable) RC_ResellersServices.addonsTable.search(filter).draw();
                if (RC_ResellersServices.domainsTable) RC_ResellersServices.domainsTable.search(filter).draw();
            }, 500);
        });
    },
    
}
// RC_ResellersServices.init();
{/literal}