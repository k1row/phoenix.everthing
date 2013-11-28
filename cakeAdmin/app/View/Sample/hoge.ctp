<?php echo $form->create(null, array('type'=>'post', 'default'=>false)); ?>
<?php echo $form->input(null, array('label'=>'', 'type'=>'text', 'id'=>'inputText')); ?>
<?php echo $form->submit('Submit', array('id'=>'submitButton')); ?>
<?php echo $form->end(); ?>
 
<script type="text/javascript">
<!--
$(function(){
 $('#submitButton').click(function(){
  $.post('<?php echo Router::url(array('controller'=>'users','action'=>'hoge')); ?>', {input_text: $('#inputText').val()}, function(res){
   alert(res);
  });
 );
});
-->
</script>
