<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jMenu.jquery', 'form-master/jquery.form', 'jquery.upload-1.0.2.min', 'dropzone.js'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


jQuery(document).ready(function () {
		$("#jMenu").jMenu();

		$("form").submit(function() {
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
			success: function() {
					var percentVal = '100%';
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

   <div class="col-md-6">
  <form action="fileRead?type=back" class="dropzone">
    <div class="fallback">
				<input type="file" name="file" id="file1"/><br />
    </div>
  </form>
</div>
