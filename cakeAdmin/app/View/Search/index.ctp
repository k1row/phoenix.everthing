<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jMenu.jquery', 'autosize-master/jquery.autosize-min'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'jMenu.jquery', 'sunny/jquery-ui-1.10.3.custom.min', 'jquery.alerts'), false, array('inline'=>false)); ?>


jQuery(document).ready(function () {
		$("#jMenu").jMenu();

		$('#btn').click(function() {
				if(!($("#device_type").prop('idfaraw')) &&
					 !($("#device_type").prop('dpidraw')))
				{
						//return alert ("Which device it type ?");
				}

				$('#device_id_form').attr('action', 'getData');
				$('#device_id_form').submit();
		});
});


<?php $this->Html->scriptEnd(); ?>

<div>
	<form method="post" name="device_id_form" id="device_id_form">
		<label><input type="radio" name="device_type" id="idfaraw" />IDFA RAW</label><br />
		<label><input type="radio" name="device_type" id="dpidraw" />ANDROID ID RAW</label><br />

    <br />
    <div>Enter the device id</div>
		<div><span style="color:#FF0000; font-size:large">Please put each one-ID on one line.</span></div><br />
		<textarea name="device_ids" style="width:300px; height:500px; padding:5px;"></textarea><br />
    <input type="submit" name="btn" id="btn" value="Search CL/CV data">
	</form>
</div>
