<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jMenu.jquery', 'autosize-master/jquery.autosize-min'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'jMenu.jquery', 'sunny/jquery-ui-1.10.3.custom.min', 'jquery.alerts'), false, array('inline'=>false)); ?>

var makeURL = function () {
		var appsigid = $("#campaigns").val();
		var publisher_id = $("#publishers").val();

		if (!appsigid || !publisher_id || appsigid == 0 || publisher_id == 0) {
				return $("#iosurl").val("");
				return $("#androidurl").val("");
		}

		var iosurl = "https://dsp-cl.amoad.net/?appsigid=" + appsigid + "&pubid=" + publisher_id;
		if($("#idfaraw").prop('checked')) {
				iosurl = iosurl + "&idfa={IDFA_RAW}";
		}
		if($("#idfamd5").prop('checked')) {
				iosurl = iosurl + "&idfamd5={IDFA_MD5}";
		}
		if($("#idfasha1").prop('checked')) {
				iosurl = iosurl + "&idfasha1={IDFA_SHA1}";
		}
		if($("#macaddr").prop('checked')) {
				iosurl = iosurl + "&macaddr={MACADDRESS}";
		}
		iosurl = iosurl + "&clickid={YOUR_TRANSACTION_ID}";

		if($("#geoid").prop('checked')) {
				iosurl = iosurl + "&geoid={ISO-3166-1-ALPHA-3(COUNTRY_CODE)}";
		}

		if($("#ip").prop('checked')) {
				iosurl = iosurl + "&ip={IP_ADDRESS}";
		}

		if($("#pubpid").prop('checked')) {
				iosurl = iosurl + "&pubpid={YOUR_PARTNER_PUBLISHER_ID(YOUR_DEFINITION)}";
		}

		if($("#pubcatid").prop('checked')) {
				iosurl = iosurl + "&pubcatid={YOUR_PARTNER_PUBLISHER_S_CATEGORY_ID(YOUR_DEFINITION)}";
		}

		if($("#creativeid").val()) {
				iosurl = iosurl + "&creid=" + $("#creativeid").val();
		}

		$("#iosurl").val(iosurl);


		var androidurl = "https://dsp-cl.amoad.net/?appsigid=" + appsigid + "&pubid=" + publisher_id;
		if($("#dpidraw").prop('checked')) {
				androidurl = androidurl + "&dpidraw={ANDROID_ID_RAW}";
		}
		if($("#dpidmd5").prop('checked')) {
				androidurl = androidurl + "&dpidmd5={ANDROID_ID_MD5}";
		}
		if($("#dpidsha1").prop('checked')) {
				androidurl = androidurl + "&dpidsha1={ANDROID_ID_SHA1}";
		}
		if($("#macaddr").prop('checked')) {
				iosurl = iosurl + "&macaddr={MACADDRESS}";
		}

		androidurl = androidurl + "&clickid={YOUR_TRANSACTION_ID}";

		if($("#geoid").prop('checked')) {
				androidurl = androidurl + "&geoid={ISO-3166-1-ALPHA-3(COUNTRY CODE)}";
		}

		if($("#ip").prop('checked')) {
				androidurl = androidurl + "&ip={IP_ADDRESS}";
		}

		if($("#pubpid").prop('checked')) {
				androidurl = androidurl + "&pubpid={YOUR_PARTNER_PUBLISHER_ID(YOUR_DEFINITION)}";
		}

		if($("#pubcatid").prop('checked')) {
				androidurl = androidurl + "&pubcatid={YOUR_PARTNER_PUBLISHER_S_CATEGORY_ID(YOUR_DEFINITION)}";
		}

		if($("#creativeid").val()) {
				iosurl = iosurl + "&creid=" + $("#creativeid").val();
		}

		$("#androidurl").val(androidurl);
}

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

		$("#campaigns").change(function() {
				makeURL();
		});

		$("#publishers").change(function() {
				makeURL();
		});

		$("#idfaraw").click(function() {
				makeURL();
		});
		$("#idfamd5").click(function() {
				makeURL();
		});
		$("#idfasha1").click(function() {
				makeURL();
		});
		$("#dpidraw").click(function() {
				makeURL();
		});
		$("#dpidmd5").click(function() {
				makeURL();
		});
		$("#dpidsha1").click(function() {
				makeURL();
		});
		$("#macaddr").click(function() {
				makeURL();
		});
		$("#geoid").click(function() {
				makeURL();
		});
		$("#ip").click(function() {
				makeURL();
		});
		$("#pubpid").click(function() {
				makeURL();
		});
		$("#pubcatid").click(function() {
				makeURL();
		});
		$("#creativeid").bind('change', function() {
				makeURL();
		});
	});


jQuery(document).ready(function () {
		$("#jMenu").jMenu();
		$("#iosurl").val("");
		$("#androidurl").val("");
});


<?php $this->Html->scriptEnd(); ?>

<div>
  <div>Select campaign :</div>
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

<br/>
<div>
  <div>Select publisher :</div>
	<select id="publishers">
		<?php foreach ($publishers as $publisher): ?>
		  <option value="<?php echo $publisher['PublisherMaster']['id']; ?>">[<?php echo $publisher['PublisherMaster']['id']; ?>]&nbsp;&nbsp;<?php echo $publisher['PublisherMaster']['owner_name']; ?></option>
		<?php endforeach; ?>
	</select>
</div>

<br/>

<div>
	<label><input type="checkbox" id="idfaraw" checked />IDFA RAW</label><br />
	<label><input type="checkbox" id="idfamd5" checked  />IDFA MD5</label><br />
	<label><input type="checkbox" id="idfasha1" checked  />IDFA SHA1</label><br />
</div>
<br />
<div>
	<label><input type="checkbox" id="dpidraw" checked  />DPID RAW</label><br />
	<label><input type="checkbox" id="dpidmd5" checked  />DPID MD5</label><br />
	<label><input type="checkbox" id="dpidsha1" checked  />DPID SHA1</label><br />
</div>
<div>
	<label><input type="checkbox" id="macaddr" checked  />MACADDRESS</label><br />
</div>
<br />
<div>
	<label><input type="checkbox" id="geoid" />GEO ID</label><br />
	<label><input type="checkbox" id="ip" checked />IP ADDRESS</label><br />
	<label><input type="checkbox" id="pubpid" checked />PARTNER PUBLISHER ID</label><br />
	<label><input type="checkbox" id="pubcatid" checked />PARTNER PUBLISHER CATEGORY ID</label><br />
</div>

<br />
<div>
CREATIVE_ID : <input id="creativeid" type="text" size="50" />
</div>

<br />
<table>
<tr>
  <td>
		<div>The click URL for iOS should be like this:</div>
		<div>
		  <textarea id="iosurl" readonly="readonly" style="width: 800px; height:100px;">
			</textarea>
		</div>
	</td>
</tr>
<tr height="50"><td></td></tr>
<tr>
  <td>
		<div>The click URL for Android should be like this:</div>
		<div>
		  <textarea id="androidurl" readonly="readonly" style="width: 800px; height:100px;">
			</textarea>
		</div>
	</td>
</tr>
</table>
