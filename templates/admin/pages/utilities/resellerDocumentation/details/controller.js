{literal}
var RC_ResellerDocumentationDetails = 
{
    table: null,
    
    init: function()
    {
        $("#documentationDetails .select2").select2();
        RC_ResellerDocumentationDetails.saveButtonHandler();
        RC_ResellerDocumentationDetails.uploadPdfHandler();
        RC_ResellerDocumentationDetails.deletePdfHandler();
    },
    
    saveButtonHandler: function()
    {
        $(".saveDetails").on("click", function()
        {
            tinymce.triggerSave();
            
            var id = $("#documentationDetails [name='id']").val();
            var resellers = $("#documentationDetails [name='resellers']").val();
            var name = $("#documentationDetails [name='name']").val();
            var content = $("#documentationDetails [name='content']").val();
            
            JSONParser.request("saveDocumentation", {id: id, name: name, resellers: resellers, content: content}, function(res){});
        });
    },
        
    uploadPdfHandler: function()
    {
        $(".uploadPdf").on("click", function()
        {
            var id = $("#documentationDetails [name='id']").val();
            
            var formdata = new FormData();
            formdata.append("pdf", $("[name='pdf']")[0].files[0]);
            formdata.append("id", id);

            $.ajax({
                url:'addonmodules.php?module=ResellersCenter&mg-page=utilities&mg-action=saveDocumentationPdf&json=1',
                data: formdata,
                type:'POST',
                contentType: false,
                processData: false
            }).success(function(result) {
                result = JSONParser.getJSON(result);
                
                if(!result.data.error)
                {
                    $(".pdfViersionInfo a").attr("href", result.data.htmlpdfpath);
                    $(".pdfViersionInfo a").text(result.data.filename);
                    $(".pdfViersionInfo").show();
                }
                else
                {
                    $('#MGAlerts').alerts('error', result.data.error);
                }
            });
        });
    },
    
    deletePdfHandler: function()
    {
        $(".deletePdf").on("click", function()
        {
            var id = $("#documentationDetails [name='id']").val();

            JSONParser.request("deleteDocumentationPdf", {id: id}, function()
            {
                $(".pdfViersionInfo").hide();
            });
        });
    }
}
RC_ResellerDocumentationDetails.init();
{/literal}