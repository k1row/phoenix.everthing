<?php

/*

php /usr/local/nginx/cakeAdmin/app/Console/cake.php AdminAnalyzeCreativeShell /usr/local/nginx/cakeAdmin/app

 */


App::uses('Shell', 'Console');


class AdminAnalyzeCreativeShell extends Shell
{
  var $uses = array('PublisherMaster', 'CampaignMaster', 'Click', 'Conversion', 'AdminAnalyzeCreative');
  var $target_date;
  var $target_datetime;

  function measure()
  {
    list($m, $s) = explode(' ', microtime());
    return ((float)$m + (float)$s);
  }

  function startup ()
  {
    $this->log (Configure::version(), LOG_DEBUG);

    Configure::write ('debug', 2);
    //$debug = Configure::read ('debug');
    //$this->target_date = date ("Y-m-d", strtotime ("-1 day"));
    $this->target_date = date ("Y-m-d");

    //$this->target_datetime = date ("Y-m-d H", strtotime ("-1 hour"));
    $this->target_datetime = date ("Y-m-d H");
    $this->User = ClassRegistry::init ('User');
  }

  public function main ()
  {
    $start_ticktime = $this->measure();

    for ($i = 0; $i >= 0; $i--)
    {
      for ($j = 24; $j >= 0; $j--)
      {
        $this->target_datetime = date ("Y-m-d H", strtotime ("-$j hour"));
        $this->target_date = substr($this->target_datetime, 0, 10);

        $this->log ($this->target_date, LOG_DEBUG);
        $this->log ($this->target_datetime, LOG_DEBUG);

        $this->insertAnalyzeData ($this->getExistCampaign ());
        $this->log ("CHECK_POINT ".($this->measure() - $start_ticktime).PHP_EOL, LOG_DEBUG);
      }
    }

    $this->log ($this->target_date, LOG_DEBUG);
    $this->log ($this->target_datetime, LOG_DEBUG);
    $this->insertAnalyzeData ($this->getExistCampaign ());
  }

  function getExistCampaign ()
  {
    $datas = $this->CampaignMaster->find ('all', array (
      'conditions' => array (array ('CampaignMaster.begin_time <=' => "$this->target_date"),
                             array ('CampaignMaster.end_time >=' => "$this->target_date"))));

    $result = array ();
    foreach ($datas as $data)
    {
      if ($data['CampaignMaster']['advertiser_id'] === '100000000') { continue; }
      if ($data['CampaignMaster']['id'] === '2045444883139e79c4246183595c2df2613d6192') { continue; }
      if ($data['CampaignMaster']['id'] === '40b247a5c58ea510c773942a6ba0aa3a7467cc35') { continue; }

      $insert_data;
      $insert_data['appsigid'] = $data['CampaignMaster']['id'];
      $insert_data['target_date'] = substr($this->target_datetime, 0, 10);
      $insert_data['target_datetime'] = $this->target_datetime;
      $insert_data['advertiser_id'] = $data['CampaignMaster']['advertiser_id'];
      $insert_data['campaign_name'] = $data['CampaignMaster']['name'];
      $insert_data['expense'] = $data['CampaignMaster']['expense'];
      $insert_data['cpi'] = $data['CampaignMaster']['cpi'];

      $insert_data['permit_transactionid_match'] = 0;
      if ($data['CampaignMaster']['smac'] == 1 || $data['CampaignMaster']['permit_transactionid_match'] == 1)
      {
        $insert_data['permit_transactionid_match'] = 1;
      }

      $this->getCampaignResult ($data['CampaignMaster']['id'], $result, $insert_data);
    }
    //echo $this->sqlDump ();

    $this->log ($result, LOG_DEBUG);
    return $result;
  }

  function getCampaignResult ($appsigid, &$result, $insert_data)
  {
    $this->click_table_name = $appsigid."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);

    //$this->log (substr($this->target_datetime, 0, 7)."-01 00:00:00", LOG_DEBUG);
    $datas = $this->Click->find ('all', array (
      'fields' => array ('DISTINCT publisher_id', 'creative_id'),
      'conditions' => array ('Click.appsigid' => $appsigid,
                             'Click.created >=' => substr($this->target_datetime, 0, 7)."-01 00:00:00")));

    foreach ($datas as $data)
    {
      if ($data['Click']['publisher_id'] === '200000000') { continue; }

      $insert_data['publisher_id'] = $data['Click']['publisher_id'];
      $insert_data['creative_id'] = $data['Click']['creative_id'];
      $this->getPublisherInfo ($data['Click']['publisher_id'], $insert_data);
      $insert_data['click_num'] = $this->getClicks ($appsigid, $data['Click']['publisher_id'], $data['Click']['creative_id']);
      $insert_data['install_num'] = $this->compareClick2Conversion ($appsigid, $data['Click']['publisher_id'], $data['Click']['creative_id'], $insert_data['permit_transactionid_match']);

      if ($insert_data['click_num'] == 0)
      {
        $insert_data['cvr'] = 0.00;
      }
      else
      {
        $insert_data['cvr'] = round ($insert_data['install_num'] / $insert_data['click_num'], 2);
      }

      if ($insert_data['install_num'] == 0)
      {
        $insert_data['sales'] = 0;
      }
      else
      {
        $insert_data['sales'] = $insert_data['install_num'] * $insert_data['cpi'];
      }
      array_push ($result, $insert_data);
    }

    //$this->log ($result, LOG_DEBUG);
  }

  function getClicks ($appsigid, $publisher_id, $creative_id)
  {
    $this->click_table_name = $appsigid."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);

    $datas = $this->Click->find ('all', array (
      'conditions' => array ('Click.appsigid' => $appsigid,
                             'Click.publisher_id' => $publisher_id,
                             'Click.creative_id' => $creative_id,
                             'Click.created >=' => "$this->target_datetime".":00:00",
                             'Click.created <=' => "$this->target_datetime".":59:59")));

    //echo $this->sqlDump ();
    return $this->getActualRecordNum ('Click', $datas);
  }

  function compareClick2Conversion ($appsigid, $publisher_id, $creative_id, $permit_transactionid_match)
  {
    //$this->log ('getConversions appsigid ='.$appsigid, LOG_DEBUG);

    $datas = $this->Conversion->find ('all', array (
      'conditions' => array ('Conversion.appsigid' => $appsigid,
                             'Conversion.created >=' => "$this->target_datetime".":00:00",
                             'Conversion.created <=' => "$this->target_datetime".":59:59")));

    $processed_record = array ();
    $install_num = 0;
    foreach ($datas as $data)
    {
      if ($this->isDuplicateRecord ("Conversion", $data, $processed_record))
        continue;

      // check dpidraw
      if ($this->doUdid ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check dpidmd5
      if ($this->doUdidmd5 ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check dpidsha1
      if ($this->doUdidsha1 ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check idfa
      if ($this->doIdfa ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check idfamd5
      if ($this->doIdfamd5 ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check idfasha1
      if ($this->doIdfasha1 ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check openudid
      //if ($this->doOpenUdid ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check macaddr
      //if ($this->doMacaddr ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }

      // check transactionid
      if ($permit_transactionid_match){ if ($this->doTransactionid ($appsigid, $publisher_id, $creative_id, $data)) { $install_num++; continue; }}
    }

    return $install_num;
  }

  function getActualRecordNum ($table_name, $datas)
  {
    $result = array ();
    foreach ($datas as $data)
    {
      //$this->log ($data, LOG_DEBUG);
      $key = 0;

      if ($data["$table_name"]['dpidraw'])
      {
        if (array_key_exists ($data["$table_name"]['dpidraw'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['dpidraw']; }
      }
      if ($data["$table_name"]['dpidmd5'])
      {
        if (array_key_exists ($data["$table_name"]['dpidmd5'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['dpidmd5']; }
      }
      if ($data["$table_name"]['dpidsha1'])
      {
        if (array_key_exists ($data["$table_name"]['dpidsha1'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['dpidsha1']; }
      }
      if ($data["$table_name"]['openudid'])
      {
        if (array_key_exists ($data["$table_name"]['openudid'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['openudid']; }
      }
      if ($data["$table_name"]['idfa'])
      {
        if (array_key_exists ($data["$table_name"]['idfa'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['idfa']; }
      }
      if ($data["$table_name"]['idfamd5'])
      {
        if (array_key_exists ($data["$table_name"]['idfamd5'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['idfamd5']; }
      }
      if ($data["$table_name"]['idfasha1'])
      {
        if (array_key_exists ($data["$table_name"]['idfasha1'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['idfasha1']; }
      }
      if ($data["$table_name"]['macaddr'])
      {
        if (array_key_exists ($data["$table_name"]['macaddr'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['macaddr']; }
      }
      if ($table_name === 'Click' && $data["$table_name"]['publisher_click_id'])
      {
        if (array_key_exists ($data["$table_name"]['publisher_click_id'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['publisher_click_id']; }
      }
      if ($table_name === 'Conversion' && $data["$table_name"]['transactionid'])
      {
        if (array_key_exists ($data["$table_name"]['transactionid'], $result))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['transactionid']; }
      }

      $result{$key} = '1';
    }

    //$this->log ($result, LOG_DEBUG);
    //$this->log (count ($result), LOG_DEBUG);
    return count ($result);
  }

  function isDuplicateRecord ($table_name, $data, &$processed_record)
  {
    $key = 0;

    if ($data["$table_name"]['dpidraw'])
    {
      if (array_key_exists ($data["$table_name"]['dpidraw'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['dpidraw']; }
    }
    if ($data["$table_name"]['dpidmd5'])
    {
      if (array_key_exists ($data["$table_name"]['dpidmd5'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['dpidmd5']; }
    }
    if ($data["$table_name"]['dpidsha1'])
    {
      if (array_key_exists ($data["$table_name"]['dpidsha1'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['dpidsha1']; }
    }
    if ($data["$table_name"]['openudid'])
    {
      if (array_key_exists ($data["$table_name"]['openudid'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['openudid']; }
    }
    if ($data["$table_name"]['idfa'])
    {
      if (array_key_exists ($data["$table_name"]['idfa'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['idfa']; }
    }
    if ($data["$table_name"]['idfamd5'])
    {
      if (array_key_exists ($data["$table_name"]['idfamd5'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['idfamd5']; }
    }
    if ($data["$table_name"]['idfasha1'])
    {
      if (array_key_exists ($data["$table_name"]['idfasha1'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['idfasha1']; }
    }
    if ($data["$table_name"]['macaddr'])
    {
      if (array_key_exists ($data["$table_name"]['macaddr'], $processed_record))
        return 1;

      if ($key == 0) { $key = $data["$table_name"]['macaddr']; }
    }

    $processed_record{$key} = '1';
    return 0;
  }

  function doUdid ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['dpidraw']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.dpidraw' => $data['Conversion']['dpidraw']));
    }

    return 0;
  }
  function doUdidmd5 ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['dpidmd5']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.dpidmd5' => $data['Conversion']['dpidmd5']));
    }

    return 0;
  }
  function doUdidsha1 ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['dpidsha1']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.dpidsha1' => $data['Conversion']['dpidsha1']));
    }

    return 0;
  }
  function doOpenUdid ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['openudid']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.openudid' => $data['Conversion']['openudid']));
    }

    return 0;
  }
  function doIdfa ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['idfa']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.idfa' => $data['Conversion']['idfa']));
    }

    return 0;
  }
  function doIdfamd5 ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['idfamd5']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.idfamd5' => $data['Conversion']['idfamd5']));
    }

    return 0;
  }
  function doIdfasha1 ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['idfasha1']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.idfasha1' => $data['Conversion']['idfasha1']));
    }

    return 0;
  }
  function doMacaddr ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['macaddr']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.macaddr' => $data['Conversion']['macaddr']));
    }

    return 0;
  }
  function doTransactionid ($appsigid, $publisher_id, $creative_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['transactionid']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.creative_id' => $creative_id,
                                                       'Click.publisher_click_id' => $data['Conversion']['transactionid']));
    }

    return 0;
  }

  function isInstalledTargetPublisher ($appsigid, $conditions)
  {
    $this->click_table_name = $appsigid."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);

    $datas = $this->Click->find ('all', array (
      'fields' => 'id',
      'conditions' => $conditions));

    if (count($datas) > 1)
    {
      // It's erro because it found more than 1 record. It's illegal.
      // For now it returns 1;
      $this->log ("Found more than 1 click datas", LOG_DEBUG);
      return 1;
    }

    return count($datas);
  }

  function getPublisherInfo ($publisher_id, &$insert_data)
  {
    $datas = $this->PublisherMaster->find ('all', array (
      'conditions' => array ('PublisherMaster.id' => $publisher_id)));

    if (empty ($datas))
      return 0;

    $insert_data['ios'] = $datas[0]['PublisherMaster']['ios'];
    $insert_data['android'] = $datas[0]['PublisherMaster']['android'];
    $insert_data['incentivized'] = $datas[0]['PublisherMaster']['incentivized'];
    $insert_data['non_incentivized'] = $datas[0]['PublisherMaster']['non_incentivized'];
  }

  function insertAnalyzeData ($result)
  {
    foreach ($result as $ret)
    {
      //$this->log ($result, LOG_DEBUG);

      $this->AdminAnalyzeCreative->create ();

      $field = array (
        'publisher_id' => $ret['publisher_id'],
        'advertiser_id' => $ret['advertiser_id'],
        'appsigid' => $ret['appsigid'],
        'creative_id' => $ret['creative_id'],
        'target_date' => $ret['target_date'],
        'target_datetime' => $ret['target_datetime'].":00:00",
        'campaign_name' => $ret['campaign_name'],
        'expense' => $ret['expense'],
        'cpi' => $ret['cpi'],
        'click_num' => $ret['click_num'],
        'install_num' => $ret['install_num'],
        'cvr' => $ret['cvr'],
        'sales' => $ret['sales'],
        'ios' => $ret['ios'],
        'android' => $ret['android'],
        'incentivized' => $ret['incentivized'],
        'non_incentivized' => $ret['non_incentivized'],
        );

      $already_data = $this->isExistsAnalyzeData ($ret);
      if ($already_data)
      {
        $field['id'] = $already_data['AdminAnalyzeCreative']['id'];
      }

      //$this->log ("field", LOG_DEBUG);
      //$this->log ($field, LOG_DEBUG);

      $this->AdminAnalyzeCreative->set ($field);
      $this->AdminAnalyzeCreative->save ();
      //echo $this->sqlDump ();
    }
  }

  function isExistsAnalyzeData ($ret)
  {
    $datas = $this->AdminAnalyzeCreative->find ('all', array (
      'conditions' => array ('AdminAnalyzeCreative.publisher_id' => $ret['publisher_id'],
                             'AdminAnalyzeCreative.advertiser_id' => $ret['advertiser_id'],
                             'AdminAnalyzeCreative.appsigid' => $ret['appsigid'],
                             'AdminAnalyzeCreative.creative_id' => $ret['creative_id'],
                             'AdminAnalyzeCreative.target_datetime' => $ret['target_datetime'].":00:00")));
    return count ($datas) >= 1 ? $datas[0] : 0;
  }

  function isExistsField ($filed_data)
  {
    if (is_null ($filed_data))
      return 0;

    if (!isset ($filed_data))
      return 0;

    if (empty ($filed_data))
      return 0;

    return 1;
  }

  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ();
  }
}
