
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
php /usr/local/nginx/cakeAdmin/app/Console/cake.php DivideClick /usr/local/nginx/cakeAdmin/app
*/


App::uses('Shell', 'Console');

class DivideClickShell extends Shell
{
  var $uses = array('Click', 'CampaignMaster');

  // オーバーライドして、Welcome to CakePHP･･･のメッセージを出さないようにする。
  function startup ()
  {
    $this->log (Configure::version(), LOG_DEBUG);

    Configure::write ('debug', 2);
    //$debug = Configure::read ('debug'); // 設定ファイルを読むこともできる
  }

  public function main ()
  {
    //$this->getClicks ('0af1553c641b3302234ef7936eaf868601a30fc2'); // Clash of Clans
    //$this->getClicks ('2045444883139e79c4246183595c2df2613d6192'); // iOS Test
    //$this->getClicks ('22f156736e2e28455a54fce2f0a81c5900a25412'); // MobPartner
    //$this->getClicks ('342ece6854b08044c8df6505b8ed26f88883b669'); // Dark Summoner (Android)
    //$this->getClicks ('40b247a5c58ea510c773942a6ba0aa3a7467cc35'); // Android Test
    //$this->getClicks ('5730e8d62eb8e2f2446356897032f74e01928071'); // Dark Summoner (iOS)
    //$this->getClicks ('8ce7ac50b9b784d93b16b23cb7a592b930490095'); // Ayakashi(iOS)
    //$this->getClicks ('a0de5ffe33f5392105adc26705a92078877c9e3b'); // Dark Rebirth (iOS)
    //$this->getClicks ('a80655f32fc8e3a4f7ab8e4995329d92081d788e'); // Ayakashi(Android)
    //$this->getClicks ('b766d5cbf434bb9a2925e740d4e9494358a2afb6'); // Dark Rebirth (Android)
    //$this->getClicks ('bd788adceb0de1342d08f8d496515e0af034707f'); // Blood Brothers (Android)
    //$this->getClicks ('ded505b7192f76bb7c588643e7cfb4a07965f6a1'); // DOT (Android)
    $this->getClicks ('efe7874fc5c5d76825325fc6cce26b4e508e32fb'); // Hell Fire (Android)
  }

  function getClicks ($appsigid)
  {
    $default_table = $this->Click->useTable;

    $camp = $this->CampaignMaster->find ('all', array (
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    foreach ($camp as $c)
    {
      if ($c['CampaignMaster']['id'] === '438803893250784a6f10052d087db8c0b656883a' ||
          $c['CampaignMaster']['id'] === '51f6b8d41dd51558f3877c2e351c48543e6221e6' ||
          $c['CampaignMaster']['id'] === '761a420765fb7b2ef7c7575bbda4f878567258e2' ||
          $c['CampaignMaster']['id'] === 'b2c80b95dbc8fb44894410fd1ab63a1a02589b60')
      {
        continue;
      }

      $datas = $this->Click->find ('all', array (
        'conditions' => array ('Click.appsigid' => $c['CampaignMaster']['id'])));

      $this->click_table_name = $c['CampaignMaster']['id']."Click";

      // Change table
      $this->Click->setSource ($this->click_table_name);
      $this->log ("Change Click Table for $this->click_table_name", LOG_DEBUG);

      $this->log ("Start", LOG_DEBUG);
      $this->log (count ($datas), LOG_DEBUG);
      foreach ($datas as $data)
      {

        $this->updateClick ($data);
      }
      $this->log ("End", LOG_DEBUG);

      // Change table
      $this->Click->setSource ($default_table);
    }
  }

  function updateClick ($data)
  {
    $this->Click->create ();

    $already_data = $this->isExists ($data);
    if ($already_data)
    {
      return;
    }

    $field = array (//'id' => $data['Click']['id'],
                    'appsigid' => $data['Click']['appsigid'],
                    'campaign_id' => $data['Click']['campaign_id'],
                    'creative_id' => $data['Click']['creative_id'],
                    'publisher_id' => $data['Click']['publisher_id'],
                    'publisher_click_id' => $data['Click']['publisher_click_id'],
                    'publisher_publisher_id' => $data['Click']['publisher_publisher_id'],
                    'publisher_category_id' => $data['Click']['publisher_category_id'],
                    'idfa' => $data['Click']['idfa'],
                    'idfamd5' => $data['Click']['idfamd5'],
                    'idfasha1' => $data['Click']['idfasha1'],
                    'dpidraw' => $data['Click']['dpidraw'],
                    'dpidmd5' => $data['Click']['dpidmd5'],
                    'dpidsha1' => $data['Click']['dpidsha1'],
                    'openudid' => $data['Click']['openudid'],
                    'macaddr' => $data['Click']['macaddr'],
                    'model' => $data['Click']['model'],
                    'sysname' => $data['Click']['sysname'],
                    'sysver' => $data['Click']['sysver'],
                    'apiver' => $data['Click']['apiver'],
                    'mobpartner_campaign_id' => $data['Click']['mobpartner_campaign_id'],
                    'created' => $data['Click']['created'],
                    'modified' => $data['Click']['modified'],
                    );

    //$this->log ($field, LOG_DEBUG);
    $this->Click->set ($field);
    $this->Click->save ();
  }

  function isExists ($data)
  {
    $datas = $this->Click->find ('all', array (
      'conditions' => array ('Click.appsigid' => $data['Click']['appsigid'],
                             'Click.campaign_id' => $data['Click']['campaign_id'],
                             'Click.creative_id' => $data['Click']['creative_id'],
                             'Click.publisher_id' => $data['Click']['publisher_id'],
                             'Click.publisher_click_id' => $data['Click']['publisher_click_id'],
                             'Click.publisher_publisher_id' => $data['Click']['publisher_publisher_id'],
                             'Click.publisher_category_id' => $data['Click']['publisher_category_id'],
                             'Click.idfa' => $data['Click']['idfa'],
                             'Click.idfamd5' => $data['Click']['idfamd5'],
                             'Click.idfasha1' => $data['Click']['idfasha1'],
                             'Click.dpidraw' => $data['Click']['dpidraw'],
                             'Click.dpidmd5' => $data['Click']['dpidmd5'],
                             'Click.dpidsha1' => $data['Click']['dpidsha1'],
                             'Click.openudid' => $data['Click']['openudid'],
                             'Click.macaddr' => $data['Click']['macaddr'],
                             'Click.model' => $data['Click']['model'],
                             'Click.sysname' => $data['Click']['sysname'],
                             'Click.sysver' => $data['Click']['sysver'],
                             'Click.apiver' => $data['Click']['apiver'],
                             'Click.mobpartner_campaign_id' => $data['Click']['mobpartner_campaign_id'],
                             'Click.created' => $data['Click']['created'],
                             'Click.modified' => $data['Click']['modified'])));

    return count ($datas) >= 1 ? $datas[0] : 0;
  }

  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ()."\n";
  }
}
