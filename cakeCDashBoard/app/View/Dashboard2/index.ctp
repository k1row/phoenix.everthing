<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR', 'jquery.ui.ympicker', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


var createImpressionGrid = function (campaign_name) {
		jQuery("#impression").jqGrid({
			url:'getGridData/' + encodeURI (campaign_name),
			datatype: 'xml',
			colNames:['Creative',
								'Impression', '', '', '',
								'Click', '', '', '',
								'Install', '', '', '',
								'Relevancy', '', '', '',
								'CTR', '', '', '',
								'CVR', '', '', ''],
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10}, frozen:true },
								 {index:'impression_4weeks_ago', name:'impression_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'impression_3weeks_ago', name:'impression_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'impression_2weeks_ago', name:'impression_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'impression_1weeks_ago', name:'impression_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },

								 {index:'click_4weeks_ago', name:'click_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'click_3weeks_ago', name:'click_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'click_2weeks_ago', name:'click_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'click_1weeks_ago', name:'click_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },

								 {index:'install_4weeks_ago', name:'install_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'install_3weeks_ago', name:'install_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'install_2weeks_ago', name:'install_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'install_1weeks_ago', name:'install_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },

								 {index:'relevancy_4weeks_ago', name:'relevancy_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'relevancy_3weeks_ago', name:'relevancy_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'relevancy_2weeks_ago', name:'relevancy_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'relevancy_1weeks_ago', name:'relevancy_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },

								 {index:'ctr_4weeks_ago', name:'ctr_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'ctr_3weeks_ago', name:'ctr_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'ctr_2weeks_ago', name:'ctr_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'ctr_1weeks_ago', name:'ctr_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },

								 {index:'cvr_4weeks_ago', name:'cvr_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'cvr_3weeks_ago', name:'cvr_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'cvr_2weeks_ago', name:'cvr_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 {index:'cvr_1weeks_ago', name:'cvr_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
								 ],
			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			rowNum:100,
			multiselect: false,

			height: 'auto',
			//width: 'auto',
			width:800,
			shrinkToFit:false,
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
			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10}, formatter:function breakLine(cellvalue, options, rowObject) { return cellvalue.replace(/\s\s/g,'<br />'); } },
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
		$("#impression").jqGrid('setFrozenColumns');
		/*
		createClickGrid (campaign_name);
		createInstallGrid (campaign_name);
		createRelevancyGrid (campaign_name);
		createCtrGrid (campaign_name);
		createCvrGrid (campaign_name);
		*/
};

var unloadGrids = function () {
		$("#impression").GridUnload ();
		/*
		$("#click").GridUnload ();
		$("#install").GridUnload ();
		$("#relevancy").GridUnload ();
		$("#ctr").GridUnload ();
		$("#cvr").GridUnload ();
		*/
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

<table id="impression"></table>
