<?php echo $this->Html->script('jquery'); ?>
<h1>a</h1>

$(function(){
 
    $('form').on('submit',function(){
        event.preventDefault();
        event.stopPropagation();
         
        $.post('/pages/ajaxtest',$(this).serialize(),function(event){
            console.log(event);
        })
         
    });
 
});
<?php $this->Html->scriptEnd()?>
<?php echo $this->Form->create('Post');?>
<?php echo $this->Form->input('title');?>
<?php echo $this->Form->input('name');?>
<?php echo $this->Form->input('description');?>
<?php echo $this->Form->select('selectlist', array('a' => '‚ ','b' => '‚¤'));?>
<?php echo $this->Form->select('selectlist_m', array(  'a' => '‚ ','b' => '‚¤'),array('multiple' => 'multiple'));?>
<?php echo $this->Form->end('“o˜^');?>
