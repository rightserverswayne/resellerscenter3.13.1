{literal}
var ResellersCenter_StatisticsSales = 
{
    init: function()
    {
        this.dateTimePickerHandler();

        //Set handlers
        $("#startDate input").on("change", function(){
            ResellersCenter_StatisticsSales.drawSalesGraph();
        });
        $("#endDate input").on("change", function(){
            ResellersCenter_StatisticsSales.drawSalesGraph();
        });
        $("#sales [name='resellers']").on("change", function(){
            ResellersCenter_StatisticsSales.drawSalesGraph();
        });
        
        //Draw on init
        ResellersCenter_StatisticsSales.drawSalesGraph();
    },
    
    dateTimePickerHandler: function()
    {
        //Set start dates
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();
        
        $('#startDate').datetimepicker({
            format: "YYYY-MM-DD",
        });
        
        $('#endDate').datetimepicker({
            format: "YYYY-MM-DD",
            useCurrent: false //Important! See issue #1075
        });
       
        $("#startDate").on("dp.change", function (e) {
            ResellersCenter_StatisticsSales.drawSalesGraph();
            $('#endDate').data("DateTimePicker").minDate(e.date);
        });
               
        $("#endDate").on("dp.change", function (e) {
            ResellersCenter_StatisticsSales.drawSalesGraph();
            $('#startDate').data("DateTimePicker").maxDate(e.date);
        });
        
        $('#endDate input').val(yyyy+'-'+mm+'-'+dd);
        
        mm -= 1;
        if(mm < 1) { mm = 12; yyyy -= 1; }
        $('#startDate input').val(yyyy+'-'+mm+'-'+dd);
    },
    
    drawSalesGraph: function() 
    {
        var begin = $("#startDate input").val();
        var end = $("#endDate input").val();
        
        JSONParser.request("getSales", {startDate: begin, endDate: end}, function(result) 
        {
            var data = {labels: [], datasets: []};
            
            data.labels = result.labels;
            $.each(result.data, function(index, values){
                for(index in values)
                {
                    values[index] = Math.round(values[index] * 100) / 100
                }
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
    
}
ResellersCenter_StatisticsSales.init();
{/literal}