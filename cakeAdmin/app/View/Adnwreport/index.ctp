<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR', 'jquery.ui.ympicker', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


var createSubGrid = function (pid, appsigid) {
		jQuery("#sublist").jqGrid({
			url:'jqgridSub/' + pid + "/" + appsigid,
			datatype: 'xml',
			colNames:['TargetDate', 'ClickNum', 'InstallNum', 'CVR'],
			colModel:[ {index:'TargetDate', name:'targetdate', width: '200', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'ClickNum', name:'clicknum', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'InstallNum', name:'installnum', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'CVR', name:'cpi', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 ],
			rowNum:70,
			multiselect: false,
			loadComplete : function () {
			},

				// Event of selected row
			onSelectRow: function(id) {
					if (id)
					{
					}
			},
			width: 'auto',
			height: 'auto',
			shrinkToFit:false,
			rowList:[10,20,30],
			viewrecords: true,
			caption: 'Data of the last two months'
		});
};

var createGrid = function (pid) {
		jQuery("#list").jqGrid({
			url:'jqgrid/' + pid,
			datatype: 'xml',
			colNames:['appsigid', 'appname', 'Today', 'Yesterday', '2days-ago', '3days-ago', '4days-ago', '5days-ago', '6days-ago', '7days-ago', '8days-ago', '9days-ago', '10days-ago', '11days-ago', '12days-ago', '13days-ago'],
			colModel:[ {index:'appsigid', name:'appsigid', width: '250', align: 'left', editable:false, editoptions:{size:10, readonly:'readonly'}, hidden:false },
								 {index:'appname', name:'appname', width: '200', align: 'center', editable:false, editoptions:{size:10} },
								 {index:'Today', name:'Today', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'Yesterday', name:'Yesterday', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'2days-ago', name:'2days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'3days-ago', name:'3days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'4days-ago', name:'4days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'5days-ago', name:'5days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'6days-ago', name:'6days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'7days-ago', name:'7days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'8days-ago', name:'7days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'9days-ago', name:'7days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'10days-ago', name:'7days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'11days-ago', name:'7days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'12days-ago', name:'7days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
								 {index:'13days-ago', name:'7days-ago', width: '80', align: 'right', editable:false, editoptions:{size:20} },
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
									$("#sublist").GridUnload ();
									createSubGrid (pid, row.appsigid);
							}
					}
			},
			width: 'auto',
			height: 'auto',
			shrinkToFit:false,
			rowList:[10,20,30],
			viewrecords: true,
			caption: 'Past 13 days install numbers'
		});
};


var getPublisherDetail = function (pid) {
		$.get('getPublisherDetail/' + pid, function(data) {
				$("#list").GridUnload ();
				createGrid (pid);
		});
}


// Afeter changed publishers combobox
$(function() {
		$("#publishers").change(function() {
				getPublisherDetail ($(this).val());
		});
});


jQuery(document).ready(function () {

		$("#jMenu").jMenu();
});


<?php $this->Html->scriptEnd(); ?>

<div>
	<select id="publishers">
		<?php foreach ($publisher_datas as $data): ?>
		  <option value="<?php echo $data['PublisherMaster']['id']; ?>">[<?php echo $data['PublisherMaster']['id']; ?>]&nbsp;&nbsp;<?php echo $data['PublisherMaster']['owner_name']; ?></option>
		<?php endforeach; ?>
	</select>
</div>
<br />

<table id="list"></table> 
<div id="pager" style="text-align:center;"></div>

<div style="margin:30px;"></div>

<table id="sublist" class="scroll" cellpadding="0" cellspacing="0">
</table>
<div id="sublistpager" style="text-align:center;"></div>
