{literal}
var RC_Statistics_Clients = 
{
    table: null,
    
    init: function()
    {
        RC_Statistics_Clients.loadClientStatisticTable();
        
        JSONParser.request("getClients", {null: null}, function(result) 
        {
            RC_Statistics_Clients.setChartData(result);
        });
    },
    
    setChartData: function(values) 
    {
        var data = [{labels: [], datasets: [{data: []}]}];
        data.labels = values.labels;

        var dataset = {};
        dataset.label = "{/literal}{$MGLANG->T('legend','clients')}{literal}";

        dataset.backgroundColor = [];
        dataset.borderColor = [];
        $.each(values.labels, function(index, label){
            dataset.backgroundColor.push(RC_Statistics_Clients.getColor(label, 0.1));
            dataset.borderColor.push(RC_Statistics_Clients.getColor(label, 1));
            dataset.borderWidth = 1;
        });

        dataset.data = values.data;
        data.datasets = [dataset];

        var container = $("#clients-chart-bar"); 
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
    
    loadClientStatisticTable: function()
    {
        this.table = $("#clientsChart table").DataTable({
            bProcessing: true,
            bServerSide: true,
            searching: true,
            sAjaxSource: "addonmodules.php?module=ResellersCenter&&mg-page=statistics&mg-action=getClientsTable&datatable=1&json=1",
            fnDrawCallback: function(){
            },
            columns: [
                { data: "reseller",   orderable: true, sortable: false, targets: 0 },
                { data: "clients",    orderable: true, sortable: false, targets: 0 },
              ],
            bPaginate: true,
            sDom: 't',
        });
    },
    
    getColor: function(seed, alpha) 
    {
        if(typeof seed == 'string'){
            seed = seed.match(/\d+/)[0];
        }
        
        var color = 'rgba(';
        for (var i = 0; i < 3; i++ ) 
        {
            var x = Math.sin(seed++);
            color += Math.floor(x * 255)+",";
        }

        return color + alpha + ")";
    }
}
RC_Statistics_Clients.init();
{/literal}