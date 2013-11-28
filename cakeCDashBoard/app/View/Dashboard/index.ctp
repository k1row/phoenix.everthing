<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR', 'jquery.ui.ympicker', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


var createImpressionGrid = function (campaign_name) {
		jQuery("#impression").jqGrid({
			url:'getImpressionGridData/' + encodeURI (campaign_name),
			datatype: 'xml',
			colNames:['Creative', '', '', '', ''],
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'4weeks-ago', name:'4weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'3weeks-ago', name:'3weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'2weeks-ago', name:'2weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'1weeks-ago', name:'1weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 ],
			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			onSortCol: function (index, colindex, sortorde) {
					/*
					$.get("getClickGridData/" encodeURI (campaign_name) + , { : "John", time: "2pm" },
								function(data){
										alert("Data Loaded: " + data);
								});
								*/
			},

			rowNum:100,
			multiselect: false,
			width: 'auto',
			height: 'auto',
			sortname: 'creative',
			sortorder: "asc",
			caption: 'Impression'
		});
};

var createClickGrid = function (campaign_name) {
		jQuery("#click").jqGrid({
			url:'getClickGridData/' + encodeURI (campaign_name),
			datatype: 'xml',
			colNames:['Creative', '', '', '', ''],
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'4weeks-ago', name:'4weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'3weeks-ago', name:'3weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'2weeks-ago', name:'2weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'1weeks-ago', name:'1weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 ],
			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			rowNum:100,
			multiselect: false,
			width: 'auto',
			height: 'auto',
			sortname: 'creative',
			sortorder: "asc",
			caption: 'Click'
		});
};


var createInstallGrid = function (campaign_name) {
		jQuery("#install").jqGrid({
			url:'getInstallGridData/' + encodeURI (campaign_name),
			datatype: 'xml',
			colNames:['Creative', '', '', '', ''],
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'4weeks-ago', name:'4weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'3weeks-ago', name:'3weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'2weeks-ago', name:'2weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'1weeks-ago', name:'1weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 ],
			rowNum:100,
			multiselect: false,
			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			width: 'auto',
			height: 'auto',
			sortname: 'creative',
			sortorder: "asc",
			caption: 'Install'
		});
};


var createRelevancyGrid = function (campaign_name) {
		jQuery("#relevancy").jqGrid({
			url:'getRelevancyGridData/' + encodeURI (campaign_name),
			datatype: 'xml',
			colNames:['Creative', '', '', '', ''],
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'4weeks-ago', name:'4weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'3weeks-ago', name:'3weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'2weeks-ago', name:'2weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'1weeks-ago', name:'1weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 ],
			rowNum:100,
			multiselect: false,
			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			width: 'auto',
			height: 'auto',
			sortname: 'creative',
			sortorder: "asc",
			caption: 'Relevancy'
		});
};


var createCtrGrid = function (campaign_name) {
		jQuery("#ctr").jqGrid({
			url:'getCtrGridData/' + encodeURI (campaign_name),
			datatype: 'xml',
			colNames:['Creative', '', '', '', ''],
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'4weeks-ago', name:'4weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'3weeks-ago', name:'3weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'2weeks-ago', name:'2weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'1weeks-ago', name:'1weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 ],
			rowNum:100,
			multiselect: false,
			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			width: 'auto',
			height: 'auto',
			sortname: 'creative',
			sortorder: "asc",
			caption: 'CTR'
		});
};


var createCvrGrid = function (campaign_name) {
		jQuery("#cvr").jqGrid({
			url:'getCvrGridData/' + encodeURI (campaign_name),
			datatype: 'xml',
			colNames:['Creative', '', '', '', ''],
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'4weeks-ago', name:'4weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'3weeks-ago', name:'3weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'2weeks-ago', name:'2weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'1weeks-ago', name:'1weeks-ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 ],
			rowNum:100,
			multiselect: false,
			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			width: 'auto',
			height: 'auto',
			sortname: 'creative',
			sortorder: "asc",
			caption: 'CVR'
		});
};


var createGrids = function (campaign_name) {
		createImpressionGrid (campaign_name);
		createClickGrid (campaign_name);
		createInstallGrid (campaign_name);
		createRelevancyGrid (campaign_name);
		createCtrGrid (campaign_name);
		createCvrGrid (campaign_name);
};

var unloadGrids = function () {
		$("#impression").GridUnload ();
		$("#click").GridUnload ();
		$("#install").GridUnload ();
		$("#relevancy").GridUnload ();
		$("#ctr").GridUnload ();
		$("#cvr").GridUnload ();
};

var getCampaignDetail = function (campaign_name) {
		unloadGrids ();
		createGrids (campaign_name);
};


// Afeter changed publishers combobox
$(function() {
		$("#campaigns").change(function() {
				getCampaignDetail ($(this).val());
		});
});


jQuery(document).ready(function () {
		$("#jMenu").jMenu();
});

<?php $this->Html->scriptEnd(); ?>

<div>
	<select id="campaigns">
	  <option value="0">--- SELECT CAMPAIGN_NAME ---</option>
		<?php foreach ($campaigns as $campaign): ?>
		  <option value="<?php echo $campaign['CreativeDashboardForDena']['campaign_name']; ?>"><?php echo $campaign['CreativeDashboardForDena']['campaign_name']; ?></option>
		<?php endforeach; ?>
	</select>
</div>
<br />

<table>
 <tr align="top">
   <td><table id="impression"></table></td>
   <td><table id="click"></table></td>
   <td><table id="install"></table></td>
   <td><table id="relevancy"></table></td>
   <td><table id="ctr"></table></td>
   <td><table id="cvr"></table></td>
 </tr>
</table>

<!--
<table>
 <tr align="top">
   <td><table id="impression"></table></td>
   <td><table id="click"></table></td>
   <td><table id="install"></table></td>
 </tr>
 <tr align="top">
   <td><table id="relevancy"></table></td>
   <td><table id="ctr"></table></td>
   <td><table id="cvr"></table></td>
 </tr>
 <tr align="top">
   <td><div id="pager_impression" class="scroll" style="text-align:center;"></td>
	 <td><div id="pager_click" class="scroll" style="text-align:center;"></td>
	 <td><div id="pager_install" class="scroll" style="text-align:center;"></td>
 </tr>
 <tr align="top">
	 <td><div id="pager_relevancy" class="scroll" style="text-align:center;"></td>
	 <td><div id="pager_ctr" class="scroll" style="text-align:center;"></td>
	 <td><div id="pager_cvr" class="scroll" style="text-align:center;"></td>
 </tr>
</table>
-->
