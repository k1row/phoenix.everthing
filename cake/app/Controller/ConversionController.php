<?php

App::uses('AppController', 'Controller');

class ConversionController extends AppController
{
  var $name = 'Conversion';
  public $uses = array('Conversion');

  var $now;
  var $click_table_name = "";

  var $appsigid = "";
  var $advertiser_id = "";

  var $campaign_start = "";
  var $campaign_end = "";

  var $is_ios_campaign = 0;
  var $is_android_campaign = 0;

  // set default variables
  var $idfa = "";
  var $idfamd5 = "";
  var $idfasha1 = "";

  var $dpidraw = "";
  var $dpidmd5 = "";
  var $dpidsha1 = "";

  var $openudid = "";
  var $macaddr = "";

  var $transactionid = ""; // click related unique id

  var $geoid = "";

  var $model = "";
  var $sysname = "";
  var $sysver = "";
  var $appid = "";
  var $appver = "1.1";
  var $apiver = "1.1";

  var $created = "";
  var $modified = "";
  var $error = "";

  var $clicked_record = null;

  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ();
  }

  function errorFinish ($errorMsg)
  {
    $this->errorLog ($errorMsg);
    //print ("200 NG: Reasons<br />" . $errorMsg. "<br>");
    header ("HTTP/1.1 400 Bad Reques\r\n");
    print ("Content-type: text/html\r\n");
    print ("\r\n");
    print ("<html>\r\n");
    print ("<body>\r\n");
    print ($errorMsg."\r\n");
    print ("</body>\r\n");
    print ("</html>\r\n");
    exit;
  }

  function finish ($msg)
  {
    $this->errorLog ($msg);
    header ("HTTP/1.1 200 OK\r\n");
    print ("Content-type: text/htmlr\n");
    print ("\r\n");
    print ("<html>\r\n");
    print ("<body>\r\n");
    print ($msg."\r\n");
    print ("</body>\r\n");
    print ("</html>\r\n");
    exit;
  }

  function errorLog ($str)
  {
    $str = "ConversionController.php [".date('Y-m-d H:i:s')."] ".$str;
    error_log ($str."\n", 3, '/var/log/nginx/error.log');
  }

  public function index()
  {
    $this->autoRender = false;
    $params = $this->params['url'];

    // set current time and time zone
    $this->now = date('Y-m-d H:i:s', time());

    // set the time variables to the current time
    $this->created = $this->now;
    $this->modified = $this->now;

    // load models
    $this->loadModel('CampaignMaster');
    $this->loadModel('PublisherMaster');
    $this->loadModel('Click');
    $this->loadModel('PublisherDetail');
    $this->loadModel('MobpartnerCampaign');

    // error checking
    $check = 1;
    $device_id = 0;
    $conversion = 0;
    $dupe = 0;

    // check appsigid
    if(!isset ($this->request->query['appsigid']) && !($this->request->query['appsigid']))
    {
      return $this->errorFinish ("No appsigid");
    }

    $this->appsigid = $this->request->query['appsigid'];

    if (!($this->initCheckCampaign ()))
    {
      return $this->errorFinish ("Campaign Isn't Running ($this->appsigid)");
    }

    // Check received device data
    if (!$this->checkAllDeviceID ())
    {
      return $this->errorFinish ("No Device ID Given");
    }

    // Process other request paramter
    $this->checkOtherParameter ();

    // If the conversion blongs to Mobpartner, checking to match campagin_id
    if ($this->mobpartner ())
    {
      if (!isset ($this->request->query['tid1']) || !($this->request->query['tid1']))
      {
        return $this->errorFinish ("Require campaigin_id parameter in this appsigid ($this->appsigid)");
      }
    }
    list ($conversion, $found_duplicate) = $this->findValidClick ();

    // run if click was found
    if($conversion == 0)
    {
      if($found_duplicate == 0)
      {
        return $this->finish ("No Click Found ($this->appsigid)");
      }
      return $this->finish ("Duplicate Conversion Found ($this->appsigid)");
    }

    $this->appendConversion ();

    // check if there is a publisher ID set in the click and if there should be a postback
    if (isset ($this->clicked_record["Click"]["publisher_id"]))
    {
      $this->doPublisherPostback ();
    }

    $this->finish ("Conversion successed");
  }

  function mobpartner ()
  {
    if ($this->appsigid === '22f156736e2e28455a54fce2f0a81c5900a25412')
      return 1;

    return 0;
  }

  function initCheckCampaign ()
  {
    $data = $this->CampaignMaster->find ('all', $params = array(
      'conditions' => array ('id' => $this->request->query['appsigid'])));

    if(isset($data[0]["CampaignMaster"]) &&
       ($data[0]["CampaignMaster"]["begin_time"] < $this->now &&
        $data[0]["CampaignMaster"]["end_time"] > $this->now))
    {
      $this->advertiser_id = $data[0]["CampaignMaster"]["advertiser_id"];

      $this->campaign_start = $data[0]["CampaignMaster"]["begin_time"];
      $this->campaign_end = $data[0]["CampaignMaster"]["end_time"];

      // We might need check appsigid's format or length
      $this->appsigid = $this->request->query['appsigid'];

      if ($data[0]["CampaignMaster"]["device"] === "ios")
      {
        $this->is_ios_campaign = 1;
      }
      elseif ($data[0]["CampaignMaster"]["device"] === "android")
      {
        $this->is_android_campaign = 1;
      }

      //print ("is_ios_campaign = $this->is_ios_campaign<br>");
      //print ("is_android_campaign = $this->is_android_campaign<br>");
      return 1;
    }
    return 0;
  }

  function checkAllDeviceID ()
  {
    $device_id = 0;

    // check idfa
    if (isset ($this->request->query['idfa']) && $this->request->query['idfa'])
    {
      $this->idfa = $this->request->query['idfa'];
      $device_id = 1;

      // Currently we can NOT recieve idfa-md5 and idfa-sha1, so we make it here to compare.
      $this->idfamd5 = md5 ($this->idfa);
      $this->idfasha1 = sha1 ($this->idfa);

      $this->errorLog ("appsigid=$this->appsigid : idfa=$this->idfa");
    }

    // check idfamd5
    if(isset($this->request->query['idfamd5']) && $this->request->query['idfamd5'])
    {
      $this->dpidmd5 = $this->request->query['idfamd5'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : idfamd5=$this->idfamd5");
    }
    else
    {
      if (isset ($this->dpidraw) && $this->dpidraw) { $this->idfamd5 = md5 ($this->idfa); }
    }

    // check dpidsha1
    if(isset($this->request->query['idfasha1']) && $this->request->query['idfasha1'])
    {
      $this->idfasha1 = $this->request->query['idfasha1'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : idfasha1=$this->idfasha1");
    }
    else
    {
      if (isset ($this->dpidraw) && $this->dpidraw) { $this->idfasha1 = sha1 ($this->idfasha1); }
    }

    /* Temporary special 0523 For Dark Summoner (iOS) */
    if ($this->appsigid === '5730e8d62eb8e2f2446356897032f74e01928071' &&
        !$this->idfa && isset($this->request->query['udidraw']))
    {
      $this->errorLog ($this->request->query['udidraw']);

      $this->idfa = $this->request->query['udidraw'];
      $device_id = 1;

      // Currently we can NOT recieve idfa-md5 and idfa-sha1, so we make it here to compare.
      $this->idfamd5 = md5 ($this->idfa);
      $this->idfasha1 = sha1 ($this->idfa);
    }

    // check dpidraw
    if(isset($this->request->query['dpidraw']) && $this->request->query['dpidraw'])
    {
      $this->dpidraw = $this->request->query['dpidraw'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : dpidraw=$this->dpidraw");
    }

    // check dpidmd5
    if(isset($this->request->query['dpidmd5']) && $this->request->query['dpidmd5'])
    {
      $this->dpidmd5 = $this->request->query['dpidmd5'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : dpidmd5=$this->dpidmd5");
    }
    else
    {
      if (isset ($this->dpidraw) && $this->dpidraw) { $this->dpidmd5 = md5 ($this->dpidraw); }
    }

    // check dpidsha1
    if(isset($this->request->query['dpidsha1']) && $this->request->query['dpidsha1'])
    {
      $this->dpidsha1 = $this->request->query['dpidsha1'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : dpidsha1=$this->dpidsha1");
    }
    else
    {
      if (isset ($this->dpidraw) && $this->dpidraw) { $this->dpidsha1 = sha1 ($this->dpidraw); }
    }

    // check openudid
    if(isset($this->request->query['openudid']) && $this->request->query['openudid'])
    {
      $this->openudid = $this->request->query['openudid'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : openudid=$this->openudid");
    }

    // check mac address
    if(isset($this->request->query['macaddr']) && $this->request->query['macaddr'])
    {
      if(strlen($this->request->query['macaddr']) <= 255)
      {
        $this->macaddr = $this->request->query['macaddr'];
        $device_id = 1;
        $this->errorLog ("appsigid=$this->appsigid : maccaddr=$this->macaddr");
      }
    }

    // check transactionid
    if(isset($this->request->query['transactionid']) && $this->request->query['transactionid'])
    {
      $this->transactionid = $this->request->query['transactionid'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : transactionid=$this->transactionid");
    }

    return $device_id;
  }

  function checkOtherParameter ()
  {
    if(isset($this->request->query['click_id']) && $this->request->query['click_id'])
    {
      $this->publisher_click_id = $this->request->query['click_id'];
    }
    elseif(isset($this->request->query['clickid']))
    {
      $this->publisher_click_id = $this->request->query['clickid'];
    }

    // check geoid
    if(isset($this->request->query['geoid']) && $this->request->query['geoid'])
    {
      $this->geoid = $this->request->query['geoid'];
    }

    // check model
    if(isset($this->request->query['model']) && $this->request->query['model'])
    {
      $this->model = $this->request->query['model'];
    }

    // check sysname
    if(isset($this->request->query['sysname']) && $this->request->query['sysname'])
    {
      $this->sysname = $this->request->query['sysname'];
    }

    // check sysver
    if(isset($this->request->query['sysver']) && $this->request->query['sysver'])
    {
      $this->sysver = $this->request->query['sysver'];
    }

    // check appid
    if(isset($this->request->query['appid']) && $this->request->query['appid'])
    {
      $this->appid = $this->request->query['appid'];
    }

    // check apiver
    if(isset($this->request->query['apiver']) && $this->request->query['apiver'])
    {
      $this->apiver = $this->request->query['apiver'];
    }
  }

  function sign_v4($params, $secret)
  {
    if ($this->is_ios_campaign)
    {
      print ("is_ios_campaign<br>");
      $sig_keys = array('device', 'd_ifa', 'd_mac', 'token', 'nonce');
    }
    elseif ($this->is_android_campaign)
    {
      print ("is_android_campaign<br>");
      $sig_keys = array('device', 'token', 'nonce');
    }

    $sig_params = array();
    foreach ($sig_keys as $key)
    {
      if (array_key_exists($key, $params))
      {
        array_push ($sig_params, $params[$key]);
      }
    }
    $sig = implode (':', $sig_params);
    print "sig=".$sig."<br>";
    $sig = base64_encode (hash_hmac("sha1", $sig, $secret, true));
    $sig = str_replace (array ('+', '/', '='), array ('-', '_', ''), $sig);
    return $sig;
  }

  function doPlayHaven ($postback_url)
  {
    $this->errorLog ("doPlayHaven");
    print $this->idfa."<br>";
    print $this->dpidraw."<br>";
    print $this->macaddr."<br>";

    $data = $this->CampaignMaster->find ('all', $params = array(
      'conditions' => array ('id' => $this->request->query['appsigid'])));

    $app_secret = $data[0]["CampaignMaster"]["playhaven_secret"];
    $token = $data[0]["CampaignMaster"]["playhaven_token"];

    $this->errorLog ("app_secret=$app_secret");
    $this->errorLog ("token=$token");

    $nonce = '5d4g5s2c1';  // Just ramdom
    $params = array(
      'token'  => $token,
      'nonce'  => $nonce,
      );

    if ($this->is_ios_campaign)
    {
      $params['device'] = $this->dpidraw;
      $params['d_ifa'] = $this->idfa;
      $params['d_mac'] = str_replace (":", "", $this->macaddr);
    }
    elseif ($this->is_android_campaign)
    {
      $params['device'] = $this->dpidraw;
    }
    $params['sig4'] = $this->sign_v4 ($params, $app_secret);
    $url = $postback_url . http_build_query ($params);
    print $url."<br>";
    $this->errorLog ($url);
    $ch = curl_init ($url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec ($ch);
    curl_close ($ch);
    print $result."<br>";
    $this->errorLog ($result);
  }

  function findValidClick ()
  {
    $conversion = 0;
    $found_duplicate = 0;

    // save data
    // check install against clicks
    if(!$conversion && $this->idfa != "")
    {
      $this->errorLog ("check using IDFA = ($this->idfa)");
      $conversion = $this->isInstallAgainstClick ("idfa", $this->idfa);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("idfa", $this->idfa))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
      else
      {
        // Because we can recieve only raw IDFA as conversion request, but we can recieve IDFA-MD5 or IDFA-SHA1.
        list ($conversion, $found_duplicate) = $this->checkForExsitingHashIDFA ($this->idfamd5, $this->idfasha1);
      }
    }

    if (!$conversion && $this->idfamd5 != "")
    {
      $this->errorLog ("check using idfamd5 = ($this->idfamd5)");
      $conversion = $this->isInstallAgainstClick ("idfamd5", $this->idfamd5);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("idfamd5", $this->idfamd5))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    if (!$conversion && $this->idfasha1 != "")
    {
      $this->errorLog ("check using idfasha1 = ($this->idfasha1)");
      $conversion = $this->isInstallAgainstClick ("idfasha1", $this->idfasha1);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("idfasha1", $this->idfasha1))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    if (!$conversion && $this->dpidraw != "")
    {
      $this->errorLog ("check using dpidraw = ($this->dpidraw)");
      $conversion = $this->isInstallAgainstClick ("dpidraw", $this->dpidraw);

      /* We allow Android ID to duplicate.
      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("dpidraw", $this->dpidraw))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
      else
      {
        // Because we can recieve only raw AndroidID as conversion request, but we can recieve AndroidID-MD5 or AndroidID-SHA1.
        list ($conversion, $found_duplicate) = $this->checkForExsitingHashDpid ($this->dpidmd5, $this->dpidsha1);
      }
      */
    }

    if (!$conversion && $this->dpidmd5 != "")
    {
      $this->errorLog ("check using dpidmd5 = ($this->dpidmd5)");
      $conversion = $this->isInstallAgainstClick ("dpidmd5", $this->dpidmd5);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("dpidmd5", $this->dpidmd5))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    if (!$conversion && $this->dpidsha1 != "")
    {
      $this->errorLog ("check using dpidsha1 = ($this->dpidsha1)");
      $conversion = $this->isInstallAgainstClick ("dpidsha1", $this->dpidsha1);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("dpidsha1", $this->dpidsha1))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    if(!$conversion && $this->openudid != "")
    {
      $conversion = $this->isInstallAgainstClick ("openudid", $this->openudid);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("openudid", $this->openudid))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    if(!$conversion && $this->macaddr != "")
    {
      $conversion = $this->isInstallAgainstClick ("macaddr", $this->macaddr);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("macaddr", $this->macaddr))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    if(!$conversion && $this->transactionid != "")
    {
      $this->errorLog ("check using transactionid = ($this->transactionid)");

      /* transactionid means publisher_click_id in Click table  */
      $conversion = $this->isInstallAgainstClick ("publisher_click_id", $this->transactionid);

      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("transactionid", $this->transactionid))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    return array ($conversion, $found_duplicate);
  }

  function isInstallAgainstClick ($field, $value)
  {
    if (!isset ($value) || !$value)
      return 0;

    $conditions = array($field => $value,
                        'appsigid' => $this->appsigid);

    // If the conversion blongs to Mobpartner, checking to match campagin_id
    if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->request->query['tid1']; }

    $this->click_table_name = $this->appsigid."Click";
    // Change table
    $this->Click->setSource ($this->click_table_name);
    //$this->errorLog ("target click table = $this->click_table_name");

    $data = $this->Click->find('first', $params = array('conditions' => $conditions));

    if(isset ($data["Click"]["$field"]))
    {
      if(($this->campaign_start < $data["Click"]["created"]) &&
         ($this->campaign_end > $data["Click"]["created"]))
      {
        $this->clicked_record = $data;
        return 1;
      }
    }
    return 0;
  }

  function checkDuplicateInstall ($field, $value)
  {
    $conditions = array($field => $value,
                        'appsigid' => $this->appsigid);

    // If the conversion blongs to Mobpartner, checking to match campagin_id
    if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->request->query['tid1']; }

    $check_installs = $this->Conversion->find ('first', $params = array('conditions' => $conditions));

    if (isset ($check_installs["Conversion"]["appsigid"]))
    {
      if(($this->campaign_start < $check_installs["Conversion"]["created"]) &&
         ($this->campaign_end > $check_installs["Conversion"]["created"]))
      {
        $this->errorLog ("found duplicate install = $field -> $value");
        return 1;
      }
    }
    return 0;
  }

  function checkForExsitingHashIDFA ()
  {
    $conversion = $this->isInstallAgainstClick ("idfamd5", $this->idfamd5);
    $found_duplicate = 0;

    if ($conversion)
    {
      if ($this->checkDuplicateInstall ("idfamd5", $this->idfamd5))
      {
        $conversion = 0;
        $found_duplicate = 1;
      }
    }
    else
    {
      $conversion = $this->isInstallAgainstClick ("idfasha1", $this->idfasha1);
      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("idfasha1", $this->idfasha1))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    return array ($conversion, $found_duplicate);
  }

  function checkForExsitingHashDpid ()
  {
    $conversion = $this->isInstallAgainstClick ("dpidmd5", $this->dpidmd5);
    $found_duplicate = 0;

    if ($conversion)
    {
      $this->errorLog ("checkForExsitingHashDpid using dpidmd5 = ($this->dpidmd5)");
      if ($this->checkDuplicateInstall ("dpidmd5", $this->dpidmd5))
      {
        $conversion = 0;
        $found_duplicate = 1;
      }
    }
    else
    {
      $this->errorLog ("checkForExsitingHashDpid using dpidsha1 = ($this->dpidsha1)");
      $conversion = $this->isInstallAgainstClick ("dpidsha1", $this->dpidsha1);
      if ($conversion)
      {
        if ($this->checkDuplicateInstall ("dpidsha1", $this->dpidsha1))
        {
          $conversion = 0;
          $found_duplicate = 1;
        }
      }
    }

    return array ($conversion, $found_duplicate);
  }

  function appendConversion ()
  {
    $field = array(
      'appsigid' => $this->appsigid,
      'idfa' => $this->idfa,
      'idfamd5' => $this->idfamd5,
      'idfasha1' => $this->idfasha1,
      'dpidraw' => $this->dpidraw,
      'dpidmd5' => $this->dpidmd5,
      'dpidsha1' => $this->dpidsha1,
      'openudid' => $this->openudid,
      'macaddr' => $this->macaddr,
      'transactionid' => $this->transactionid,
      'geoid' => $this->geoid,
      'model' => $this->model,
      'sysname' => $this->sysname,
      'sysver' => $this->sysver,
      'appid' => $this->appid,
      'appver' => $this->appver,
      'apiver' => $this->apiver,
      'created' => $this->created,
      'modified' => $this->modified
      );

    if ($this->mobpartner ()) { $field['mobpartner_campaign_id'] = $this->request->query['tid1']; }

    // save the install to the database
    $this->Conversion->set($field);
    $this->Conversion->save();
  }

  function doPublisherPostback ()
  {
    if (!isset ($this->clicked_record["Click"]["publisher_id"]) || !($this->clicked_record["Click"]["publisher_id"]))
    {
      return $this->errorLog ("Publisher is NOT givven");
    }

    $clicked_appsigid = $this->clicked_record["Click"]["appsigid"];
    $clicked_publisher_id = $this->clicked_record["Click"]["publisher_id"];
    $clicked_ip = $this->clicked_record["Click"]["ip"];

    $this->errorLog ("start doPublisherPostback");
    $this->errorLog ("clicked_appsigid=$clicked_appsigid");
    $this->errorLog ("clicked_publisher_id=$clicked_publisher_id");

    // check database for the publisher
    $publiser_master = $this->PublisherMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $clicked_publisher_id,
        )));

    if(isset ($publiser_master[0]["PublisherDetail"]["id"]) && $publiser_master[0]["PublisherDetail"]["id"])
    {
      return $this->errorLog ("Couldn't find PublisherMaster table ($clicked_publisher_id)");
    }

    // check if the publisher ID exists, the publisher is set to enabled, and that there should be a postback
    if ($publiser_master[0]["PublisherMaster"]["enable"] != 1)
    {
      return $this->errorLog ("This publisher is NOT enable ($clicked_publisher_id)");
    }

    if ($publiser_master[0]["PublisherMaster"]["postback"] != 1)
    {
      return $this->errorLog ("This publisher postback flag is not available");
    }

    // Check publisher detail table
    list ($publisher_app_id, $publisher_app_str) = $this->showPublisherDetailTable ($clicked_publisher_id);

    // set the postback url
    $postback_url = $publiser_master[0]["PublisherMaster"]["url"];

    // check if publisher click id is set
    if(isset($this->clicked_record["Click"]["publisher_click_id"]))
    {
      $publisher_click_id = $this->clicked_record["Click"]["publisher_click_id"];
    }

    // If publisher is LifeStreet and Device is Android, the URL should be changed
    if (($publiser_master[0]["PublisherMaster"]["id"] == 200000014 ||  // LifeStreet
         $publiser_master[0]["PublisherMaster"]["id"] == 200000018 ||  // GreyStripe
         $publiser_master[0]["PublisherMaster"]["id"] == 200000030 ||  // Apsalar
         $publiser_master[0]["PublisherMaster"]["id"] == 200000042 ||  // Vungle
         $publiser_master[0]["PublisherMaster"]["id"] == 200000046)    // Fusepowered
        && $this->is_android_campaign)
    {
      $postback_url = $publiser_master[0]["PublisherMaster"]["url2"];
    }

    // We have to do special process when click is from PlayHaven
    if ($clicked_publisher_id == 200000012)
    {
      return $this->doPlayHaven ($postback_url, $this->idfa, $this->dpidraw, $this->macaddr);
    }

    if (($clicked_appsigid === 'ded505b7192f76bb7c588643e7cfb4a07965f6a1' ||
         $clicked_appsigid === 'bd788adceb0de1342d08f8d496515e0af034707f' ||
         $clicked_appsigid === 'efe7874fc5c5d76825325fc6cce26b4e508e32fb' ) &&
        $clicked_publisher_id == 200000020 &&
        $this->is_android_campaign)
    {
      // Because of we can only get dpidsha1 from D.O.T or B.B
      // So we have to get raw android id for publisher
      $this->dpidraw = $this->clicked_record["Click"]["dpidraw"];
    }

    $deviceid_raw = $this->is_android_campaign ? $this->dpidraw : $this->idfa;
    $deviceid_sha1 = $this->is_android_campaign ? $this->dpidsha1 : $this->idfasha1;
    $deviceid_md5 = $this->is_android_campaign ? $this->dpidmd5 : $this->idfamd5;

    if (!$deviceid_raw)
    {
      $deviceid_raw = $this->is_android_campaign ? $this->clicked_record["Click"]["dpidraw"] : $this->clicked_record["Click"]["idfa"];
    }
    if (!$deviceid_sha1)
    {
      $deviceid_sha1 = $this->is_android_campaign ? $this->clicked_record["Click"]["dpidsha1"] : $this->clicked_record["Click"]["idfasha1"];
    }
    if (!$deviceid_md5)
    {
      $deviceid_md5 = $this->is_android_campaign ? $this->clicked_record["Click"]["dpidmd5"] : $this->clicked_record["Click"]["idfamd5"];
    }

    // find and replace values in the postback url
    $postback_url = str_replace("_RRAAWW_", $deviceid_raw, $postback_url);     // Any Raw device id
    $postback_url = str_replace("SSHHAA11", $deviceid_sha1, $postback_url);    // Any SHA1 device id
    $postback_url = str_replace("_MMDD55_", $deviceid_md5, $postback_url);     // Any MD5 device id

    $postback_url = str_replace("IIDDFFAA", $this->idfa, $postback_url);       // only idfa raw
    $postback_url = str_replace("UUDDIIDD", $this->dpidraw, $postback_url);    // only android id raw
    $postback_url = str_replace("SHAANDID", $this->dpidsha1, $postback_url);   // only android sha1

    $postback_url = str_replace("UDIDOPEN", $this->openudid, $postback_url);   // openudid
    $postback_url = str_replace("MMAACCAA", $this->macaddr, $postback_url);    // mac address

    $postback_url = str_replace("CCAAMMPP", $publisher_app_id, $postback_url);   // publisher campaign/app id
    $postback_url = str_replace("PPSSTTRR", $publisher_app_str, $postback_url);  // publisher campaign/app srt
    $postback_url = str_replace("SSUUBBID", $publisher_click_id, $postback_url); // publisher click/sub id

    $postback_url = str_replace("__IIPP__", $clicked_ip, $postback_url);         // clicked ip address
    $postback_url = str_replace("AAPPIIDD", $this->appid, $postback_url);        // bundleid or package
    $postback_url = str_replace("TTIIMMEE", strtotime ($this->now), $postback_url);// Unix timestamp

    $this->doRequest ($postback_url);
  }

  function showPublisherDetailTable ($publisher_id)
  {
    $publisher_app_id = null;
    $publisher_app_str = null;

    // get publisher app id
    $data = $this->PublisherDetail->find('all', $params = array(
      'conditions' => array(
        'id' => $publisher_id,
        'appsigid' => $this->appsigid,
        )));

    if(isset ($data[0]["PublisherDetail"]["id"]) && $data[0]["PublisherDetail"]["id"])
    {
      $publisher_app_id = $data[0]["PublisherDetail"]["app_id"];
      $this->errorLog ("publisher_app_id=$publisher_app_id");

      $publisher_app_str = $data[0]["PublisherDetail"]["str"];
      $this->errorLog ("publisher_app_str=$publisher_app_str");
    }

    return array ($publisher_app_id, $publisher_app_str);
  }

  function doRequest ($postback_url)
  {
    $this->errorLog ("postback_url = ($postback_url)");
    $this->errorLog ("appisigid=($this->appsigid)");

    // postback logic
    $postback_curl = curl_init();

    curl_setopt($postback_curl, CURLOPT_URL, $postback_url);
    curl_setopt($postback_curl, CURLOPT_HEADER, 0);

    $result = curl_exec ($postback_curl);
    if ($result == false)
    {
      $this->errorLog ("Postback Failed");
    }

    curl_close($postback_curl);
  }
}
