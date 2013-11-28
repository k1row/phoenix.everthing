<?php

/*
���ۂ�Terminal������s����ꍇ�́A
/usr/bin/php /CakePHP�̃p�X/app/Console/cake.php Hoge -app /CakePHP�̃p�X/app/

1) /usr/bin/php                        ��� php�܂ł̃p�X
2) /CakePHP�̃p�X/app/Console/cake.php ��� cake.php�܂ł̃p�X(�Œ�j
3) Hoge                                ��� Shell.php���������V�F����
4) -app                                ��� app�R�}���h
5) /CakePHP�̃p�X/app/                 ��� app�܂ł̃p�X(�Œ�j

���\�b�h�����w�肵�Ȃ��ꍇ�����o���ɁAmain()���\�b�h���Ăяo�����
php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeCampaign /usr/local/nginx/cakeAdmin/app

�V�F�����̌�ɔC�ӂ̃��\�b�h���w��ł���
php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeCampaign test /usr/local/nginx/cakeAdmin/app

php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeCampaign specifiedDate 2013-03-10 -app /usr/local/nginx/cakeAdmin/app
�p�����[�^��n�����Ƃ��\
*/



/*

CREATE TABLE `admin_analyze_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advertiser_id` int(11) NOT NULL,
  `appsigid` int(11) NOT NULL,
  `begin_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `click_num` smallint(6) NOT NULL,
  `install_num` smallint(6) NOT NULL,
  `cvr` double(4,2) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_index` (`appsigid`, `begin_time`, `end_time`),
  KEY `appsigid` (`appsigid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

*/

App::uses('Shell', 'Console');

class AnalyzeCampaignShell extends Shell
{
  var $uses = array('AdvertiserMaster', 'CampaignMaster', 'Click', 'Conversion', 'AdminAnalyzeCampaign');
  var $target_date;

  // �I�[�o�[���C�h���āAWelcome to CakePHP����̃��b�Z�[�W���o���Ȃ��悤�ɂ���B
  function startup ()
  {
    $this->log (Configure::version(), LOG_DEBUG);

    Configure::write ('debug', 2);
    //$debug = Configure::read ('debug'); // �ݒ�t�@�C����ǂނ��Ƃ��ł���
    $this->target_date = date ("Y-m-d", strtotime ("-1 day"));
    $this->User = ClassRegistry::init ('User');
  }

  public function main ()
  {
    $campaign_result = $this->getExistCampaign ();
    $this->insertAnalyzeData ($campaign_result);
  }
  public function specifiedDate ()
  {
    $this->log ($this->args[0], LOG_DEBUG);

    // �����������ȓ��t�ō������ꍇ�W�v�f�[�^����ύX
    if (!empty ($this->args[0]))
    {
      if (!$this->isValidDate ($this->args[0]))
      {
        echo 'Invalid arg = '.$this->args[0]."\n";
        echo 'Usage : php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeCampaign specifiedDate 2013-03-10 -app /usr/local/nginx/cakeAdmin/app'."\n";
        exit;
      }

      $this->target_date = date ("Y-m-d", strtotime ($this->args[0]));
      $this->log ("target_date has changed = $this->target_date", LOG_DEBUG);
    }

    //$this->log ($this->target_date, LOG_DEBUG);

    $campaign_result = $this->getExistCampaign ();
    $this->insertAnalyzeData ($campaign_result);
  }

  function isValidDate ($input)
  {
    $date_format = 'Y-m-d';
    $input = trim ($input);
    $time = strtotime ($input);

    $is_valid = date ($date_format, $time) == $input;

    //print "Valid [$input] ? ".($is_valid ? 'yes' : 'no')."\n";
    return $is_valid ? true : false;
  }

  function getExistCampaign ()
  {
    /*
    $datas = $this->CampaignMaster->find ('all', array (
      'conditions' => array (array ('LEFT (CampaignMaster.begin_time, 10) >=' => "$this->target_date"),
                             array ('LEFT (CampaignMaster.end_time, 10) <=' => "$this->target_date"))));
     */

    $one_day_before = date ("Y-m-d", strtotime ("$this->target_date -1 day"));
    $datas = $this->CampaignMaster->find ('all', array (
      'conditions' => array (array ('LEFT (CampaignMaster.end_time, 10) >=' => $one_day_before))));

    //echo $this->sqlDump ();
    //$this->log ($datas, LOG_DEBUG);
    $result = array ();
    foreach ($datas as $data)
    {
      if ($data['CampaignMaster']['advertiser_id'] === '100000000') { continue; }
      if ($data['CampaignMaster']['id'] === '2045444883139e79c4246183595c2df2613d6192') { continue; }
      if ($data['CampaignMaster']['id'] === '40b247a5c58ea510c773942a6ba0aa3a7467cc35') { continue; }

      //$this->log (strtotime (substr ($data['CampaignMaster']['begin_time'], 0, 10)), LOG_DEBUG);
      //$this->log (strtotime ("$this->target_date"), LOG_DEBUG);

      if (strtotime (substr ($data['CampaignMaster']['begin_time'], 0, 10)) > strtotime ("$this->target_date"))
        continue;

      $insert_data;
      $insert_data['advertiser_id'] = $data['CampaignMaster']['advertiser_id'];
      $insert_data['appsigid'] = $data['CampaignMaster']['id'];
      $insert_data['begin_time'] = $data['CampaignMaster']['begin_time'];
      $insert_data['end_time'] = $data['CampaignMaster']['end_time'];
      $insert_data['click_num'] = $this->getClicks ($data);
      $insert_data['install_num'] = $this->getConversions ($data);
      $insert_data['cvr'] = round ($insert_data['install_num'] / $insert_data['click_num'], 2);

      array_push ($result, $insert_data);
    }
    return $result;
  }

  function getClicks ($data)
  {
    $begin_time = $data['CampaignMaster']['begin_time'];
    /*
    if (strtotime ("$this->target_date 00:00:00") >= strtotime ($data['CampaignMaster']['begin_time']))
      $begin_time = "$this->target_date 00:00:00";
     */

    $end_time = $data['CampaignMaster']['end_time'];
    /*
    if (strtotime ("$this->target_date 23:59:59") <= strtotime ($data['CampaignMaster']['end_time']))
      $end_time = "$this->target_date 23:59:59";
     */

    $this->click_table_name = $data['CampaignMaster']['id']."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);

    $datas = $this->Click->find ('all', array (
      'conditions' => array ('Click.appsigid' => $data['CampaignMaster']['id'],
                             array ('Click.created >=' => $begin_time),
                             array ('Click.created <=' => $end_time))));

    //echo $this->sqlDump ();
    return $this->getActualRecordNum ('Click', $datas);
  }

  function getConversions ($data)
  {
    $begin_time = $data['CampaignMaster']['begin_time'];

    /*
    if (strtotime ("$this->target_date 00:00:00") >= strtotime ($data['CampaignMaster']['begin_time']))
      $begin_time = "$this->target_date 00:00:00";
     */

    $end_time = $data['CampaignMaster']['end_time'];
    /*
    if (strtotime ("$this->target_date 23:59:59") <= strtotime ($data['CampaignMaster']['end_time']))
      $end_time = "$this->target_date 23:59:59";
     */

    $datas = $this->Conversion->find ('all', array (
      'conditions' => array ('Conversion.appsigid' => $data['CampaignMaster']['id'],
                             array ('Conversion.created >=' => $begin_time),
                             array ('Conversion.created <=' => $end_time))));

    //echo $this->sqlDump ();
    return $this->getActualRecordNum ('Conversion', $datas);
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
        //$this->log ('openudid ='.$data["$table_name"]['openudid'], LOG_DEBUG);
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

  function insertAnalyzeData ($campaign_result)
  {
    $this->log ('insertAnalyzeData', LOG_DEBUG);
    foreach ($campaign_result as $result)
    {
      //$this->log ($result, LOG_DEBUG);

      $this->AdminAnalyzeCampaign->create ();
      $field = array (
        'advertiser_id' => $result['advertiser_id'],
        'appsigid' => $result['appsigid'],
        'begin_time' => $result['begin_time'],
        'end_time' => $result['end_time'],
        'click_num' => $result['click_num'],
        'install_num' => $result['install_num'],
        'cvr' => $result['cvr'],
        );

      $already_data = $this->isExistsAnalyzeData ($result);
      if ($already_data)
      {
        $this->log ("already exist data!!!", LOG_DEBUG);
        $field['id'] = $already_data['AdminAnalyzeCampaign']['id'];
      }

      $this->AdminAnalyzeCampaign->set ($field);
      $this->AdminAnalyzeCampaign->save ();
    }
  }

  function isExistsAnalyzeData ($ret)
  {
    $datas = $this->AdminAnalyzeCampaign->find ('all', array (
      'conditions' => array ('AdminAnalyzeCampaign.advertiser_id' => $ret['advertiser_id'],
                             'AdminAnalyzeCampaign.appsigid' => $ret['appsigid'],
                             'AdminAnalyzeCampaign.begin_time' => $ret['begin_time'],
                             'AdminAnalyzeCampaign.end_time' => $ret['end_time'],
                             )));
    //echo $this->sqlDump ();
    return count ($datas) >= 1 ? $datas[0] : 0;
  }

  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ()."\n";
  }
}
