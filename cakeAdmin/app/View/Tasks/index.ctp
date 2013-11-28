<h2>Tasks</h2>
<?php
echo $this->Form->create('Task', array('default'=>false));
echo $this->Form->input('title');
echo $this->Form->submit('Add');
echo $this->Form->end();
?>
<ul id="tasks">
<?php foreach($tasks as $task) { ?>
<li><?= h($task['Task']['title']); ?></li>
<?php } ?>
</ul>
<script language="JavaScript">
$(function() {
    $("#TaskAddForm").submit(function() {
      $.post('/tasks/ajax_add', {
        title: $("#TaskTitle").val()
      }, function(rs) {
        $("#tasks").prepend(rs);
        $("#TaskTitle").val('').focus();
      });
    });
});
</script>
