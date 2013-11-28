<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jMenu.jquery', 'jquery.ui.draggable'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'jMenu.jquery', 'sunny/jquery-ui-1.10.3.custom.min'), false, array('inline'=>false)); ?>

$(function() {
		$("#from").datepicker({
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 1,
			onClose: function( selectedDate ) {
					$("#to").datepicker("option", "minDate", selectedDate);
			}
		});

		$("#to").datepicker({
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 1,
			onClose: function( selectedDate ) {
					$("#from").datepicker("option", "maxDate", selectedDate);
			}
		});
});


var getCreativeData = function (appsigid) {

		$.get('getCreativeData/' + appsigid, function(data) {
				$("#creatives").html(data);
		});
}

// Afeter changed Campaign combobox
$(function() {
		$("#campaigns").change(function() {
				getCreativeData ($(this).val());
		});
});


var getCampaignData = function (advertiserId) {

		$.get('getCampaignData/' + advertiserId, function(data) {
				$("#campaigns").html(data);
		});
}

// Afeter changed Advertiser combobox
$(function() {
		$("#advertisers").change(function() {
				getCampaignData ($(this).val());
		});
});


String.prototype.replaceAll = function (org, dest) {
		return this.split(org).join(dest);
}


function download(parameter) {
    query = {
        parameter : parameter,
    }
    $.post("/DownloadFileCheckServlet", query, read);
}

function read(resp) {
    // コールバック関数でなんか処理
    if (resp.length == 0) {
        // レスポンスに何も入力されて無ければファイル有り(とする)サーブレットをGetメソッドで呼び出し
        location.href = "/DownloadServlet";
    } else {
        // レスポンスに入力されていればエラーメッセージとしてダイアログに出力(とする)
        alert(resp);
    }
}

jQuery(document).ready(function () {
		$("#jMenu").jMenu();

		// CSV Output
		$("#out2csv").click(function() {

				$("body").append("<p id='loading'><img src='images/gif-load.gif' alt=''></p>");

				var from = $("#from").val();
				if (!from) {
						return alert ("You need to select FROM date");
				}

				var to = $("#to").val();
				if (!to) {
						return alert ("You need to select TO date");
				}

				if (compare2now(from) > 0) {
						return alert ("FROM is made to a date in the future.");
				}

				var appsigid = $("#campaigns").val();
				var creative_id = $("#creatives").val();
				//alert (creative_id);

				var url = 'output2csv/' + appsigid + "/" + creative_id + "/?from=" + from.replace(/\u002f/g, "-") + "&to=" + to.replace(/\u002f/g, "-");
				window.location.href = url;
				$("#loading").hide();
		});
});

function compare2now (datestr)
{
		var today = new Date();
		today.setHours(0);
		today.setMinutes(0);
		today.setSeconds(0);
		today.setMilliseconds(0);

		var adate = new Date(datestr);
		adate.setHours(0);
		adate.setMinutes(0);
		adate.setSeconds(0);
		adate.setMilliseconds(0);

		if (adate.getTime() <= today.getTime()) {
				return -1;
		}

		return 1;
}


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

	<select id="creatives">
	</select>
</div>

<br />
<div>
	<label for="from">From</label>
	<input type="text" id="from" name="from" />
	<label for="to">to</label>
	<input type="text" id="to" name="to" />
</div>

<br />
<input id="out2csv" type="button" value="CSV Output" />
