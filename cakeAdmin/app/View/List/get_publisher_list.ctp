<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR', 'jquery.ui.ympicker', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


var createGrid = function () {
		jQuery("#list").jqGrid({
			url:'publisherGrid/',
			datatype: 'xml',
			colNames:['id', 'owner_name', 'owner_email_address', 'callback_url1', 'callback_url2', 'enable'],
			colModel:[ {index:'id', name:'id', width: '100', align: 'right', editable:false, editoptions:{size:10, readonly:'readonly'}, hidden:false },
								 {index:'owner_name', name:'owner_name', width: '150', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'owner_email_address', name:'owner_email_address', width: '250', editable:false, editoptions:{size:20} },
								 {index:'callback_url1', name:'callback_url1', width: '350', editable:false, editoptions:{size:20} },
								 {index:'callback_url2', name:'callback_url2', width: '350', editable:false, editoptions:{size:20} },
								 {index:'enable', name:'enable', width: '80', editable:false, editoptions:{size:20} },
								 ],
			rowNum:50,
			multiselect: false,
			loadComplete : function () {
			},

				// Event of selected row
			onSelectRow: function(id) {
					if (id)
					{
							var row = $("#list").jqGrid('getRowData', id);
							if (row)
							{
							}
					}
			},
			afterInsertRow: function(rowid, rowData, rowelem) {
					var enable = rowData['enable'];
					if (enable == "Active")
					{
							$(this).jqGrid ('setRowData', rowid, false, { color: '#0000FF' });
					}
					else if (enable == "Non-Active")
					{
							$(this).jqGrid ('setRowData', rowid, false, { color: '#FF0000' });
					}
			},
			width: 'auto',
			height: 'auto',
			shrinkToFit:false,
			rowList:[10,20,30],
			viewrecords: true,
			caption: 'Publisher List'
		});
};


jQuery(document).ready(function () {

		$("#jMenu").jMenu();
		createGrid ();
});


<?php $this->Html->scriptEnd(); ?>

<div align="center">
	<table id="list"></table> 
	<div id="pager" style="text-align:center;"></div>
</div>
<br />

