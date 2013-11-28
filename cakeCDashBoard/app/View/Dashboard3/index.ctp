<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR', 'jquery.jqGrid-4.5.2/js/i18n/grid.locale-en', 'jquery.ui.ympicker', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


var createGrid = function (datas) {
		jQuery("#list").jqGrid({
			data: datas,
			datatype: "local",
			colNames:['Creative',
								'Impression', '', '', '',
								'Click', '', '', '',
								'Install', '', '', '',
								'Relevancy', '', '', '',
								'CTR(%)', '', '', '',
								'CVR(%)', '', '', ''],

			colModel:[ {index:'creative', name:'creative', width: '160', align: 'center', editable:false, editoptions:{size:10}, frozen:true, sorttype:'text' },
								 {index:'impression_4weeks_ago', name:'impression_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'impression_3weeks_ago', name:'impression_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'impression_2weeks_ago', name:'impression_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'impression_1weeks_ago', name:'impression_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },

								 {index:'click_4weeks_ago', name:'click_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'click_3weeks_ago', name:'click_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'click_2weeks_ago', name:'click_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'click_1weeks_ago', name:'click_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },

								 {index:'install_4weeks_ago', name:'install_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'install_3weeks_ago', name:'install_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'install_2weeks_ago', name:'install_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },
								 {index:'install_1weeks_ago', name:'install_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'int' },

								 {index:'relevancy_4weeks_ago', name:'relevancy_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'relevancy_3weeks_ago', name:'relevancy_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'relevancy_2weeks_ago', name:'relevancy_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'relevancy_1weeks_ago', name:'relevancy_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },

								 {index:'ctr_4weeks_ago', name:'ctr_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'ctr_3weeks_ago', name:'ctr_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'ctr_2weeks_ago', name:'ctr_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'ctr_1weeks_ago', name:'ctr_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },

								 {index:'cvr_4weeks_ago', name:'cvr_4weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'cvr_3weeks_ago', name:'cvr_3weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'cvr_2weeks_ago', name:'cvr_2weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 {index:'cvr_1weeks_ago', name:'cvr_1weeks_ago', width: '100', align: 'center', editable:false, editoptions:{size:20}, sorttype:'float' },
								 ],

			loadComplete : function () {
			},

			ondblClickRow: function (id) {
					if(id == 0) { return; }
			},

			height: 'auto',
				//width: 'auto',
			width:1200,
			shrinkToFit:false,

			sortname: 'creative',
			sortorder: "asc",

			rowNum:30,
			rowTotal: 2000,
			rowList : [30,50,100],
			viewrecords: true,
			gridview: true,
			pager: '#pagertb',
			multiselect: false,
			caption: 'Creative Datas'
		});
};

var makeGridData = function (datas) {
		var externals = new Array();
		$.each (datas, function (index, value) {
				if (index == 'header') {
						return true;
				}

				externals.push ({creative:value.creative,
					impression_4weeks_ago:value.impression_4weeks_ago,
					impression_3weeks_ago:value.impression_3weeks_ago,
					impression_2weeks_ago:value.impression_2weeks_ago,
					impression_1weeks_ago:value.impression_1weeks_ago,
					click_4weeks_ago:value.click_4weeks_ago,
					click_3weeks_ago:value.click_3weeks_ago,
					click_2weeks_ago:value.click_2weeks_ago,
					click_1weeks_ago:value.click_1weeks_ago,
					install_4weeks_ago:value.install_4weeks_ago,
					install_3weeks_ago:value.install_3weeks_ago,
					install_2weeks_ago:value.install_2weeks_ago,
					install_1weeks_ago:value.install_1weeks_ago,
					relevancy_4weeks_ago:value.relevancy_4weeks_ago,
					relevancy_3weeks_ago:value.relevancy_3weeks_ago,
					relevancy_2weeks_ago:value.relevancy_2weeks_ago,
					relevancy_1weeks_ago:value.relevancy_1weeks_ago,
					ctr_4weeks_ago:value.ctr_4weeks_ago,
					ctr_3weeks_ago:value.ctr_3weeks_ago,
					ctr_2weeks_ago:value.ctr_2weeks_ago,
					ctr_1weeks_ago:value.ctr_1weeks_ago,
					cvr_4weeks_ago:value.cvr_4weeks_ago,
					cvr_3weeks_ago:value.cvr_3weeks_ago,
					cvr_2weeks_ago:value.cvr_2weeks_ago,
					cvr_1weeks_ago:value.cvr_1weeks_ago
				});
		});

		return externals;
};

var makeTrueHeader = function (datas) {
		$.each (datas, function (index, value) {
				if (index == 'header') {
						$("#list").jqGrid('setLabel', "impression_4weeks_ago", value.impression_4weeks_ago);
						$("#list").jqGrid('setLabel', "impression_3weeks_ago", value.impression_3weeks_ago);
						$("#list").jqGrid('setLabel', "impression_2weeks_ago", value.impression_2weeks_ago);
						$("#list").jqGrid('setLabel', "impression_1weeks_ago", value.impression_1weeks_ago);
						$("#list").jqGrid('setLabel', "click_4weeks_ago", value.click_4weeks_ago);
						$("#list").jqGrid('setLabel', "click_3weeks_ago", value.click_3weeks_ago);
						$("#list").jqGrid('setLabel', "click_2weeks_ago", value.click_2weeks_ago);
						$("#list").jqGrid('setLabel', "click_1weeks_ago", value.click_1weeks_ago);
						$("#list").jqGrid('setLabel', "install_4weeks_ago", value.install_4weeks_ago);
						$("#list").jqGrid('setLabel', "install_3weeks_ago", value.install_3weeks_ago);
						$("#list").jqGrid('setLabel', "install_2weeks_ago", value.install_2weeks_ago);
						$("#list").jqGrid('setLabel', "install_1weeks_ago", value.install_1weeks_ago);
						$("#list").jqGrid('setLabel', "relevancy_4weeks_ago", value.relevancy_4weeks_ago);
						$("#list").jqGrid('setLabel', "relevancy_3weeks_ago", value.relevancy_3weeks_ago);
						$("#list").jqGrid('setLabel', "relevancy_2weeks_ago", value.relevancy_2weeks_ago);
						$("#list").jqGrid('setLabel', "relevancy_1weeks_ago", value.relevancy_1weeks_ago);
						$("#list").jqGrid('setLabel', "ctr_4weeks_ago", value.ctr_4weeks_ago);
						$("#list").jqGrid('setLabel', "ctr_3weeks_ago", value.ctr_3weeks_ago);
						$("#list").jqGrid('setLabel', "ctr_2weeks_ago", value.ctr_2weeks_ago);
						$("#list").jqGrid('setLabel', "ctr_1weeks_ago", value.ctr_1weeks_ago);
						$("#list").jqGrid('setLabel', "cvr_4weeks_ago", value.cvr_4weeks_ago);
						$("#list").jqGrid('setLabel', "cvr_3weeks_ago", value.cvr_3weeks_ago);
						$("#list").jqGrid('setLabel', "cvr_2weeks_ago", value.cvr_2weeks_ago);
						$("#list").jqGrid('setLabel', "cvr_1weeks_ago", value.cvr_1weeks_ago);

						$("#list").jqGrid ('setGroupHeaders', {
							useColSpanStyle: true,
							groupHeaders:[
									{startColumnName: 'creative', numberOfColumns: 1, titleText: '<em> </em>'},
									{startColumnName: 'impression_4weeks_ago', numberOfColumns: 4, titleText: '<em>Impression</em>'},
									{startColumnName: 'click_4weeks_ago', numberOfColumns: 4, titleText: '<em>Click</em>'},
									{startColumnName: 'install_4weeks_ago', numberOfColumns: 4, titleText: '<em>Install</em>'},
									{startColumnName: 'relevancy_4weeks_ago', numberOfColumns: 4, titleText: '<em>Relevancy</em>'},
									{startColumnName: 'ctr_4weeks_ago', numberOfColumns: 4, titleText: '<em>CTR</em>'},
									{startColumnName: 'cvr_4weeks_ago', numberOfColumns: 4, titleText: '<em>CVR</em>'},
									]
						});

						$("#list").jqGrid ('setFrozenColumns');
						return false;
				}
				return true;
		});
};

var getCampaignDetail = function (campaign_name, network_name) {
		var _sync = function() {
				var json = $.ajax({
					url: 'getGridData/' + encodeURI (campaign_name) + '/' + encodeURI (network_name),
					async: false,
					type: 'GET',
					dataType: 'json'
				});
				if (json.responseText == '') return true;
				var response = $.parseJSON (json.responseText);

				var gridData = makeGridData (response);

				$("#list").GridUnload ();
				createGrid (gridData);

				makeTrueHeader (response);

				$("#list").jqGrid ('navGrid','#pagertb',{del:false, add:false, edit:false, search:true});

				if (gridData.length == 0) {
						$('#modal').dialog('open');
				}
		};
		_sync ();
};


$(function() {
		$("#networks").change(function() {
				$.get('getEachNetworksCampaign/' + $(this).val(), function(data) {
						$("#list").GridUnload ();
						$("#campaigns").html(data);
				});
		});
});

$(function() {
		$("#campaigns").change(function() {
				getCampaignDetail ($(this).val(), $("#networks").val());
		});
});

jQuery(document).ready(function () {

		$('#modal').dialog({
			autoOpen: false,
			modal: true,
			resizable: false,
			draggable: false,
			show: "clip",
			hide: "fade"
		});

		$("#jMenu").jMenu();

		$.get('getEachNetworksCampaign/' + $(this).val(), function(data) {
				$("#campaigns").html(data);
		});
});

<?php $this->Html->scriptEnd(); ?>

<div>
	<select id="networks">
	  <option value="0">--- SELECT NETWORKS ---</option>
	  <option value="MM">MM</option>
		<option value="CB">CB</option>
	</select>
</div>
<br />

<div>
	<select id="campaigns">
	</select>
</div>
<br />

<div>
	<table id="list">
	</table>
	<div id="pagertb"></div>
</div>


<div id="modal" title="CreativeDashboard">
  <p>Nothing data in these term</p>
</div>
