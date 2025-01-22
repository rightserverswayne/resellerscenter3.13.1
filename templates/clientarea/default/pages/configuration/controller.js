{literal}
var ResellersCenter_Configuration = 
{
    configData: null,

    init: function()
    {
        $(".checkbox-container .checkbox-switch").bootstrapSwitch();
        this.getConfigData();
        this.initLogoContainer();
        this.initInvoiceLogoContainer();
        this.uploadLogoHandler();
        this.uploadInvoiceLogoHandler();
        this.deleteLogoHandler();
        this.deleteInvoiceLogoHandler();
        this.sortPaymentGatewaysHandler();
        this.assignDescriptionToApiTokenField();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
        
        //Set active tab in settings
        if (window.location.href.indexOf("#RCConfigEmailTemplates") != -1)
        {
            $("#RCConfigGeneralLi").parent().removeClass("active");
            $("#RCConfigEmailTemplatesLi").parent().addClass("active");
        }
    },
    
    submitConfigForm: function()
    {
        $("#MGPageconfiguration .has-error").removeClass("has-error");
        this.checkIfAnyGatewayIsEnabled();
        this.validateApiKey();
        this.validateBillings();
        //Validate
        if(! $.isNumeric($("[name='settings[nextinvoicenumber]']").val()) && $("[name='settings[nextinvoicenumber]']").length){
            $("[name='settings[nextinvoicenumber]']").parents(".form-group").addClass("has-error");
        }
        
        if($("#MGPageconfiguration .has-error").length)
        {
            $('#MGAlerts').alerts('error', "{/literal}{$MGLANG->absoluteT('form', 'validate', 'configuration')}{literal}");
            return;
        }
         
        var form = $("#RCConfiguration form").serialize();
        JSONParser.request("save", form, function(){});
    },
    
    deleteLogoHandler: function()
    {
        $(".deleteLogo").on("click", function(e){
            e.preventDefault();
            type='logo';
            JSONParser.request("deleteLogo", {type:type}, function()
            {
                $("div.logo-container img").hide();
                $("div.logo-container span").show();
                $("a.deleteLogo").hide();
                $(".logo-container img").removeAttr("src");
            });
        });
    },

    deleteInvoiceLogoHandler: function()
    {
        $(".deleteInvoiceLogo").on("click", function(e){
            e.preventDefault();
            type='invoiceLogo';
            JSONParser.request("deleteLogo", {type:type}, function()
            {
                $("div.invoiceLogo-container img").hide();
                $("div.invoiceLogo-container span").show();
                $("a.deleteInvoiceLogo").hide();
                $(".invoiceLogo-container img").removeAttr("src");
            });
        });
    },

    initLogoContainer: function()
    {
        const uploadedLogoFileName = $("input[name='settings[logo]']").val();

        if (uploadedLogoFileName && uploadedLogoFileName.length > 0) {
            this.showImageElements();
        } else {
            this.hideImageElements();
        }
    },

    initInvoiceLogoContainer: function()
    {
        const uploadedInvoiceLogoFileName = $("input[name='settings[invoiceLogo]']").val();

        if (uploadedInvoiceLogoFileName && uploadedInvoiceLogoFileName.length > 0) {
            this.showInvoiceImageElements();
        } else {
            this.hideInvoiceImageElements();
        }
    },

    uploadLogoHandler: function()
    {
        self = this;
        $(".logo-container").on("click", function(){
            $("#RCConfiguration form").find("[name='logoFile']").click();
        });

        $("[name='logoFile']").change(function()
        {

            var formdata = new FormData();
            formdata.append("logo", this.files[0]);
            formdata.append("resellerid", this.files[0]);

            $.ajax({
                url:'index.php?m=ResellersCenter&mg-page=configuration&mg-action=uploadLogo&json=1&type=logo',
                data: formdata,
                type:'POST',
                contentType: false,
                processData: false
            }).success(function(result) {
                result = JSONParser.getJSON(result);
                if (!result.data.error) {
                    self.showImageElements();
                    $("[name='settings[logo]']").val(result.data.logo);
                    $(".logo-container img").removeAttr("src").attr("src", result.data.htmllogopath+'?'+Math.random());
                } else {
                    $('#MGAlerts').alerts('error', result.data.error);
                }
            });
        });
    },

    uploadInvoiceLogoHandler: function()
    {
        self = this;
        $(".invoiceLogo-container").on("click", function(){
            $("#RCConfiguration form").find("[name='invoiceLogoFile']").click();
        });

        $("[name='invoiceLogoFile']").change(function()
        {
            var formdata = new FormData();
            formdata.append("invoiceLogo", this.files[0]);
            formdata.append("resellerid", this.files[0]);

            $.ajax({
                url:'index.php?m=ResellersCenter&mg-page=configuration&mg-action=uploadLogo&json=1&type=invoiceLogo',
                data: formdata,
                type:'POST',
                contentType: false,
                processData: false
            }).success(function(result) {
                result = JSONParser.getJSON(result);
                if(!result.data.error)
                {
                    self.showInvoiceImageElements();
                    $("[name='settings[invoiceLogo]']").val(result.data.logo);
                    $(".invoiceLogo-container img").removeAttr("src").attr("src", result.data.htmllogopath+'?'+Math.random());
                }
                else
                {
                    $('#MGAlerts').alerts('error', result.data.error);
                }
            });
        });
    },

    showInvoiceImageElements: function()
    {
        const image = $("div.invoiceLogo-container img");
        const uploadButton = $("div.invoiceLogo-container span.uploadLogoButton");
        const deleteButton = $("a.deleteInvoiceLogo");

        image.show();
        uploadButton.hide();
        deleteButton.show();
    },

    hideInvoiceImageElements: function()
    {
        const image = $("div.invoiceLogo-container img");
        const uploadButton = $("div.invoiceLogo-container span.uploadLogoButton");
        const deleteButton = $("a.deleteInvoiceLogo");

        image.hide();
        uploadButton.show();
        deleteButton.hide();
    },

    showImageElements: function()
    {
        const image = $("div.logo-container img");
        const uploadButton = $("div.logo-container span.uploadLogoButton");
        const deleteButton = $("a.deleteLogo");

        image.show();
        uploadButton.hide();
        deleteButton.show();
    },

    hideImageElements: function()
    {
        const image = $("div.logo-container img");
        const uploadButton = $("div.logo-container span.uploadLogoButton");
        const deleteButton = $("a.deleteLogo");

        image.hide();
        uploadButton.show();
        deleteButton.hide();
    },
    
    sortPaymentGatewaysHandler: function()
    {
        $("#RCConfigPayments .nav").sortable(
        {
            beforeStop: function(event, ui)
            {
                var order = {};
                $("#RCConfigPayments .nav li a").each(function(index, element){
                    order[index] = $(element).data("gateway");
                });

                JSONParser.request("sortPaymentGateways", {order: order}, function()
                {
                    $.each(order, function(value, gateway)
                    {
                        $("[name='gateways["+gateway+"][order]']").val(value);
                    });
                });

                $("#RCConfigPayments .nav a").trigger( "click" );
            }
        });
        
        $('#RCConfigPayments .nav').sortable({ cancel: '.show-draggable' });
    },
    
    checkIfAnyGatewayIsEnabled: function()
    {
        var isAnyEnabled = false;
        var switches = $("#RCConfigPayments").find("[name$='[enabled]']");
        $.each(switches, function(index, switcher)
        {
            var status = $(switcher).bootstrapSwitch('state');
            if(status)
            {
                isAnyEnabled = true;
            }
        });
        
        if(!isAnyEnabled){
            $(".noGatewayEnabled").show();
        }
        else
        {
            $(".noGatewayEnabled").hide();
        }
    },
    validateApiKey: function()
    {
        var key = $('[name="settings[apikey]"]').val();
        if(key.length > 0 && key.length < 32)
        {
            $("#RCAPIAccess").addClass("has-error");
        }
    },
    validateBillings: function()
    {
        var day = $('[name="settings[consolidatedInvoicesDay]"]').val();
        if (day < 1 || day > 31) {
            $("#RCConfigBillings").addClass("has-error");
        }
    },
    assignDescriptionToApiTokenField: function(url)
    {
        var description = "{/literal}{$MGLANG->T('apiAccess','key','help')}{literal}";
        description = description.replace("{0}", url);
        $('#apiTokenDecription').html(description);
    },
    getConfigData: function()
    {
        $.ajax({
            url:'index.php?m=ResellersCenter&mg-page=configuration&mg-action=getConfig&json=1',
            type:'GET',
            contentType: false,
            processData: false
        }).success(function(result) {
            ResellersCenter_Configuration.assignDescriptionToApiTokenField(result)
        });
    },

    testConnection: function (e)
    {
        successMess = $('#testConnectionMessage .text-success' );
        errorMess = $('#testConnectionMessage .text-danger' );
        errorMess.hide();
        successMess.hide();

        e.preventDefault();
        var form = $("#RCConfiguration form").serialize();

        JSONParser.request("testConnection", form, function(data) {
            if (data.validate) {
                successMess.show();
            } else {
                errorMess.show();
                errorMess.text(data.message);
            }
        });
    },

    testMail: function (e)
    {
        successMess = $('#testMailMessage .text-success' );
        errorMess = $('#testMailMessage .text-danger' );
        errorMess.hide();
        successMess.hide();

        e.preventDefault();
        var form = $("#RCConfiguration form").serialize();

        JSONParser.request("testMail", form, function(data) {
            if (data.validate) {
                successMess.show();
            } else {
                errorMess.show();
                errorMess.text(data.message);
            }
        });
    }
}
ResellersCenter_Configuration.init();
{/literal}