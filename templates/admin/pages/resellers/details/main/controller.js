{literal}
var RC_ResellersMainDetails = {
    init: function()
    {
        $('#confirmGroupChange').on('hidden.bs.modal', function () {
            RC_ResellersMainDetails.cancelGroupChange();
        });
    },
    
    /**
     * Delete Reseller
     */
    openDeleteModal: function()
    {
        $("#deleteResellerModal").modal("show");
    },
    
    submitDeleteForm: function()
    {
        var form = $("#deleteResellerForm").serialize();
        
        JSONParser.request("deleteReseller", form, function(){
            $("#deleteResellerModal").modal("hide");
            window.location.href = "addonmodules.php?module=ResellersCenter&mg-page=resellers";
        });
    },
    
    hideGroupEdit: function()
    {
        $(".groupEditBtn").show();
        $("#changeGroup").find("a").show();
        $("#changeGroup").find("form").hide();
    },
    
    /**
     * Change Reseller's group
     */
    showGroupEdit: function(trigger)
    {
        var current = $("#changeGroup").data("groupid");
        $("#changeGroup select option").each(function(index, option){
            if($(option).val() == current){
                $(option).attr("selected", "selected");
            }
        });

        $(trigger).hide();
        $("#changeGroup").find("a").hide();
        $("#changeGroup").find("form").show();
    },
    
    showConfirmModal: function()
    {
        $("#confirmGroupChange").modal("show");
    },
    
    saveGroupForm: function()
    {
        var data = $("#changeGroup form").serialize();
        
        JSONParser.request("updateResellerGroup", data, function(){
            var groupid = $("#changeGroup [name='groupid']").val();
            var groupname = $("#changeGroup option:selected").text();
            
            $("#changeGroup").find("a").attr("href","addonmodules.php?module=ResellersCenter&mg-page=groups&gid="+groupid);
            $("#changeGroup").find("a").html(groupname);
            
            $("#changeGroup").find("a").show();
            $("#changeGroup").find("form").hide();

            $("#changeGroup").parent().find(".rc-actions a").show();
            $("#confirmGroupChange").modal("hide");
        });
    },
    
    cancelGroupChange: function()
    {
        var current = $("#changeGroup").data("groupid");
        $("#changeGroup [name='groupid']").val(current);
        
        $("#confirmGroupChange").modal("hide");
    }
}

RC_ResellersMainDetails.init();
{/literal}