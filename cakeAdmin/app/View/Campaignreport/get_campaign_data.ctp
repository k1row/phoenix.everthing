<option value="0">--- SELECT APP ---</option>
<?php foreach ($campaign_datas as $data): ?>
  <?php if ($data['CampaignMaster']['id'] === $selectedAppsigid) : ?>
    <option value="<?php echo $data['CampaignMaster']['id']; ?>" selected><?php echo $data['CampaignMaster']['name']; ?></option>
  <?php else : ?>
    <option value="<?php echo $data['CampaignMaster']['id']; ?>"><?php echo $data['CampaignMaster']['name']; ?></option>
  <?php endif; ?>
<?php endforeach; ?>
