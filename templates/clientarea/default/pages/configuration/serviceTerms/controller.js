{literal}
var ResellersCenter_ServiceTerms =
    {
        maxReminderLength: 5,

        init: function()
        {
            this.addReminderHandler();
            this.removeReminderHandler();
        },

        addReminderHandler: function()
        {
            $("i.addReminder").on("click", ()=>{
                const cloned = $(".inputsForClone").clone();
                const remindersGroup = $("#remindersGroup");
                const reminders = $("#remindersGroup > div");
                const noRemindersMessage = $("span.noRemindersMessage");

                if (typeof remindersGroup == "undefined") {
                    return;
                }

                if (reminders.length < this.maxReminderLength) {
                    cloned.removeClass("inputsForClone");
                    cloned.find("select").prop( "disabled", false );
                    cloned.find("input").prop( "disabled", false );

                    remindersGroup.append(cloned[0]);
                    if ($("#remindersGroup > div").length > this.maxReminderLength - 1) {
                        $("i.addReminder").css('color', 'grey');
                    }
                    noRemindersMessage.hide();
                    this.removeReminderHandler();
                }
            });
        },

        removeReminderHandler: function()
        {
            $("i.removeReminder").on("click", function(event){
                const noRemindersMessage = $("span.noRemindersMessage");

                event.target.parentNode.parentNode.remove();
                $("i.addReminder").css('color', 'darkgreen');

                if ($("#remindersGroup > div").length === 0) {
                    noRemindersMessage.show();
                }
            });
        },
}
ResellersCenter_ServiceTerms.init();
{/literal}
