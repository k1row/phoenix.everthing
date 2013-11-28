  <form id="appsigid-form">
    <input type="hidden" name="appsigid" value="<?php echo $appsigid; ?>">
    <input type="hidden" name="pid" value="<?php echo $publisher_id; ?>">
  </form>

	<ul>
	  <li><a href="#tabs_total">Total</a></li>
	  <?php foreach ($publishers as $publisher): ?>
	    <li><a href="#tabs_<?php echo $publisher['PublisherMaster']['id']; ?>"><?php echo $publisher['PublisherMaster']['owner_name']; ?></a></li>
	  <?php endforeach; ?>
	</ul>

  <div style="font-size:small;">
    <div id="tabs_total">
    </div>

    <?php foreach ($publishers as $publisher): ?>
    	<div id="tabs_<?php echo $publisher['PublisherMaster']['id']; ?>">
    	</div>
    <?php endforeach; ?>

    <table id="list"></table> 
    <div id="pager" style="text-align:center;"></div>
  </div>
