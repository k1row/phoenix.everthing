<?php $this->Html->script(array('jquery-1.8.3.min', 'jquery-ui.min', 'grid/pqgrid.min'), array('inline'=>false)); ?>
<?php echo $this->Html->css(array('pqgrid.min'), false, array('inline'=>false)); ?>


<?php $this->Html->scriptStart(array('inline'=>false)); ?>

$(function() {
	$("#sort").change(function() {
    alert('change');
		$.get('get_option/' + $(this).val(), function(data) {
			$("#ingredient").html(data);
		});
	});
});


$(function () {
    function changeToTable(that) {
        var tbl = $("table.fortune500");
        tbl.css("display", "");
        $("#grid_table").pqGrid("destroy");
        $(that).val("Change Table to Grid");
    }
    function changeToGrid(that) {
        var tbl = $("table.fortune500");
        var obj = $.adapter.tableToArray(tbl);
        var newObj = { width: 700, height: 400 };
        newObj.dataModel = { data: obj.data };
        newObj.colModel = obj.colModel;
        $("#grid_table").pqGrid(newObj);
        $(that).val("Change Grid back to Table");
        tbl.css("display", "none");
    }
    $("#pq-grid-table-btn").button().toggle(function () {
        changeToGrid(this);
    },
    function () {
        changeToTable(this);
    });
});


$(function(){
    var data = [ [1,'Exxon Mobil','339,938.0','36,130.0'],
            [2,'Wal-Mart Stores','315,654.0','11,231.0'],
			[3,'Royal Dutch Shell','306,731.0','25,311.0'],
			[4,'BP','267,600.0','22,341.0'],
			[5,'General Motors','192,604.0','-10,567.0'],
			[6,'Chevron','189,481.0','14,099.0'],
			[7,'DaimlerChrysler','186,106.3','3,536.3'],
			[8,'Toyota Motor','185,805.0','12,119.6'],
			[9,'Ford Motor','177,210.0','2,024.0'],
			[10,'ConocoPhillips','166,683.0','13,529.0'],
			[11,'General Electric','157,153.0','16,353.0'],			
			[12,'Total','152,360.7','15,250.0'],				
			[13,'ING Group','138,235.3','8,958.9'],
			[14,'Citigroup','131,045.0','24,589.0'],
			[15,'AXA','129,839.2','5,186.5'],
			[16,'Allianz','121,406.0','5,442.4'],
			[17,'Volkswagen','118,376.6','1,391.7'],
			[18,'Fortis','112,351.4','4,896.3'],
			[19,'Credit Agricole','110,764.6','7,434.3'],
			[20,'American Intl. Group','108,905.0','10,477.0']];
            
    var obj = {};
    obj.width = 700;
    obj.height = 400;
    obj.colModel = [{title:"Rank", width:100, dataType:"integer"},
        {title:"Company", width:200, dataType:"string"},
        {title:"Revenues ($ millions)", width:150, dataType:"float", align:"right"},
        {title:"Profits ($ millions)", width:150, dataType:"float", align:"right"}];
    obj.dataModel = {data:data};
    $("#grid_array").pqGrid( obj );



 $(function () {
        var colM = [
        { title: "ShipCountry", width: 100 },
        { title: "Customer Name", width: 100 },
        { title: "Order ID", width: 100 },
		];
        var dataModel = {
            location: "remote",
            sorting: "remote",
            paging: "remote",
            dataType: "JSON",
            method: "GET",
            curPage: 1,
            rPP: 20,
            sortIndx: 2,
            sortDir: "up",
            rPPOptions: [1, 10, 20, 30, 40, 50, 100, 500, 1000],
            getUrl: function () {
                var sortDir = (this.sortDir == "up") ? "asc" : "desc";
                var sort = ['ShipCountry', 'contactName', 'orderID'];
                return { url: "/pagingGetOrders", data: "cur_page=" + this.curPage + "&records_per_page=" + 
                    this.rPP + "&sortBy=" + sort[this.sortIndx] + "&dir=" + sortDir };
            },
            getData: function (dataJSON) {
                //var data=                
                return { curPage: dataJSON.curPage, totalRecords: dataJSON.totalRecords, data: dataJSON.data };
            }
        }

        var grid1 = $("div#grid_paging").pqGrid({ width: 900, height: 400,
            dataModel: dataModel,
            colModel: colM,
            title:"Shipping Orders",
            resizable: true,            
            columnBorders: true,
            freezeCols: 2
        });
    });
});

<?php $this->Html->scriptEnd(); ?>


<!--
<select id="sort">
	<option value="meat">Meat</option>
	<option value="green">Green</option>
	<option value="fish">Fish</option>
</select>

<select id="ingredient">
	<option value="sirloin">sirloin</option>
	<option value="rib">rib</option>
	<option value="tongue">tongue</option>
</select>
-->


<!--
<div id="grid_array"></div>
-->


<div id="grid_paging"></div>

