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
php /usr/local/nginx/cakeAdmin/app/Console/cake.php ExportAnalyzeDataLoop /usr/local/nginx/cakeAdmin/app

シェル名の後に任意のメソッドを指定できる
php /usr/local/nginx/cakeAdmin/app/Console/cake.php ExportAnalyzeData test /usr/local/nginx/cakeAdmin/app

php /usr/local/nginx/cakeAdmin/app/Console/cake.php ExportAnalyzeData specifiedDate 2013-03-10 -app /usr/local/nginx/cakeAdmin/app
パラメータを渡すことも可能
*/


App::uses('Shell', 'Console');

class ExportAnalyzeDataLoopShell extends Shell
{
  var $uses = array('CampaignMaster', 'Click', 'Conversion', 'AdminAnalyzePerPublisher');

  var $target_utc_date;
  var $target_pst_date;

  var $begin_pst_time;
  var $end_pst_time;

  var $fp;
  var $file_name;

  function startup ()
  {
    $this->log (Configure::version(), LOG_DEBUG);

    Configure::write ('debug', 2);
    //$this->file_name = "phoenix0906_0910_2.tsv";
    //$debug = Configure::read ('debug');

    /*
    // File time zone should be UTC
    $this->setTimezoneUTC ();

    // But we have to get PST time, so returing original time zone
    $this->setTimezonePST ();
      */
  }
  function setTimezoneUTC ()
  {
    // File time zone should be UTC
    date_default_timezone_set ('UTC');

    $this->target_utc_date = date ("Y-m-d");

    //$file_dir = sprintf ("/mnt/s3/%s/", date ("Y/m/d/H", strtotime ("-1 hour")));

    //$cmd = "mkdir -p $file_dir";
    //$this->log ("cmd = $cmd", LOG_DEBUG);
    shell_exec ($cmd);

    //$this->file_name = $file_dir.sprintf ("report_%s.txt", date ("Ymd_H", strtotime ("-1 hour")));
    //$this->log ("file_name = $this->file_name", LOG_DEBUG);

    $this->begin_utc_time = date ("Y-m-d H:00:00", strtotime ("-1 hour"));
  }
  function setTimezonePST ()
  {
    // But we have to get PST time, so returing original time zone
    date_default_timezone_set('America/Los_Angeles');

    $this->target_pst_date = date ("Y-m-d");

    $this->begin_pst_time = date ("Y-m-d H:00:00", strtotime ("-1 hour"));
    $this->end_pst_time = date ("Y-m-d H:59:59", strtotime ("-1 hour"));
    $this->User = ClassRegistry::init ('User');
  }
  public function main ()
  {
    $this->fp = fopen ($this->file_name, "w");

    for ($i = 1; $i >= 0; $i--)
    {
      for ($j = 0; $j <= 24; $j++)
      {
        //$this->log ($i, LOG_DEBUG);
        //$this->log ($j, LOG_DEBUG);

        {
          // File time zone should be UTC
          date_default_timezone_set ('UTC');

          $this->target_utc_date = date ("Y-m-d", strtotime ("-$i day -$j hour"));
          $file_dir = sprintf ("/mnt/s3/%s/", date ("Y/m/d/H", strtotime ("-$i day -$j hour")));

          $cmd = "mkdir -p $file_dir";
          $this->log ("cmd = $cmd", LOG_DEBUG);
          shell_exec ($cmd);

          $this->file_name = $file_dir.sprintf ("report_%s.txt", date ("Ymd_H", strtotime ("-$i day -$j hour")));
          $this->log ("file_name = $this->file_name", LOG_DEBUG);

          $this->begin_utc_time = date ("Y-m-d H:00:00", strtotime ("-$i day -$j hour"));
        }

        {
          // But we have to get PST time, so returing original time zone
          date_default_timezone_set('America/Los_Angeles');

          $this->target_pst_date = date ("Y-m-d", strtotime ("-$i day"));

          $this->begin_pst_time = date ("Y-m-d H:00:00", strtotime ("-$i day -$j hour"));
          $this->end_pst_time = date ("Y-m-d H:59:59", strtotime ("-$i day -$j hour"));
          $this->User = ClassRegistry::init ('User');
        }

        $data = $this->getExistCampaign ();
      }
    }
    fclose ($this->fp);
  }

  function getExistCampaign ()
  {
    $datas = $this->CampaignMaster->find ('all', array (
      'conditions' => array (array ('LEFT (CampaignMaster.end_time, 10) >=' => $this->target_pst_date)),
      'order' => array('CampaignMaster.end_time' => 'DESC')));

    //echo $this->sqlDump ();
    //$this->log ($datas, LOG_DEBUG);
    $result = array ();
    foreach ($datas as $data)
    {
      if ($data['CampaignMaster']['advertiser_id'] === '100000000') { continue; }
      if ($data['CampaignMaster']['id'] === '2045444883139e79c4246183595c2df2613d6192') { continue; }
      if ($data['CampaignMaster']['id'] === '40b247a5c58ea510c773942a6ba0aa3a7467cc35') { continue; }

      $this->getData ($data['CampaignMaster']['id']);
    }
    //echo $this->sqlDump ();
    return $result;
  }

  function getData ($appsigid)
  {
    //$this->log ($this->begin_utc_time, LOG_DEBUG);
    $this->log ($this->begin_pst_time, LOG_DEBUG);

    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'conditions' => array ('AdminAnalyzePerPublisher.appsigid' => $appsigid,
                             'AdminAnalyzePerPublisher.target_datetime' => $this->begin_pst_time)));

    $result = array ();
    foreach ($datas as $data)
    {
      $key = $data['AdminAnalyzePerPublisher']['target_datetime'];
      if (!array_key_exists ($key, $result))
      {
        $result{$key} = $data['AdminAnalyzePerPublisher'];
        $result{$key}['cpi'] = $result{$key}['cpi'] * 1000000;
        continue;
      }

      $result{$key}['click_num'] += $data['AdminAnalyzePerPublisher']['click_num'];
      $result{$key}['install_num'] += $data['AdminAnalyzePerPublisher']['install_num'];
      $result{$key}['sales'] += $data['AdminAnalyzePerPublisher']['sales'];
    }

    $this->writeFile ($result);
  }

  function writeFile ($datas)
  {
    foreach ($datas as $data)
    {
      if ((int)($data['click_num']) == 0 && (int)($data['install_num']) == 0) { continue; }

      $l = $this->begin_utc_time."\t".$data['advertiser_id']."\t".$data['appsigid']."\t".$data['click_num']."\t".$data['install_num']."\t".$data['cpi']."\n";
      $this->log ($l, LOG_DEBUG);
      fwrite ($this->fp, $l);
    }
  }

  function getClicks ($data)
  {
    $this->click_table_name = $data['CampaignMaster']['id']."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);

    $datas = $this->Click->find ('all', array (
      'conditions' => array ('Click.appsigid' => $data['CampaignMaster']['id'],
                             array ('Click.created >=' => $this->begin_pst_time),
                             array ('Click.created <=' => $this->end_pst_time))));

    //echo $this->sqlDump ();
    return $this->getActualRecordNum ('Click', $datas);
  }

  function getConversions ($data)
  {
    $datas = $this->Conversion->find ('all', array (
      'conditions' => array ('Conversion.appsigid' => $data['CampaignMaster']['id'],
                             array ('Conversion.created >=' => $this->begin_pst_time),
                             array ('Conversion.created <=' => $this->end_pst_time))));

    //echo $this->sqlDump ();
    return $this->getActualRecordNum ('Conversion', $datas);
  }

  function getActualRecordNum ($table_name, $datas)
  {
    $result = array ();
    /*
    if ($table_name === 'Conversion')
    {
      $this->log ($this->begin_utc_time, LOG_DEBUG);
      $this->log ($this->begin_pst_time, LOG_DEBUG);
    }
    */
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

    /*
    if ($table_name === 'Conversion')
    {
      $this->log ($result, LOG_DEBUG);
    }
    */
    //$this->log (count ($result), LOG_DEBUG);
    return count ($result);
  }
  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ()."\n";
  }
}
