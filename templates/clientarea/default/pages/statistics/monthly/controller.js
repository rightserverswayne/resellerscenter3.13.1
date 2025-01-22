{literal}
var ResellersCenter_StatisticsMonthly = 
{
    init: function()
    {
        JSONParser.request("getMonthlyData", {null: null}, function(result) 
        {
            ResellersCenter_StatisticsMonthly.setChartData(result);
        });
    },
    

    setChartData: function(values) 
    {
        var data = {labels: [], datasets: [{data: []}]};
        
        var income = [];
        $.each(values, function(index, value)
        {
            data.labels.push(index);
            income.push(Math.round(value.income * 100) / 100);
        });
        
        data.datasets = [
            {
                label: "{/literal}{$MGLANG->T('legend','income')}{literal}",
                backgroundColor: "rgba(255, 206, 86, 0.2)",
                borderColor: "rgba(255, 206, 86, 1)",
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
                legend: {
                    display: false
                }
            }
        });
    },
}
ResellersCenter_StatisticsMonthly.init();
{/literal}