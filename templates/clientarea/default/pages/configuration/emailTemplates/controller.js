{literal}
var ResellersCenter_EmailTemplates = 
{
    init: function()
    {
        tinymce.init({
            selector: '.tinyMCE',
            height: 500,
            menubar: false,
            plugins: [
              'advlist autolink lists link image charmap print preview anchor',
              'searchreplace visualblocks code fullscreen',
              'insertdatetime media table contextmenu paste code'
            ],   
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            },
            toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        });
          
        ResellersCenter_EmailTemplates.removeLanguageHandler();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
    },
    
    addLanguageHandler: function()
    {
        var template = $("#editTemplateForm [name='name']").val();
        
        $("#RCEmailTemplatesAddLang [name='language']").select2({
            ajax: 
            {
                url: 'index.php?m=ResellersCenter&mg-page=configuration&mg-action=getAvailableLanguages&json=1&name='+template,
                processResults: function (data) 
                {
                    var result = JSONParser.getJSON(data);
                    var languages = [];
                    $.each(result.data, function(index, value){
                        languages.push({id: value, text: value});
                    });
                    
                    return {results: languages};
                },
                delay: 250
            },
            width: '100%',
            placeholder: "{/literal}{html_entity_decode($MGLANG->absoluteT('form','select','placeholder'))}{literal}",
        });
        
        //Clear selection
        $("#RCEmailTemplatesAddLang [name='language']").select2("val", "");      
        
        $("#RCEmailTemplatesAddLang").modal("show");
        
    },
    
    addLanguageSubmit: function()
    {
        var language = $("#RCEmailTemplatesAddLang [name='language']").val();
        if(!language) return;
        language = language.toLowerCase();
        $("#editTemplateForm").append('<input type="hidden" name="selectedLanguage" value='+language+'>');
        var tab = $("#langTab_default").clone();
        tab.attr("id", 'langTab_'+language);
        tab.removeClass("active");
        
        tab.find(".mce-tinymce").remove();
        tab.find("textarea").removeAttr("id");
        tab.find("[name='templates[default][subject]']").attr("name", "templates["+language+"][subject]");
        tab.find("[name='templates[default][message]']").attr("name", "templates["+language+"][message]");
        tab.find("textarea").css("display", "block");

        $("#RCEmailTemplateEdit").find("ul").append('<li style="display: flex;"><a href="#langTab_'+language+'" data-toggle="tab" >'+$("#RCEmailTemplatesAddLang [name='language']").val()+'</a> <button type="button" class="close deleteLanguage" data-language="'+language+'"><span aria-hidden="true">×</span><span class="sr-only"></span></button> </li>');
        $("#editTemplateForm .tab-content").append(tab);
        
        tinymce.init({
            selector: "[name='templates["+language+"][message]'",
            height: 500,
            menubar: false,
            plugins: [
              'advlist autolink lists link image charmap print preview anchor',
              'searchreplace visualblocks code fullscreen',
              'insertdatetime media table contextmenu paste code'
            ],
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            },
            toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        });
          
        $("#RCEmailTemplatesAddLang").modal("hide");

        //Save new template
        ResellersCenter_EmailTemplates.saveChanges(language);
        ResellersCenter_EmailTemplates.removeLanguageHandler();
        RC_TwentyOne_Helper.twentyOneLiSelector();
        $("form#editTemplateForm > input[name='selectedLanguage']").remove();
    },
    
    removeLanguageHandler: function()
    {
        $(".deleteLanguage").unbind("click");
        $(".deleteLanguage").on("click", function()
        {
            var template = $("#editTemplateForm [name='name']").val();
            var tmplang = $(this).data("language");

            JSONParser.request("deleteTemplate", {template: template, language: tmplang}, function(){});

            //Remove from list
            $("#langTab_"+tmplang).remove();
            $("[data-language='"+tmplang+"']").parent().remove();
        });
    },
    
    saveChanges: function(language = null)
    {
        var form = $("#editTemplateForm").serialize();

        JSONParser.request("saveTemplate", form, function(result)
        {
            if (result.success && language && result.clonedTemplate) {
                var tab = $("#langTab_" + language)

                tab.find("[name='templates["+language+"][subject]']").val(result.clonedTemplate.subject);
                tab.find("[name='templates["+language+"][message]']").text(result.clonedTemplate.message);

                tab.find(".mce-tinymce").remove();
                tab.find("textarea").removeAttr("id");
                tab.find("[name='templates["+language+"][subject]']").val(result.clonedTemplate.subject);
                tab.find("[name='templates["+language+"][message]']").text(result.clonedTemplate.message);
                tab.find("textarea").css("display", "block");

                tinymce.init({
                    selector: "[name='templates["+language+"][message]'",
                    height: 500,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table contextmenu paste code'
                    ],
                    setup: function (editor) {
                        editor.on('change', function () {
                            editor.save();
                        });
                    },
                    toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                });
            }
        });
    },
}
ResellersCenter_EmailTemplates.init();
{/literal}