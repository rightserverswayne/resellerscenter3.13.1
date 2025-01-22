<script type='text/javascript'>
    
    $(document).ready(function ()
    {
        let htmlPlaceOrderForClientCode = "<div class='alert alert-info'>{$MGLANG->T('addonCA','orderforclient', 'info')} <strong>{$client->firstname} {$client->lastname}</strong> <br> <a href='index.php?m=ResellersCenter&mg-page=Clients&mg-action=cleanAfterOrder&json=1'>{$MGLANG->T('addonCA','orderforclient', 'return')}</a></div>";
        if($(".main-content").length)
        {
            // Six Based Template
            $(".main-content").prepend(htmlPlaceOrderForClientCode);
        } else {
            // Twenty-One Based Template
            $(".primary-content").prepend(htmlPlaceOrderForClientCode);
        }
    });
</script>