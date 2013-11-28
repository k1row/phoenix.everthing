<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR', 'jquery.ui.ympicker', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


var createSubGrid = function (appsigid, target_date, publisherid) {
		jQuery("#sublist").jqGrid({
			url:'jqgridSub/' + appsigid + "/" + target_date + "/" + publisherid,
			datatype: 'xml',
			colNames:['TargetDateTime','ClickNum','InstallNum','CVR', 'CPI', 'Sales'],
			colModel:[ {index:'target_datetime', name:'target_datetiem', width: '120', editable:false, editoptions:{size:20}, sorttype:'date' },
								 {index:'click_num', name:'click_num', width: '80', align: 'right', editable:false },
								 {index:'install_num', name:'install_num', width: '80', align: 'right', editable:false },
								 {index:'cvr', name:'cvr', width: '80', align: 'right', editable:false },
								 {index:'cpi', name:'cpi', width: '80', align: 'right', editable:false },
								 {index:'sales', name:'sales', width: '80', align: 'right', editable:false },
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
			caption: 'Timeline data'
		});
};


var createGrid = function (appsigid, date, creative_id) {
		jQuery("#list").jqGrid({
			url:'jqgrid/' + appsigid + "/" + date + "/" + creative_id,
			datatype: 'xml',
			colNames:['TargetDate','ClickNum','InstallNum','CVR', 'CPI', 'Sales'],
			colModel:[ {index:'target_date', name:'target_date', width: '120', editable:false, editoptions:{size:20}, sorttype:'date' },
								 {index:'click_num', name:'click_num', width: '80', align: 'right', editable:false },
								 {index:'install_num', name:'install_num', width: '80', align: 'right', editable:false },
								 {index:'cvr', name:'cvr', width: '80', align: 'right', editable:false },
								 {index:'cpi', name:'cpi', width: '80', align: 'right', editable:false },
								 {index:'sales', name:'sales', width: '80', align: 'right', editable:false },
								 ],
			rowNum:50,
			multiselect: true,
			loadComplete : function () {
			    $("#campaigns").val(appsigid)
			},

				// Event of selected row
			onSelectRow: function(id) {
					if (id)
					{
							var row = $("#list").jqGrid('getRowData', id);
							if (row)
							{
									$("#sublist").GridUnload ();
									var pid = $("#textcurrentpublisherid").val();
									//alert (pid);
									createSubGrid (appsigid, row.target_date, pid);
							}
					}
			},
			//scroll: true,
			width: 'auto',
			height: 'auto',
			rowList:[10,20,30],
			viewrecords: true,
			caption: 'All of this application\'s ADNW data'
		});
};


var initTabs = function () {

		var tabOpts = {
			activate: handleTabSelect,
			show: {
				height: 'toggle',
				opacity: 'toggle'
			},
			collapsible: true
		};

		$("#tabs").tabs (tabOpts);
		$("#tabs").tabs ("refresh");

		function handleTabSelect (event, tab) {

				// This is selected appsigid (get_campaign_detail.ctp).
				var appsigid = $('#appsigid-form [name=appsigid]').val();
				var publisher = tab.newPanel.selector.split ("_");

				$.get('getCreativeDetail/' + appsigid + '/' + publisher[1], function(data) {
						$("#creatives").html(data);
						$('#appsigid-form').html(data);

						if (!$('#creatives').is(':visible')) {
								$('#creatives').toggle ();
						}

						if (publisher[1] == 'total' && $('#creatives').is(':visible')) {
								$('#creatives').toggle ();
						}
				});

				new_url = "jqgrid/" + appsigid + "/" + $("#datepicker").val() + "?pid=" + publisher[1];
				//alert (new_url);

				$("#list").jqGrid('setGridParam', { url:new_url, page:1 });
				$("#list").trigger('reloadGrid');

				$("#sublist").GridUnload ();
				$("#textcurrentpublisherid").val(publisher[1]);
		}
};


var initDataPicker = function () {

		$("#datepicker").ympicker ({
			showOn: 'button',
			buttonImage: '/img/datepicker.png',
			buttonImageOnly: true,
			dateFormat: 'yy-mm',
			changeMonth: true,
			changeYear: true,
			yearRange: '2013:2014',
				//minDate: new Date(2013, 6 - 1, 1),
			showMonthAfterYear: false,
				//showOtherMonths: false,
		});

		$("#datepicker").datepicker("option", "defaultDate", thisMonth ());
		$('#datepicker').val(thisMonth ());
}

var thisMonth = function () {
		today = new Date ();
		y = today.getFullYear ();
		m = today.getMonth ()+1;

		if (m < 10) { m = '0' + m; }
		//return m + "/" + y;
		return y + "-" + m;
}


var getUrlVars = function () {
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
				hash = hashes[i].split('=');
				vars.push(hash[0]);
				vars[hash[0]] = hash[1];
		}

		return vars;
};


var getCampaignData = function (advertiserId) {

		$("#list").GridUnload ();

		$.get('getCampaignData/' + advertiserId, function(data) {
				$("#campaigns").html(data);
		});
}

// Afeter changed Advertiser combobox
$(function() {
		$("#advertisers").change(function() {
				$("#textappsigid").val("");
				getCampaignData ($(this).val());
		});
});


var getCampaignDetail = function (appsigid) {

		// This is "--- SELECT APP ---"
		if (appsigid == 0) {
				$("#list").GridUnload ();
				return;
		}

		$.get('getCampaignDetail/' + appsigid, function(data) {

				$('#tabs').html(data);
				initTabs ();

				// This is currect appsigid.
				var appsigid = $('#appsigid-form [name=appsigid]').val();

				$("#list").GridUnload ();
				createGrid (appsigid, $("#datepicker").val());
		});
}

// Afeter changed Campaign combobox
$(function() {
		$("#campaigns").change(function() {
				getCampaignDetail ($(this).val());
				$("#textappsigid").val($(this).val());
		});
});


// Afeter changed calender
$(function() {
		$("#datepicker").change(function() {

				$("#sublist").GridUnload ();

				// This is currect appsigid (get_campaign_detail.ctp).
				var appsigid = $('#appsigid-form [name=appsigid]').val();

				// This is currect publisher (get_campaign_detail.ctp).
				var pid = $('#appsigid-form [name=pid]').val();

				new_url = "jqgrid/" + appsigid + "/" + $("#datepicker").val();

				if (pid) {
						new_url = new_url + "?pid=" + pid;
				}
				//alert (new_url);

				$("#list").jqGrid('setGridParam', { url:new_url, page:1 });
				$("#list").trigger('reloadGrid');
		});
});


// Afeter changed Creative combobox
$(function() {
		$("#creatives").change(function() {

				// This is selected appsigid (get_campaign_detail.ctp).
				var appsigid = $('#appsigid-form [name=appsigid]').val();

				var active = $("#tabs").tabs( "option", "active");
				alert (active);

				var publisher_id = $("#appsigid-form [name=pid]").val();
				var creative_id = $(this).val();

				new_url = "jqgrid/" + appsigid + "/" + $("#datepicker").val() + "?pid=" + publisher_id + "&creid=" + creative_id;
				//alert (new_url);

				$("#list").jqGrid('setGridParam', { url:new_url, page:1 });
				$("#list").trigger('reloadGrid');

				$("#sublist").GridUnload ();
		});
});


function htmlEscape(str) {
    return String(str)
            .replace(/&/g, '&')
            .replace(/"/g, '"')
            .replace(/'/g, '\'')
            .replace(/</g, '<')
            .replace(/>/g, '>');
}


jQuery(document).ready(function () {

		$("#jMenu").jMenu();
		$("#textcurrentpublisherid").toggle();

		initDataPicker ();

		var arg = getUrlVars ();
		if (arg["appsigid"]) {
				getCampaignData ($("#advertisers").val());
				getCampaignDetail (arg["appsigid"]);
				$("#textappsigid").val(arg["appsigid"]);
		}
		else {
				// Init as DeNA
				$("#advertisers").val(1008);
				getCampaignData ("1008");
		}

		//$('#creatives').toggle ();

		// CSV Output
		$("#out2csv").click(function() {
				// check selected rows
				var rowIds = jQuery('#list').getGridParam('selarrrow');

				if (rowIds.length == 0) {
						msg = "Please select rows which you want to output.";
						alert(msg);
						return false;
				}

				$('#mybuffer').html("");    // clear send buffer
				// get title
				var dataIds = jQuery('#list').getDataIDs();
				var rowHead = jQuery('#list').getRowData(dataIds[0]);
				var colNames = new Array();
				var i = 0;
				for (var col in rowHead) {
						colNames[i++] = col;
				}

				var appsigid = $('#appsigid-form [name=appsigid]').val();
				$('<input type="hidden" name="data[0][0]" value="'+ appsigid +'" />').appendTo("#mybuffer");

				var current_pid = $('#appsigid-form [name=pid]').val();
				$('<input type="hidden" name="data[0][1]" value="'+ current_pid +'" />').appendTo("#mybuffer");

				var current_creid = $('#creatives').val();
				$('<input type="hidden" name="data[0][2]" value="'+ current_creid +'" />').appendTo("#mybuffer");

				// To output header part input.hidden tags
				for (var j = 0; j < colNames.length; j++) {
						$('<input type="hidden" name="data[1]['+j+']" value="' + htmlEscape(colNames[j]) + '" />').appendTo("#mybuffer");
				}

				// To output data part input.hidden tags
				for (var i = 0; i < rowIds.length; i++) {
						var row = jQuery('#list').getRowData(rowIds[i]);
						for (var j = 0; j < colNames.length; j++) {
								$('<input type="hidden" name="data['+(i+2)+']['+j+']" value="' + htmlEscape(row[colNames[j]]) + '" />').appendTo("#mybuffer");
						}
				}

				// send
				document.frm_out2csv.submit();
				return true;
		})
	});


<?php $this->Html->scriptEnd(); ?>

<div>
	<select id="advertisers">
		<?php foreach ($advertiser_datas as $data): ?>
      <?php if ($data['id'] === $selectedAdvertiser) : ?>
			  <option value="<?php echo $data['id']; ?>" selected><?php echo $data['company_name']; ?></option>
      <?php else : ?>
			  <option value="<?php echo $data['id']; ?>"><?php echo $data['company_name']; ?></option>
      <?php endif; ?>
		<?php endforeach; ?>
	</select>

	<select id="campaigns">
	</select>
</div>

<div>
  <input type="text" id="datepicker"readonly="readonly" />
</div>
<br>
<div>appsigid : <input id="textappsigid" type="text" readonly="readonly" size="50" /></div>
<div><input id="textcurrentpublisherid" type="text" readonly="readonly" size="50" /></div>
<br>

<div id="tabs">
</div> <!-- end of tabs -->

<form id="frm_out2csv" name="frm_out2csv" action="csv" method="POST">
  <button type="button" id="out2csv">CSV Output</button>
  <div id="mybuffer"></div>
</form>

<div style="margin:30px;"></div>

<table id="sublist" class="scroll" cellpadding="0" cellspacing="0">
</table>
<div id="sublistpager" style="text-align:center;"></div>
