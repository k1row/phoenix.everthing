  <form id="creative-form">
    <input type="hidden" name="appsigid" value="<?php echo $appsigid; ?>">
    <input type="hidden" name="pid" value="<?php echo $publisher_id; ?>">
  </form>

  <option value="0">--- SELECT CREATIVE ---</option>
  <?php foreach ($creatives as $creative): ?>
    <option value="<?php echo $creative['AdminAnalyzeCreative']['creative_id']; ?>"><?php echo $creative['AdminAnalyzeCreative']['creative_id']; ?></option>
  <?php endforeach; ?>

