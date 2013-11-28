<?php echo $this->Html->script('jquery'); ?>

<div id="element"></div>
<script>
  <?php echo $this->Js->request(array('action' => 'ajax_return', 'param1'), array('async' => true, 'update' => '#element')); ?>
</script>
