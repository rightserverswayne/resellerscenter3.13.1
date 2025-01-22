{literal}
var RC_Statistics_Monthly = 
{
    init: function()
    {
        $("#monthly .tableView").hide();
        JSONParser.request("getMonthlyData", {null: null}, function(result) 
        {
            RC_Statistics_Monthly.setTableData(result);
            RC_Statistics_Monthly.setChartData(result);
        });
    },
    
    setTableData: function(data)
    {
        $.each(data, function(month, values)
        {
            var row = $('#monthlyTablePrototype tr').clone();
            row.html(function(index, text) 
            {
                text = text.replace(/(\+month\+)/g, month);
                text = text.replace(/(\+totalsale\+)/g, values.totalsale);
                text = text.replace(/(\+resellersincome\+)/g, values.resellersincome);
                text = text.replace(/(\+income\+)/g, values.income);
                return text;
            });

            $("#monthly tbody").append(row);
        });
    },

    setChartData: function(values) 
    {
        var data = {labels: [], datasets: [{data: []}]};
        
        var totalsale = [];
        var resellersincome = [];
        var income = [];
        $.each(values, function(index, value)
        {
            data.labels.push(index);
            totalsale.push(value.totalsale);
            resellersincome.push(Math.round(value.resellersincome * 100) / 100);
            income.push(value.income);
        });
        
        data.datasets = [
            {
                label: "{/literal}{$MGLANG->T('legend','totalsale')}{literal}",
                backgroundColor: "rgba(54, 162, 235, 0.2)",
                borderColor: "rgba(54, 162, 235, 1)",
                borderWidth: 1,
                data: totalsale
            },
            {
                label: "{/literal}{$MGLANG->T('legend','resellersincome')}{literal}",
                backgroundColor: "rgba(255, 206, 86, 0.2)",
                borderColor: "rgba(255, 206, 86, 1)",
                borderWidth: 1,
                data: resellersincome
            },
            {
                label: "{/literal}{$MGLANG->T('legend','income')}{literal}",
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
                borderWidth: 1,
                data: income
            }
        ];
        
        var container = $("#monthly-income-chart canvas"); 
        new Chart(container, {
            type: "bar",
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    },
    
    toggleView: function()
    {
        if($("#monthly .tableView").is(":visible"))
        {
            $("#monthly .rc-actions i").removeClass("fa-bar-chart-o");
            $("#monthly .rc-actions i").addClass("fa-table");
            
            $("#monthly .tableView").hide();
            $("#monthly .graphView").show();
        }
        else
        {
            $("#monthly .rc-actions i").addClass("fa-bar-chart-o");
            $("#monthly .rc-actions i").removeClass("fa-table");
            
            $("#monthly .tableView").show();
            $("#monthly .graphView").hide();
        }
    }
}
RC_Statistics_Monthly.init();
{/literal}