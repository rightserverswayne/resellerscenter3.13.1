{literal}
var RC_Statistics_Sales = 
{
    init: function()
    {
        this.resellerSelectHandler();
        this.dateTimePickerHanlder();

        //Set handlers
        $("#startDate input").on("change", function(){
            RC_Statistics_Sales.drawSalesGraph();
        });
        $("#endDate input").on("change", function(){
            RC_Statistics_Sales.drawSalesGraph();
        });
        $("#sales [name='resellers']").on("change", function(){
            RC_Statistics_Sales.drawSalesGraph();
        });
        
        //Draw on init
        RC_Statistics_Sales.drawSalesGraph();
    },
    
    resellerSelectHandler: function()
    {
        $("#sales [name='resellers']").select2({
            ajax: 
            {
                url: 'addonmodules.php?module=ResellersCenter&mg-page=statistics&mg-action=getResellers&json=1',
                processResults: function (data) {
                    var result = JSONParser.getJSON(data);
                    var groups = {};
                    $.each(result.data, function(index, value)
                    {
                        if(typeof groups[value.groupname] == 'undefined') {
                            groups[value.groupname] = {id: 'group', text: value.groupname, children: []};
                        }
                        
                        var text = '#' + value.id + ' ' + value.name;
                        groups[value.groupname].children.push({id: value.id, text: text});
                    });
                    
                    result = [];
                    $.each(groups, function(index, value) {
                        result.push(value);
                    });

                    return {results: result};
                },
                delay: 250
            },
            placeholder: "{/literal}{$MGLANG->T('form','select','placeholder')}{literal}",
        });
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
        if(mm < 1) { mm = 12; yyyy -= 1; }
        $('#startDate input').val(yyyy+'-'+mm+'-'+dd);
        
        $('#startDate').datetimepicker({
            format: "YYYY-MM-DD",
        });
        
        $('#endDate').datetimepicker({
            format: "YYYY-MM-DD",
            useCurrent: false //Important! See issue #1075
        });
        
        $("#startDate").on("dp.change", function (e) {
            RC_Statistics_Sales.drawSalesGraph();
            $('#endDate').data("DateTimePicker").minDate(e.date);
        });
        
        $("#endDate").on("dp.change", function (e) {
            RC_Statistics_Sales.drawSalesGraph();
            $('#startDate').data("DateTimePicker").maxDate(e.date);
        });
    },
    
    drawSalesGraph: function() 
    {
        var resellers = $("#sales [name='resellers']").val();
        var begin = $("#startDate input").val();
        var end = $("#endDate input").val();
        
        JSONParser.request("getSales", {resellers: resellers, startDate: begin, endDate: end}, function(result) 
        {
            var data = {labels: [], datasets: []};
            
            data.labels = result.labels;
            $.each(result.data, function(index, values){
                var dataset = {};
                dataset.data = values;
                dataset.label = index;
                
                dataset.backgroundColor = RC_Statistics_Sales.getColor(index, 0.1),
                dataset.borderColor = RC_Statistics_Sales.getColor(index, 1),
                
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
                }
            });
        });
    },
    
    getColor: function(seed, alpha) 
    {
        seed = seed.match(/\d+/)[0];
        var color = 'rgba(';
        for (var i = 0; i < 3; i++ ) 
        {
            var x = Math.sin(seed++);
            color += Math.floor(x * 255)+",";
        }

        return color + alpha + ")";
    }
}
RC_Statistics_Sales.init();
{/literal}