{literal}
var ResellersCenter_TicketReply = 
{
    init: function()
    {
        this.addMoreAttachmentsHandler();
        this.updateTicketStatus();

        // Twenty-One Template Navbar Case
        RC_TwentyOne_Helper.twentyOneLiSelector();
        
        new SimpleMDE({ 
            element: document.getElementById("replyeditor"),
            toolbar: ['bold', 'italic', 'heading', 'quote', "|", 'ordered-list', 'unordered-list', 'link',  "|",  'preview', 'side-by-side', 'fullscreen',  "|", 'guide']
        });
    },
    
    addMoreAttachmentsHandler: function()
    {
        $(".addAtachement").on("click", function(e){
           e.preventDefault(); 
            var lastAttachment = $(".ticketReply [name='attachments[]']").last();
            lastAttachment.after('<input type="file" name="attachments[]"/>');
        });
    },
    
    updateTicketStatus: function()
    {
        $(".ticket-status").on("change", function()
        {
            var status = $(this).val();
            var ticketid = $(this).data("ticketid");
            JSONParser.request("changeStatus", {ticketid: ticketid, status: status}, function(){});
        });
    }
    
}
ResellersCenter_TicketReply.init();
{/literal}