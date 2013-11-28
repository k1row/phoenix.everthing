<?php

/*
実際にTerminalから実行する場合は、
/usr/bin/php /CakePHPのパス/app/Console/cake.php Hoge -app /CakePHPのパス/app/

1) /usr/bin/php                        ･･･ phpまでのパス
2) /CakePHPのパス/app/Console/cake.php ･･･ cake.phpまでのパス(固定）
3) Hoge                                ･･･ Shell.phpを除いたシェル名
4) -app                                ･･･ appコマンド
5) /CakePHPのパス/app/                 ･･･ appまでのパス(固定）

メソッド名を指定しない場合自動出来に、main()メソッドが呼び出される
php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeIndividualCreative /usr/local/nginx/cakeAdmin/app

シェル名の後に任意のメソッドを指定できる
php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeIndividualCreative test /usr/local/nginx/cakeAdmin/app

php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeIndividualCreative main 2013-03-10 -app /usr/local/nginx/cakeAdmin/app
パラメータを渡すことも可能
*/



/*

CREATE TABLE `admin_analyze_creatives` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`publisher_id` INT(11) NOT NULL,
	`advertiser_id` INT(11) NOT NULL,
	`appsigid` VARCHAR(255) NOT NULL DEFAULT '',
	`creative_id` VARCHAR(255) NOT NULL DEFAULT '',
	`campaign_name` VARCHAR(255) NOT NULL,
	`expense` SMALLINT(6) NOT NULL,
	`cpi` DOUBLE(4,2) NOT NULL,
	`install_num` SMALLINT(6) NOT NULL,
	`ios` TINYINT(1) NULL DEFAULT '0',
	`android` TINYINT(1) NULL DEFAULT '0',
	`created` DATETIME NOT NULL,
	`modified` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `creative_id` (`creative_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


*/

App::uses('Shell', 'Console');

class AnalyzeIndividualCreativeShell extends Shell
{
  var $uses = array('PublisherMaster', 'CampaignMaster', 'Click', 'Conversion', 'AdminAnalyzeCreative');
  var $target_date;

  function startup ()
  {
    $this->log (Configure::version(), LOG_DEBUG);

    Configure::write ('debug', 2);
    //$debug = Configure::read ('debug');
    //$this->target_date = date ("Y-m-d", strtotime ("-1 day"));
    $this->target_date = date ("Y-m-d");
    $this->User = ClassRegistry::init ('User');
  }

  public function main ()
  {
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
      if ($data['CampaignMaster']['id'] === '07b5ed46511dcc34507a546304f3366785758695')
      {
        $insert_data;
        $insert_data['appsigid'] = $data['CampaignMaster']['id'];
        $insert_data['target_date'] = $this->target_date;
        $insert_data['advertiser_id'] = $data['CampaignMaster']['advertiser_id'];
        $insert_data['campaign_name'] = $data['CampaignMaster']['name'];
        $insert_data['expense'] = $data['CampaignMaster']['expense'];
        $insert_data['cpi'] = $data['CampaignMaster']['expense'];

        $this->getCampaignResult ($data['CampaignMaster']['id'], $result, $insert_data);
      }
    }
    return $result;
  }

  function getCampaignResult ($appsigid, &$result, $insert_data)
  {
    $this->click_table_name = $appsigid."Click";
    $this->Click->setSource ($this->click_table_name);

    $datas = $this->Click->find ('all', array (
      'fields' => array ('DISTINCT creative_id', 'publisher_id', 'appsigid'),
      'conditions' => array ('Click.appsigid' => $appsigid)));

    foreach ($datas as $data)
    {
      if ($data['Click']['publisher_id'] == 200000000) { continue; }
      if ($data['Click']['appsigid'] === '2045444883139e79c4246183595c2df2613d6192') { continue; }
      if ($data['Click']['appsigid'] === '40b247a5c58ea510c773942a6ba0aa3a7467cc35') { continue; }

      $insert_data['publisher_id'] = $data['Click']['publisher_id'];
      $insert_data['creative_id'] = $data['Click']['creative_id'];

      $this->getPublisherInfo ($data['Click']['publisher_id'], $insert_data);
      $insert_data['click_num'] = $this->getClicks ($appsigid, $data['Click']['publisher_id'], $data['Click']['creative_id']);
      $insert_data['install_num'] = $this->compareClick2Conversion ($appsigid, $data['Click']['publisher_id']);
      $insert_data['cvr'] = round ($insert_data['install_num'] / $insert_data['click_num'], 2);
      $insert_data['sales'] = $insert_data['install_num'] * $insert_data['cpi'];
      array_push ($result, $insert_data);
    }

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
                             'Click.created LIKE' => "$this->target_date%")));

    //$this->log ($datas, LOG_DEBUG);
    //echo $this->sqlDump ();
    return $this->getActualRecordNum ('Click', $datas);
  }

  function compareClick2Conversion ($appsigid, $publisher_id)
  {
    $datas = $this->Conversion->find ('all', array (
      'conditions' => array ('Conversion.appsigid' => $appsigid,
                             'Conversion.created LIKE' => "$this->target_date%")));

    $processed_record = array ();
    $install_num = 0;
    foreach ($datas as $data)
    {
      if ($this->isDuplicateRecord ("Conversion", $data, $processed_record))
        continue;

      // check dpidraw
      if ($this->doUdid ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check dpidmd5
      if ($this->doUdidmd5 ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check dpidsha1
      if ($this->doUdidsha1 ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check openudid
      if ($this->doOpenUdid ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check idfa
      if ($this->doIdfa ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check idfamd5
      if ($this->doIdfamd5 ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check idfasha1
      if ($this->doIdfasha1 ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check macaddr
      if ($this->doMacaddr ($appsigid, $publisher_id, $data)) { $install_num++; continue; }

      // check transactionid
      if ($this->doTransactionid ($appsigid, $publisher_id, $data)) { $install_num++; continue; }
    }

    return $install_num;
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
    if ($table_name === 'Click' && $data["$table_name"]['publisher_click_id'])
    {
      if (array_key_exists ($data["$table_name"]['publisher_click_id'], $processed_record))
        continue;

      if ($key == 0) { $key = $data["$table_name"]['publisher_click_id']; }
    }
    if ($table_name === 'Conversion' && $data["$table_name"]['transactionid'])
    {
      if (array_key_exists ($data["$table_name"]['transactionid'], $processed_record))
        continue;

      if ($key == 0) { $key = $data["$table_name"]['transactionid']; }
    }

    $processed_record{$key} = '1';
    return 0;
  }

  function getActualRecordNum ($table_name, $datas)
  {
    $result = array ();
    foreach ($datas as $data)
    {
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

    return count ($result);
  }

  function doUdid ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['dpidraw']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.dpidraw' => $data['Conversion']['dpidraw']));
    }

    return 0;
  }
  function doUdidmd5 ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['dpidmd5']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.dpidmd5' => $data['Conversion']['dpidmd5']));
    }

    return 0;
  }
  function doUdidsha1 ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['dpidsha1']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.dpidsha1' => $data['Conversion']['dpidsha1']));
    }

    return 0;
  }
  function doOpenUdid ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['openudid']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.openudid' => $data['Conversion']['openudid']));
    }

    return 0;
  }
  function doIdfa ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['idfa']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.idfa' => $data['Conversion']['idfa']));
    }

    return 0;
  }
  function doIdfamd5 ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['idfamd5']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.idfamd5' => $data['Conversion']['idfamd5']));
    }

    return 0;
  }
  function doIdfasha1 ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['idfasha1']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.idfasha1' => $data['Conversion']['idfasha1']));
    }

    return 0;
  }
  function doMacaddr ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['macaddr']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.macaddr' => $data['Conversion']['macaddr']));
    }

    return 0;
  }
  function doTransactionid ($appsigid, $publisher_id, $data)
  {
    if ($this->isExistsField ($data['Conversion']['transactionid']))
    {
      return $this->isInstalledTargetPublisher ($appsigid,
                                                array ('Click.appsigid' => $appsigid,
                                                       'Click.publisher_id' => $publisher_id,
                                                       'Click.publisher_click_id' => $data['Conversion']['transactionid']));
    }

    return 0;
  }

  function isInstalledTargetPublisher ($appsigid, $conditions)
  {
    $this->click_table_name = $appsigid."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);

    $datas = $this->Click->find ('count', array (
      'conditions' => $conditions));

    if ($datas > 1)
    {
      // It's erro because it found more than 1 record. It's illegal.
      // For now it returns 1;
      $this->log ("Found more than 1 click datas", LOG_DEBUG);
      return 1;
    }

    return $datas;
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
      $this->AdminAnalyzeCreative->create ();

      $field = array (
        'publisher_id' => $ret['publisher_id'],
        'advertiser_id' => $ret['advertiser_id'],
        'appsigid' => $ret['appsigid'],
        'creative_id' => $ret['creative_id'],
        'target_date' => $ret['target_date'],
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

      $this->AdminAnalyzeCreative->set ($field);
      $this->AdminAnalyzeCreative->save ();
    }
  }

  function isExistsAnalyzeData ($ret)
  {
    $datas = $this->AdminAnalyzeCreative->find ('all', array (
      'conditions' => array ('AdminAnalyzeCreative.publisher_id' => $ret['publisher_id'],
                             'AdminAnalyzeCreative.advertiser_id' => $ret['advertiser_id'],
                             'AdminAnalyzeCreative.appsigid' => $ret['appsigid'],
                             'AdminAnalyzeCreative.creative_id' => $ret['creative_id'])));
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
