<script type='text/javascript'>
    $(document).ready(function ()
    {
        let htmlCodeMainDiv = '<div class="alert alert-info admin-masquerade-notice"';

        var menu = $("#top-nav");
        if(! menu.length) {
            menu = $(".top-nav");
        }

        // If still not found - check twentyOne template
        if(!menu.length) {
           menu = $('.active-client');
           htmlCodeMainDiv += ' style="margin-top:0px"';
        }

        menu.append(htmlCodeMainDiv + '>{$MGLANG->T('addonCA', 'loggedasclient')}<br><a href="index.php?m=ResellersCenter&mg-page=clients&mg-action=returnToRc&json=1" class="alert-link">{$MGLANG->T('addonCA','returntorc')}</a></div>');
    });
</script>

