{literal}
var RC_ResellersStatistics = 
{
    init: function()
    {
        this.dateTimePickerHanlder();

        //Draw on init
        RC_ResellersStatistics.drawSalesGraph();
    },
    
    dateTimePickerHanlder: function()
    {
        //Set start dates
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();
        
        $('#endDate input').val(yyyy+'-'+mm+'-'+dd);

        mm -= 1;
        if(mm < 1) { mm = 12; }
        $('#startDate input').val(yyyy+'-'+mm+'-'+dd);
        
        $('#startDate').datetimepicker({
            format: "YYYY-MM-DD",
        });
        
        $('#endDate').datetimepicker({
            format: "YYYY-MM-DD",
            useCurrent: false //Important! See issue #1075
        });

        //Draw chart on change
        $("#startDate").on("dp.change", function (e) {
            RC_ResellersStatistics.drawSalesGraph();
            $('#endDate').data("DateTimePicker").minDate(e.date);
        });
        
        $("#endDate").on("dp.change", function (e) {
            RC_ResellersStatistics.drawSalesGraph();
            $('#startDate').data("DateTimePicker").maxDate(e.date);
        });
        $("#startDate input").on("change", function(){
            RC_ResellersStatistics.drawSalesGraph();
        });
        $("#endDate input").on("change", function(){
            RC_ResellersStatistics.drawSalesGraph();
        });
    },
    
    drawSalesGraph: function() 
    {
        var resellers = [$("#statistics [name='resellerid']").val()];
        var begin = $("#startDate input").val();
        var end = $("#endDate input").val();
        
        JSONParser.request("getSales|statistics", {resellers: resellers, startDate: begin, endDate: end}, function(result) 
        {
            var data = {labels: [], datasets: []};
            
            data.labels = result.labels;
            $.each(result.data, function(index, values){
                var dataset = {};
                dataset.data = values;
                dataset.label = index;
                
                dataset.backgroundColor = "rgba(143, 244, 66, 0.1)";
                dataset.borderColor = "rgba(143, 244, 66, 1)",
                
                data.datasets.push(dataset);
            });

            $("#sales-chart-line").empty();
            $("#sales-chart-line").html("<canvas height='450'></canvas>"); 
            var container = $("#sales-chart-line canvas"); 
            new Chart(container, {
                type: "line",
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    }
                }
            });
        });
    },

    toggleView: function()
    {
        if($("#statistics .tableView").is(":visible"))
        {
            $("#statistics .rc-actions a i").removeClass("fa-bar-chart-o");
            $("#statistics .rc-actions a i").addClass("fa-table");
            
            $("#statistics .tableView").hide();
            $("#statistics .graphView").show();
        }
        else
        {
            $("#statistics .rc-actions a i").addClass("fa-bar-chart-o");
            $("#statistics .rc-actions a i").removeClass("fa-table");
            
            $("#statistics .tableView").show();
            $("#statistics .graphView").hide();
        }
    }
}
RC_ResellersStatistics.init();
{/literal}