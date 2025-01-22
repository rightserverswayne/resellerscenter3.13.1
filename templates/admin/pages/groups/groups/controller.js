{literal}
var RC_ConfigurationGroups = {
    
    activeGroupid: null,
    groupTable: $("#groupList"),
    settings: null,
    
    init: function()
    {
        RC_ConfigurationGroups.groupsTableHandler();
        RC_ConfigurationGroups.groupsSearchHanlder();
        $("#groupEditFormModal .checkbox-switch").bootstrapSwitch();
        $("#groupCreateFormModal .checkbox-switch").bootstrapSwitch();
    },
    
    refreshHandlers: function()
    {
        RC_ConfigurationGroups.openGroupConfiguration();
    },
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Groups Controll
     */
    groupsTableHandler: function()
    {
        RC_ConfigurationGroups.groupTable = $("#groupList").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: false,
            ajax: function(data, callback, settings){
                var filter = $("#groupListFilter").val();
                JSONParser.request('getGroupsTableData', {filter: filter}, function(data)
                {
                    $("#groupList").find("thead").remove();
                    callback(data);
                    RC_ConfigurationGroups.refreshHandlers();
                });
            },
            fnDrawCallback: function()
            {
                RC_ConfigurationGroups.openEditFormHandler();
                
                $("#groupList").find("tr").first().addClass("active");
                RC_ConfigurationGroups.activeGroupid = $("#groupList .active data").data("groupid");
                
                if(typeof(RC_ConfigurationGroups.activeGroupid) !== 'undefined')
                {
                    RC_ConfigurationSettings.showConfigurationBox();
                }
                else
                {
                    $(".groups").removeClass("col-lg-3").removeClass("col-md-4").removeClass("col-xs-12");
                    $(".groups").addClass("col-sm-12");
                    $("#configurationBox").hide();
                }
            },
            columns: [
                { orderable: false, sortable: false, targets: 0 },
                { orderable: false, sortable: false, targets: 0 },
                { orderable: false, sortable: false, targets: 0 },
                { orderable: false, sortable: false, targets: 0 },
              ],
            bPaginate: false,
            sDom: '',
        });
    },
    
    openSearchContainer: function()
    {
        if($(".groupListSearch").is(":visible")) {
            $(".groupListSearch").hide();
        }
        else {
            $(".groupListSearch").show();
        }
    },
    
    groupsSearchHanlder: function()
    {
        $("#groupListFilter").keyup(function(){
            timer = setTimeout(function(){
                RC_ConfigurationGroups.groupTable.draw();
            }, 500);
        });
    },
    
    openGroupConfiguration: function()
    {
        $("#groupList tr").unbind("click");
        $("#groupList tr").on("click", function(){
            $("#groupList tr").removeClass("active");
            $(this).addClass("active");
            RC_ConfigurationGroups.activeGroupid = $("#groupList .active data").data("groupid");
            
            if(typeof(RC_ConfigurationGroups.activeGroupid) !== 'undefined')
            {
                RC_ConfigurationSettings.showConfigurationBox();
            }
        });
    },
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Form Controll
     */
    openCreateFormHandler: function()
    {
        $("#groupCreateFormModal").modal("show");
    },
    
    submitForm: function()
    {
        var form = $("#groupCreateForm").serialize();
        
        JSONParser.request('createGroup', form, function(result){
            RC_ConfigurationGroups.groupTable.ajax.reload();

            $("#groupCreateFormModal").modal("hide");
        });
    },
    
    
    openEditFormHandler: function()
    {
        $(".editGroupName").unbind("click");
        $(".editGroupName").on("click", function()
        {
            $("#groupEditFormModal [name='name']").val($(this).data("groupname"));
            $("#groupEditFormModal [name='id']").val($(this).data("groupid"));

            JSONParser.request('getGroupSettings', {groupId:$(this).data("groupid")}, function(result)
            {
                $("#groupEditFormModal [name='settings[enableConsolidatedInvoices]']").bootstrapSwitch('state', result.enableConsolidatedInvoices);
                $("#groupEditFormModal [name='settings[endClientConsolidatedInvoices]']").bootstrapSwitch('state', result.endClientConsolidatedInvoices);
                if (result.consolidatedInvoicesDay) {
                    $("#groupEditFormModal [name='settings[consolidatedInvoicesDay]']").val(result.consolidatedInvoicesDay);
                }
            });

            $("#groupEditFormModal").modal("show");
        });
        
    },
    
    editGroup: function()
    {
        var form = $("#groupEditForm").serialize();
        JSONParser.request('editGroupName', form, function(result)
        {
            RC_ConfigurationGroups.groupTable.ajax.reload();
            $("#groupEditFormModal").modal("hide");
        });
    },

    openDeleteFromHandler: function()
    {
        $("#groupDeleteFormModal").modal("show");
    },
    
    deleteGroup: function()
    {
        JSONParser.request('deleteGroup', {groupid: RC_ConfigurationGroups.activeGroupid}, function(data){
            RC_ConfigurationGroups.groupTable.ajax.reload();

            $("#groupDeleteFormModal").modal("hide");
        });
    }
};
RC_ConfigurationGroups.init();
{/literal}