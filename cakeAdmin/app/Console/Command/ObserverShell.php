<?php

App::uses('Shell', 'Console');
App::uses('CakeEmail', 'Network/Email');

/*
php /usr/local/nginx/cakeAdmin/app/Console/cake.php Observer checkClick /usr/local/nginx/cakeAdmin/app
php /usr/local/nginx/cakeAdmin/app/Console/cake.php Observer checkConversion /usr/local/nginx/cakeAdmin/app
php /usr/local/nginx/cakeAdmin/app/Console/cake.php Observer checkAliveClickServer /usr/local/nginx/cakeAdmin/app
*/

class ObserverShell extends Shell
{
  var $uses = array('PublisherMaster', 'CampaignMaster', 'Click', 'Conversion');
  var $target_date;

  // オーバーライドして、Welcome to CakePHP･･･のメッセージを出さないようにする。
  function startup ()
  {
    $this->log (Configure::version(), LOG_DEBUG);

    Configure::write ('debug', 2);
    $this->User = ClassRegistry::init ('User');
    $this->target_date = date ("Y-m-d");
  }

  function isIgnoreCampaign ($appsigid)
  {
    if ($appsigid === 'efe7874fc5c5d76825325fc6cce26b4e508e32fb') { return 1; } // Hell Fire (Android)
    if ($appsigid === '5730e8d62eb8e2f2446356897032f74e01928071') { return 1; } // Dark Summoner (iOS)
    if ($appsigid === '433605618d588d46a0bb8bd185d1599ba9b523a3') { return 1; } // Shall we date?: Destiny Ninja(iOS)
    if ($appsigid === '0af1553c641b3302234ef7936eaf868601a30fc2') { return 1; } // Clash of Clans
    if ($appsigid === 'b0e7fba09543e473153f51e09810ebe1616a90b5') { return 1; } // Robinson's Island(Android)
    if ($appsigid === '28d8e63d3d9329f211502226220221a6a8aaa378') { return 1; } // Robinson's Island(iOS)
    if ($appsigid === '07b5ed46511dcc34507a546304f3366785758695') { return 1; } // Rage of Bahamut (Android)
    if ($appsigid === '98537c36b009b7164a71285d376f96073d9c4116') { return 1; } // Rage of Bahamut (iOS)
    if ($appsigid === '9c4592e6cba39fb528dbd0b329f4301ea861b398') { return 1; } // Empire: Four Kingdoms(iOS)
    if ($appsigid === '22f156736e2e28455a54fce2f0a81c5900a25412') { return 1; } // MobPartner

    return 0;
  }

  function getClickTimeInterval ($appsigid)
  {
    //$now = new DateTime ();
    //$now_str = $now->format('%h');

    // It's very weird if those apps don't get any clicks during the 10 minutes.

    // Those apps interval is 15 min
    if ($appsigid === 'ded505b7192f76bb7c588643e7cfb4a07965f6a1') { return 15; } // DOT (Android)
    if ($appsigid === '342ece6854b08044c8df6505b8ed26f88883b669') { return 15; } // Dark Summoner (Android)

    // Those apps interval is 30 min
    if ($appsigid === 'a80655f32fc8e3a4f7ab8e4995329d92081d788e') { return 30; } // Ayakashi (Android)
    if ($appsigid === '8ce7ac50b9b784d93b16b23cb7a592b930490095') { return 30; } // Ayakashi (iOS)

    // Those apps interval is 59 min
    if ($appsigid === 'bd788adceb0de1342d08f8d496515e0af034707f') { return 59; } // Blood Brothers (Android)
    if ($appsigid === '0a4ef3e31e5b801213ebd32a4a1040b64672e531') { return 59; } // Lord of the Dragons (Android)
    if ($appsigid === '95e3301db51085231d7463efca0fb65e2517b34b') { return 59; } // War of Legions (iOS)
  }


  function getConversionTimeInterval ($appsigid)
  {
    $now = new DateTime ();
    $now_str = intval ($now->format('h'));
    $this->log ($now_str, LOG_DEBUG);

    $addtional_interval = 0;

    if ($now_str >= 20 && $now_str <= 23)
    {
      $addtional_interval = 2;
    }
    if ($now_str >= 0 && $now_str <= 6)
    {
      $addtional_interval = 4;
    }

    // It's very weird if those apps don't get any clicks during the 1 hour.
    if ($appsigid === 'ded505b7192f76bb7c588643e7cfb4a07965f6a1') { return intval (0 + $addtional_interval); } // DOT (Android)

    if ($appsigid === '342ece6854b08044c8df6505b8ed26f88883b669') { return intval (1 + $addtional_interval); } // Dark Summoner (Android)

    // Those apps interval is 3 hour
    if ($appsigid === 'a80655f32fc8e3a4f7ab8e4995329d92081d788e') { return intval (3 + $addtional_interval); } // Ayakashi (Android)
    if ($appsigid === '8ce7ac50b9b784d93b16b23cb7a592b930490095') { return intval (3 + $addtional_interval); } // Ayakashi (iOS)

    // Those apps interval is 12 hour
    if ($appsigid === 'bd788adceb0de1342d08f8d496515e0af034707f') { return 12; } // Blood Brothers (Android)

    // Those apps interval is 24 hour
    if ($appsigid === '95e3301db51085231d7463efca0fb65e2517b34b') { return 23; } // War of Legions (iOS)
    if ($appsigid === '0a4ef3e31e5b801213ebd32a4a1040b64672e531') { return 23; } // Lord of the Dragons (Android)
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
      $insert_data['target_date'] = $this->target_date;
      $insert_data['advertiser_id'] = $data['CampaignMaster']['advertiser_id'];
      $insert_data['campaign_name'] = $data['CampaignMaster']['name'];
      $insert_data['expense'] = $data['CampaignMaster']['expense'];
      $insert_data['cpi'] = $data['CampaignMaster']['cpi'];
      $insert_data['device'] = $data['CampaignMaster']['device'];

      $insert_data['permit_transactionid_match'] = 0;
      if ($data['CampaignMaster']['smac'] == 1 || $data['CampaignMaster']['permit_transactionid_match'] == 1)
      {
        $insert_data['permit_transactionid_match'] = 1;
      }

      array_push ($result, $insert_data);
    }

    return $result;
  }

  function checkClick ()
  {
    $campaigns = $this->getExistCampaign ();
    $time1 = new DateTime ();
    $title = "ObserverShell::checkClick [".$time1->format('Y-m-d H:i:s')."] \n\n\n";
    $msg = "";

    $warning_count = 0;
    foreach ($campaigns as $c)
    {
      if ($this->isIgnoreCampaign ($c['appsigid'])) { continue; }

      $this->click_table_name = $c['appsigid']."Click";
      $this->Click->setSource ($this->click_table_name);

      $datas = $this->Click->find ('all', array (
        'conditions' => array ('Click.appsigid' => $c['appsigid']),
        'limit' => 1,
        'order' => array ('Click.created DESC')));

      if (count($datas))
      {
        $time2 = new DateTime ($datas[0]['Click']['created']);
        $diff = $time1->diff ($time2);

        $proper_interval = $this->getClickTimeInterval ($c['appsigid']);

        // If there is more than one hour time different, it would be warning.
        if (intval ($diff->format('%i')) > intval ($proper_interval))
        {
          $warning_count++;
          $msg .= $c['appsigid']." : ".$c['campaign_name']." -> ".$diff->format('%h hour %i min is passed when we got last click')."\n\n";
        }
      }
    }

    if ($warning_count)
    {
      $this->sendMail ($title, $msg);
    }
  }

  function checkConversion ()
  {
    $campaigns = $this->getExistCampaign ();
    $time1 = new DateTime ();
    $title = "ObserverShell::checkConversion [".$time1->format('Y-m-d H:i:s')."] \n\n\n";
    $msg = "";

    $warning_count = 0;
    foreach ($campaigns as $c)
    {
      if ($this->isIgnoreCampaign ($c['appsigid'])) { continue; }

      $datas = $this->Conversion->find ('all', array (
        'conditions' => array ('Conversion.appsigid' => $c['appsigid']),
        'limit' => 2,
        'order' => array ('Conversion.created DESC')));

      if (count($datas))
      {
        $time1 = new DateTime ();
        $time2 = new DateTime ($datas[0]['Conversion']['created']);
        $diff = $time1->diff ($time2);

        $proper_interval = $this->getConversionTimeInterval ($c['appsigid']);

        // If there is more than half hour time different, it would be warning.
        if (intval ($diff->format('%h')) > intval ($proper_interval))
        {
          $warning_count++;
          $msg .= $c['appsigid']." : ".$c['campaign_name']." -> ".$diff->format('%h hour %i min is passed when we got last conversion')."\n\n";
        }
      }
    }

    if ($warning_count)
    {
      $this->sendMail ($title, $msg);
    }
  }

  function checkAliveClickServer ()
  {
    $campaigns = $this->getExistCampaign ();
    $time1 = new DateTime ();
    $title = "ObserverShell::checkClick [".$time1->format('Y-m-d H:i:s')."] \n\n\n";

    $base_url = 'https://dsp-cl.amoad.net/';
    //$base_url = 'http://localhost/';

    $error_count = 0;
    $error_msg = "";

    foreach ($campaigns as $c)
    {
      if ($this->isIgnoreCampaign ($c['appsigid'])) { continue; }

      $data = array ();
      $data['appsigid'] = $c['appsigid'];
      $data['pubid'] = "200000000";

      if ($c['device'] === 'ios')
      {
        $data['idfa'] = "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA";
      }
      else
      {
        $data['dpidraw'] = "aaaaaaaaaaaaaaaa";
      }

      if ($c['appsigid'] === '07b5ed46511dcc34507a546304f3366785758695')
      {
        $data['creid'] = "ROB_movie_US_02";
      }
      if ($c['appsigid'] === '98537c36b009b7164a71285d376f96073d9c4116')
      {
        $data['creid'] = "attribute_ROB_0001_01_240x350_35k_AU_01";
      }
      if ($c['appsigid'] === '95e3301db51085231d7463efca0fb65e2517b34b')
      {
        $data['creid'] = "charaCB_LW_0001_01_120x120_40k";
      }
      if ($c['appsigid'] === 'd053dda4edf31dde61928aa948116f32fb77c78f')
      {
        $data['creid'] = "02_300x250";
      }

      $data['clickid'] = "1111111111111111111111111111111111111111";
      $data['ip'] = "127.0.0.1";
      $data['pubpid'] = "test_pubpid";
      $data['pubcatid'] = "test_pubcatid";

      list ($return_http_code, $exec_url) = $this->doPing ($base_url, $data);
      $this->log ("Return HTTP CODE = ".$return_http_code, LOG_DEBUG);
      if ($return_http_code)
      {
        $error_count++;
        $error_msg .= "HTTPCODE = ($return_http_code) -> $exec_url\n";
      }

      {
        $delete_condition;
        $delete_condition{"appsigid"} = $c['appsigid'];
        if ($c['device'] === 'ios')
        {
          $delete_condition{"idfa"} = "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA";
        }
        else
        {
          $delete_condition{"dpidraw"} = "aaaaaaaaaaaaaaaa";
        }

        //$this->deleteCheckingClick ($c['appsigid'], $delete_condition);
      }
    }

    if ($error_count)
    {
      $time1 = new DateTime ();
      $title = "ObserverShell::checkAliveClickServer [".$time1->format('Y-m-d H:i:s')."] \n\n\n";

      $this->sendMail ($title, $error_msg);
    }
  }

  function doPing ($url, &$data)
  {
    $fields_string = "";
    foreach ($data as $key => $value)
    {
      $fields_string .= $key.'='.$value.'&';
    }
    $fields_string = rtrim ($fields_string, "&");

    $url .= "?".$fields_string;

    $this->log ($url, LOG_DEBUG);

    //open connection
    $ch = curl_init ();

    $user_agent = "Mozilla/5.0 (Linux; U; Android 4.3; ja-jp; Nexus 4 Build/JWR66V) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30";
    if (isset ($data{'idfa'}))
    {
      $user_agent = "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_4 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Mobile/10B350";
    }

    //set the url, number of POST vars, POST data
    $options = array(
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER         => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_AUTOREFERER    => true,
      CURLOPT_CONNECTTIMEOUT => 120,
      CURLOPT_TIMEOUT        => 5,
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_USERAGENT      => $user_agent,
      );

    curl_setopt_array($ch, $options);
    $response = curl_exec ($ch);
    $httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);

    $info = curl_getinfo ($ch);
    curl_close ($ch);

    if ($info{'http_code'} == 301 || $info{'http_code'} == 302 || $info{'http_code'} == 502)
    {
      $this->log ($info{'url'}, LOG_DEBUG);

      if (preg_match ('/^market\:\/\/details\?id\=/', $info{'url'}))
      {
        return array (0, "");
      }

      if ((preg_match ('/^itms-appss\:\/\/itunes/', $info{'url'})) ||
          (preg_match ('/^itmss\:\/\/itunes\.apple\.com\//', $info{'url'})))
      {
        return array (0, "");
      }
    }

    if ($info{'http_code'} != 200)
    {
      $this->log ($info, LOG_DEBUG);
      return array ($info{'http_code'}, $url);
    }

    return array (0, "");
  }

  function deleteCheckingClick ($appsigid, $conditions)
  {
    $this->click_table_name = $appsigid."Click";
    $this->Click->setSource ($this->click_table_name);

    $this->log ($conditions, LOG_DEBUG);
    $this->Click->deleteAll ($conditions);
  }

  function sendMail ($title, $msg)
  {
    $this->log ("Warning mail is sending ...", LOG_DEBUG);
    $this->log ($title.$msg, LOG_DEBUG);

    $email = new CakeEmail ('ses');
    $mailRespons = $email->config (array ('log' => 'emails'))
      //->template('text_mail', 'text_layout')
      //->viewVars($body)
      ->from (array ('phoenix-observer@amoad.net' => 'phoenix-observer@amoad.net'))
        ->to ('k.nagashima@cyberagentamerica.com')
          ->subject ($title)
            ->send ($msg);
    debug($mailRespons);
  }
}
