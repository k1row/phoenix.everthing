<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.upload-1.0.2.min', 'jMenu.jquery', 'jquery.uploadify.min'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>


jQuery(document).ready(function () {
		$("#jMenu").jMenu();

		<?php $timestamp = time(); ?>
			$('#file_upload').uploadify({
					'formData'     : {
							'timestamp' : '<?php echo $timestamp;?>',
							'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
						},

					'debug'        : false,
					//'uploader'     : '/media/upload',
					'uploader'     : '/js/uploadify.swf',
					//'swf'          : '/js/uploadify.swf',
					'script'       : '/complete/',
					'auto'         : true,
					'queueID'      : 'queue',
					'fileTypeDesc' : 'CSV Files',
					'fileTypeExts' : '*.csv',

					'folder'       : '/files/',
					'cancelImg'    : '/img/uploadify-cancel.png',
			});
});


<?php $this->Html->scriptEnd(); ?>


<div style="padding:15px">
	<div id="upload_file" style="text-align:center">
		<h3>Upload a ".nice_extension" file to import.</h3>
		<div align="center"><input height="30" width="110" type="file" name="file_upload" id="file_upload" align="center" /></div>
	</div>
</div>

