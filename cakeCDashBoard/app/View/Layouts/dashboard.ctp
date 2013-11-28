<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>

  <STYLE type="text/css">
      #footer p{margin:0 0 10px 0;font-size:10px}.tipsy{padding:4px;font-size:11px;position:absolute;z-index:100000}.tipsy-inner{padding:2px 8px 2px 8px;background-color:black;color:white;max-width:200px;text-align:center}.tipsy-inner{border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px}.tipsy-arrow{position:absolute;background:url("/assets/admin/tipsy.gif") no-repeat top left;width:9px;height:5px}.tipsy-n .tipsy-arrow{top:0;left:50%;margin-left:-4px}.tipsy-nw .tipsy-arrow{top:0;left:10px}.tipsy-ne .tipsy-arrow{top:0;right:10px}.tipsy-s{margin-top:-5px}.tipsy-w{margin-left:5px}.tipsy-e{margin-right:-5px}.tipsy-n{margin-top:5px}.tipsy-s .tipsy-arrow{bottom:0;left:50%;margin-left:-4px;background-position:bottom left}.tipsy-sw .tipsy-arrow{bottom:0;left:10px;background-position:bottom left}.tipsy-se .tipsy-arrow{bottom:0;right:10px;background-position:bottom left}.tipsy-e .tipsy-arrow{top:50%;margin-top:-4px;right:0;width:5px;height:9px;background-position:top right}.tipsy-w .tipsy-arrow{top:50%;margin-top:-4px;left:0;width:5px;height:9px}.cPicker span{margin-left:36px;font-size:11px;white-space:nowrap;padding-top:2px;display:block}
  </STYLE>

</head>
<body>
  <?php echo $this->Session->flash(); ?>

  <div align="center">
  	<ul id="jMenu">
<!--
  		<li style="width: 100px;">
  		  <a class="fNiv" href="/dashboard/">Dashboard</a>
  		</li>
  		<li style="width: 100px;">
  		  <a class="fNiv" href="/dashboard2/">Dashboard2</a>
  		</li>
-->
  		<li style="width: 100px;">
  		  <a class="fNiv" href="/dashboard3/">Dashboard</a>
  		</li>
  		<li style="width: 100px;">
  		  <a class="fNiv" href="/upload/">Upload</a>
  		</li>
  	</ul>
  </div>

  <br />
	<?php echo $this->fetch('content'); ?>

  <br />
  <br />
  <div id="footer" style="text-align:center">
    <img src="/img/clogo.png" width="150" alt="AMoAd, Inc." /><br />
    <div style="text-align:center; font-size:11px">&copy; 2013 AMoAd International, Inc.</div>
  </div>

</body>
</html>
