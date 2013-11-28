<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.upload-1.0.2.min', 'jMenu.jquery', 'jquery.uploadify.min'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>

$(function() {
		$("form").submit(function() {
		});
});


jQuery(document).ready(function () {
		$("#jMenu").jMenu();

		$('#file1').change(function() {
				$(this).upload('fileRead', function(res) {
						$(res).insertAfter(this);
				}, 'html');
		});

		/*
		var bar = $('.bar');
		var percent = $('.percent');
		var status = $('#status');
		$('form').ajaxForm({
			beforeSend: function() {
					status.empty();
					var percentVal = '0%';
					bar.width(percentVal)
						percent.html(percentVal);
			},
			uploadProgress: function(event, position, total, percentComplete) {
					var percentVal = percentComplete + '%';
					bar.width(percentVal)
						percent.html(percentVal);
			},
			complete: function(xhr) {
					status.html(xhr.responseText);
			}
		});
		*/
});


<?php $this->Html->scriptEnd(); ?>

<br />

	<form method="post" enctype="multipart/form-data">
	  <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="example" />
		<input type="file" name="file" id="file1"/><br />
	</form>
	<br />
	<div class="progress">
	    <div class="bar"></div >
	    <div class="percent">0%</div >
	</div>

	<div id="status"></div>
