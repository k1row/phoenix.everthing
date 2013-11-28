<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR', 'jquery.ui.ympicker', 'jMenu.jquery'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>
<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min', 'jMenu.jquery'), false, array('inline'=>false)); ?>

<?php $this->Html->scriptEnd(); ?>

<?PHP
    echo $this->Js->object($item);
?>
