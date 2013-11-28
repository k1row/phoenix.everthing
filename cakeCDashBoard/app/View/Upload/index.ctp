<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'form-master/jquery.form', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>

$(function() {
		$("form").submit(function() {
		/*
				$(this).upload('fileRead', function(res) {
						$(res).insertAfter(this);
				}, 'html');
				*/
		});
});


jQuery(document).ready(function () {
		$("#jMenu").jMenu();

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
			success: function() {
					var percentVal = '100%';
					bar.width(percentVal)
						percent.html(percentVal);
			},
			complete: function(xhr) {
					status.html(xhr.responseText);
			}
		});
});


<?php $this->Html->scriptEnd(); ?>


<h1>File Upload Progress</h1>
<form action="fileRead" method="post" enctype="multipart/form-data">
<input type="file" name="file" id="file"/><br />
  <input type="submit" value="Upload File to Server">
</form>

<div class="progress">
  <div class="bar"></div >
  <div class="percent">0%</div >
</div>

<div id="status"></div>
