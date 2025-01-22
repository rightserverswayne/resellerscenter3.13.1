//Configuration
customPaginationStartPage = 1; //0 - disable; avalible - 1...


customPaginationTable = ''; //do not change

function checkIfIsNumber(evnt){
    var chCode = (evnt.which) ? evnt.which : evnt.keyCode;
    return !(chCode > 31 && (chCode < 48 || chCode > 57));
}

function RCJumpToPage(){
    var page = jQuery('#RCJumpToPage').val();
    if(page == 0){
        jQuery('#RCJumpToPage').val(1);
        page = 1;
    }
    if(page == 1){
        jQuery('#RCCustomPaginPrev').addClass('disabled');
    }else{
        jQuery('#RCCustomPaginPrev').removeClass('disabled');                
    }
    var dataTable = jQuery('#RCJumpToPage').parents().find('.dataTable').first();
    var dataTableId = dataTable.prop('id');
    var dTableVars = jQuery('#'+dataTableId).DataTable().page.info();
    if(page > dTableVars.pages){
        jQuery('#RCJumpToPage').val(dTableVars.pages);                    
        page = dTableVars.pages;
    }
    if(page == dTableVars.pages){
        jQuery('#RCCustomPaginNext').addClass('disabled');
    }else{
        jQuery('#RCCustomPaginNext').removeClass('disabled');                
    }            
    jQuery('#'+dataTableId).DataTable().page(page-1).ajax.reload(null, false);
    RCReloadCustomPageNumbers(page, dTableVars.pages);
}
        
function RCGoToPrevPage(event){
    event.preventDefault();
    if(!jQuery('#RCCustomPaginPrev').hasClass('disabled')){
        var page = jQuery('#RCJumpToPage').val();
        jQuery('#RCJumpToPage').val(parseInt(page) - 1);
        RCJumpToPage();    
    }
}
function RCGoToNextPage(event){
    if(!jQuery('#RCCustomPaginNext').hasClass('disabled')){            
        event.preventDefault();
        var page = jQuery('#RCJumpToPage').val();
        jQuery('#RCJumpToPage').val(parseInt(page) + 1);
        RCJumpToPage();
    }
}        
function RCReloadCustomPageNumbers(page, maxPage){
    var tempPage = parseInt(page);
    var tempMaxPage = parseInt(maxPage); 
    jQuery('#RCCustomPrevButton2').children().first().html(tempPage-2);
    jQuery('#RCCustomPrevButton1').children().first().html(tempPage-1);
    jQuery('#RCCustomNextButton1').children().first().html(tempPage+1);
    jQuery('#RCCustomNextButton2').children().first().html(tempPage+2);
    if(tempPage == 1){
        jQuery('#RCCustomPrevButton1').addClass('hidden');
        jQuery('#RCCustomPrevButton2').addClass('hidden');                
    }else if(tempPage == 2){  
        jQuery('#RCCustomPrevButton2').addClass('hidden');
        jQuery('#RCCustomPrevButton1').removeClass('hidden');
    }else{
        jQuery('#RCCustomPrevButton1').removeClass('hidden');
        jQuery('#RCCustomPrevButton2').removeClass('hidden');                
    }          
    if(tempPage == tempMaxPage){
        jQuery('#RCCustomNextButton1').addClass('hidden');
        jQuery('#RCCustomNextButton2').addClass('hidden');                
    }else if(tempPage == tempMaxPage -1){
        jQuery('#RCCustomNextButton2').addClass('hidden');
        jQuery('#RCCustomNextButton1').removeClass('hidden');
    }else{
        jQuery('#RCCustomNextButton1').removeClass('hidden');
        jQuery('#RCCustomNextButton2').removeClass('hidden');                   
    }            
}

function RCJumpToButtonPage(item)
{
    var id = jQuery(item).prop('id');
    var curPage = jQuery('#RCJumpToPage').val();
    if(id.indexOf('Next') > -1)
    {
        var pageNr = id.replace('RCCustomNextButton', '');
        jQuery('#RCJumpToPage').val(parseInt(curPage) + parseInt(pageNr));
    }
    if(id.indexOf('Prev') > -1)
    {
        var pageNr = id.replace('RCCustomPrevButton', ''); 
        jQuery('#RCJumpToPage').val(parseInt(curPage) - parseInt(pageNr));
    }     
    
    console.log(id);
    RCJumpToPage();            
}        

function addCustomPagination(tableId, startPage=-1){
    if(startPage == -1){
        startPage = customPaginationStartPage;
    }
    if(startPage == 0){
        return false;
    }
    addPaginationBlock(tableId);
    var dtInfo = jQuery('#'+tableId).DataTable().page.info();
        
    jQuery('#RCJumpToPage').val(dtInfo.page + 1);
    if(dtInfo.pages >= startPage){
        jQuery('#'+tableId+'_paginate').addClass('hidden')
        jQuery('#RCJumpToPageBlock').removeClass('hidden');
        
        RCReloadCustomPageNumbers(dtInfo.page + 1, dtInfo.pages);
    }
    else //Table is empty
    {
        RCReloadCustomPageNumbers(1, 1);
    }
}

function addEventsDeclarations(tableId){
    $(document).delegate('#RCJumpToPage', 'change', function() {
        var page = jQuery('#RCJumpToPage').val();
        if(page == 0){
            jQuery('#RCJumpToPage').val(1);
            page = 1;
        }
        var dTableVars = jQuery('#'+tableId).DataTable().page.info();
        if(page > dTableVars.pages){
            jQuery('#RCJumpToPage').val(dTableVars.pages);                    
            page = dTableVars.pages;
        }
        //jQuery('#'+tableId).DataTable().page(page-1).ajax.reload(null, false); 
        RCJumpToPage();
    });
    
    $('#'+tableId).on( 'length.dt', function(){
        RCJumpToPage();
    });    
}

function addPaginationBlock(tableId){
    if($('#RCJumpToPageBlock').length == 0){
        var jumpToPageContent = '<div class="hidden pull-right" id="RCJumpToPageBlock" style="display:inline-block;">'
            +'<ul class="pagination">'
            +'<li id="RCCustomPaginPrev" class="paginate_button previous disabled"><a href="#" onclick="RCGoToPrevPage(event);">Previous</a></li>'
            +'<li class="paginate_button" id="RCCustomPrevButton2" onclick="RCJumpToButtonPage(this);"> <a href="#"></a></li>'
            +'<li class="paginate_button" id="RCCustomPrevButton1" onclick="RCJumpToButtonPage(this);"> <a href="#"></a></li>'        
            +'<li class="paginate_button"><a style="padding: 0px 0px; height: 100%;"><input id="RCJumpToPage" type="text" class="paginate_button active" maxlength="5" size="4" onkeypress="return checkIfIsNumber(event);" style="height: 30px; text-align: center; border:none; color:#fff; background-color:#337ab7;"></input></a></li>'
            +'<li class="paginate_button" id="RCCustomNextButton1" onclick="RCJumpToButtonPage(this);"> <a href="#"></a></li>'
            +'<li class="paginate_button" id="RCCustomNextButton2" onclick="RCJumpToButtonPage(this);"> <a href="#"></a></li>' 
            +'<li id="RCCustomPaginNext" class="paginate_button next"><a href="#" onclick="RCGoToNextPage(event);">Next</a></li>'
            +'</ul></div>';
            jQuery('#'+tableId+'_paginate').parent().append(jumpToPageContent);   
            addEventsDeclarations(tableId);
    }
}
