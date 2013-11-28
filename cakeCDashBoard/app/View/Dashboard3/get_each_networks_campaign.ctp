	  <option value="0">--- SELECT CAMPAIGN_NAME ---</option>
		<?php foreach ($campaigns as $campaign): ?>
		  <option value="<?php echo $campaign['CreativeDashboardForDena']['campaign_name']; ?>"><?php echo $campaign['CreativeDashboardForDena']['campaign_name']; ?></option>
		<?php endforeach; ?>
