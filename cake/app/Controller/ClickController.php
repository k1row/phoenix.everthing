<?php

App::uses('AppController', 'Controller');

class ClickController extends AppController
{
  //var $name = 'Click';
  public $uses = array('Click');

  var $now;
  var $click_table_name = "";

  var $appsigid = "";
  var $campaign_id = "";
  var $creative_id = "";
  var $advertiser_id = "";

  var $campaign_start = "";
  var $campaign_end = "";

  var $is_ios_campaign = 0;
  var $is_android_campaign = 0;

  // error checking
  var $device_id = 0;

  // which 3rdParty is used.
  var $has_offers = 0;
  var $dena = 0;
  var $fox = 0;
  var $kochava = 0;
  var $ad_x = 0;
  var $smac = 0;
  var $tracking_affiliates = 0;
  var $zynga = 0;
  var $gmotech = 0;
  var $adven = 0;
  var $motive = 0;
  var $appflood = 0;
  var $advertiser_postback = 0;

  // set default variables
  var $pubid = "";
  var $pubpid = "";
  var $pubcatid = "";
  var $publisher_click_id = "";

  var $idfa = "";
  var $idfamd5 = "";
  var $idfasha1 = "";

  var $dpidraw = "";
  var $dpidmd5 = "";
  var $dpidsha1 = "";

  var $openudid = "";
  var $macaddr = "";

  var $geoid = "";
  var $ip = "";

  var $model = "";
  var $sysname = "";
  var $sysver = "";
  var $apiver = "1.1";

  // If you wanna use to mactch transactionid, the flag should be "1"
  var $permit_transactionid_match = 0;

  var $created = "";
  var $modified = "";

  // Campaign ID for Mobpartner campaign.
  var $mpid = "";

  var $last_insert_click_table_id;

  // for debug
  var $start_ticktime;

  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ();
  }

  function errorFinish ($errorMsg)
  {
    $this->errorLog ($errorMsg);
    header ("HTTP/1.1 400 Bad Reques\r\n");
    print ("Content-type: text/htmlr\n");
    print ("\r\n");
    print ("<html>\r\n");
    print ("<body>\r\n");
    print ($errorMsg."\r\n");
    print ("</body>\r\n");
    print ("</html>\r\n");
    exit;
  }

  function errorLog ($str)
  {
    $str = "ClickController.php [".date('Y-m-d H:i:s')."] ".$str;
    error_log ($str."\n", 3, '/var/log/nginx/error.log');
  }

  function measure()
  {
    list($m, $s) = explode(' ', microtime());
    return ((float)$m + (float)$s);
  }

  public function index()
  {
    $this->start_ticktime = $this->measure();

    $this->autoRender = false;

    // load models
    $this->loadModel('CampaignMaster');
    $this->loadModel('PublisherMaster');
    $this->loadModel('MobpartnerCampaign');

    $params = $this->params['url'];

    ini_set ("log_errors", "On");
    ini_set ("error_log", "/var/log/nginx/error.log" );

    // set time
    $this->now = date('Y-m-d H:i:s', time());

    // set the time variables to the current time
    $this->created = $this->now;
    $this->modified = $this->now;

    $check = 1;
    $error = "";

    // check appsigid
    if(!isset ($this->request->query['appsigid']) && !($this->request->query['appsigid']))
    {
      return $this->errorFinish ("No appsigid");
    }

    // We might need check appsigid's format or length
    $this->appsigid = $this->request->query['appsigid'];

    if ($this->mobpartner ())
    {
      if (!isset ($this->request->query['mpid']) || !($this->request->query['mpid']))
      {
        return $this->errorFinish ("No mpid givven");
      }
      $this->mpid = $this->request->query['mpid'];
      $this->errorLog ("mpid=$this->mpid");
    }

    $this->errorLog ("CHECK_POINT1 ".($this->measure() - $this->start_ticktime).PHP_EOL);

    if (!($this->initCheckCampaign ()))
    {
      return $this->errorFinish ("Campaign Isn't Running");
    }

    $this->errorLog ("CHECK_POINT2 ".($this->measure() - $this->start_ticktime).PHP_EOL);

    // check publisher id
    if(!isset ($this->request->query['pubid']) || !($this->request->query['pubid']))
    {
      return $this->errorFinish ("No Publisher ID");
    }
    $this->checkPublisherID ($this->request->query['pubid']);

    $this->errorLog ("CHECK_POINT3 ".($this->measure() - $this->start_ticktime).PHP_EOL);

    // Check received device data
    if (!$this->checkAllDeviceID ())
    {
      return $this->errorFinish ("No Correct Device ID Given");
    }

    $this->errorLog ("CHECK_POINT4 ".($this->measure() - $this->start_ticktime).PHP_EOL);

    // Process other request paramter
    $this->checkOtherParameter ();

    $this->errorLog ("CHECK_POINT5 ".($this->measure() - $this->start_ticktime).PHP_EOL);

    // Check click data
    $found_duplicate = $this->isExistSameClick ();
    $this->errorLog ("found_duplicate=$found_duplicate");

    if (!$found_duplicate)
    {
      $this->appendClick ();
    }

    $this->errorLog ("CHECK_POINT6 ".($this->measure() - $this->start_ticktime).PHP_EOL);

    $this->do3rdPartyTrack ();
    //$this->errorLog ($this->sqlDump ());
    //debug ($this->sqlDump ());
    print ("200 OK");
  }

  function appendClick ()
  {
    $field = array (
      'appsigid' => $this->appsigid,
      'campaign_id' => $this->campaign_id,
      'creative_id' => $this->creative_id,
      'publisher_id' => $this->pubid,
      'publisher_click_id' => $this->publisher_click_id,
      'publisher_publisher_id' => $this->pubpid,
      'publisher_category_id' => $this->pubcatid,
      'idfa' => $this->idfa,
      'idfamd5' => $this->idfamd5,
      'idfasha1' => $this->idfasha1,
      'dpidraw' => $this->dpidraw,
      'dpidmd5' => $this->dpidmd5,
      'dpidsha1' => $this->dpidsha1,
      'openudid' => $this->openudid,
      'macaddr' => $this->macaddr,
      'geoid' => $this->geoid,
      'ip' => $this->ip,
      'model' => $this->model,
      'sysname' => $this->sysname,
      'sysver' => $this->sysver,
      'apiver' => $this->apiver,
      'created' => $this->created,
      'modified' => $this->modified
      );

    if ($this->mobpartner ()) { $field['mobpartner_campaign_id'] = $this->mpid; }

    // save the click in the database
    $this->Click->set ($field);
    $this->Click->save ();
    $this->last_insert_click_table_id = $this->Click->getLastInsertID ();
  }

  function manualRollbackClick ()
  {
    $this->errorLog ("manualRollbackClick : DELETE FROM $this->click_table_name WHERE id = $this->last_insert_click_table_id");
    $this->Click->delete ($this->last_insert_click_table_id);
  }

  function initCheckCampaign ()
  {
    $this->errorLog ("CHECK_POINT (Before CampaignMaster) ".($this->measure() - $this->start_ticktime).PHP_EOL);

    $data = $this->CampaignMaster->find('all', $params = array(
      'fields' => array('id', 'advertiser_id', 'device', 'cpi', 'begin_time', 'end_time', 'has_offers', 'dena', 'fox', 'kochava', 'ad_x', 'smac', 'tracking_affiliates', 'zynga', 'gmotech', 'adven', 'motive', 'appflood', 'advertiser_postback', 'permit_transactionid_match'),
      'conditions' => array('id' => $this->appsigid),
      ));
    $this->errorLog ("CHECK_POINT (After CampaignMaster) ".($this->measure() - $this->start_ticktime).PHP_EOL);

    $error = 0;
    if (!isset ($data[0]["CampaignMaster"]) || !($data[0]["CampaignMaster"]))
    {
      $this->errorFinish ("Uninitialized campaign [no data] -> $this->appsigid");
      $error = 1;
    }

    // Those checks are excluded for MobPartner
    if (!$this->mobpartner ())
    {
      if (!($data[0]["CampaignMaster"]["device"] === "ios") && !($data[0]["CampaignMaster"]["device"] === "android"))
      {
        $this->errorFinish ("Uninitialized campaign [device] -> $this->appsigid");
        $error = 1;
      }
      if (!($data[0]["CampaignMaster"]["cpi"]) || (double)($data[0]["CampaignMaster"]["cpi"]) <= (double)0.00)
      {
        $this->errorFinish ("Uninitialized campaign [cpi] -> $this->appsigid");
        $error = 1;
      }
    }

    if ($data[0]["CampaignMaster"]["begin_time"] > $this->now)
    {
      $this->errorFinish ("Uninitialized campaign [begin_time] -> $this->appsigid");
      $error = 1;
    }
    if ($data[0]["CampaignMaster"]["end_time"] < $this->now)
    {
      $this->errorFinish ("Uninitialized campaign [end_time] -> $this->appsigid");
      $error = 1;
    }

    if ($error) { return 0; }

    $this->click_table_name = $this->appsigid."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);
    //$this->errorLog ("target click table = $this->click_table_name");

    $this->advertiser_id = $data[0]["CampaignMaster"]["advertiser_id"];

    $this->campaign_start = $data[0]["CampaignMaster"]["begin_time"];
    $this->campaign_end = $data[0]["CampaignMaster"]["end_time"];

    $this->permit_transactionid_match = $data[0]["CampaignMaster"]["permit_transactionid_match"];

    // Have to decide which 3rdparty will be used.
    $this->checkUsing3rdParty ($data[0]);

    if ($data[0]["CampaignMaster"]["device"] === "ios")
    {
      $this->is_ios_campaign = 1;
    }
    elseif ($data[0]["CampaignMaster"]["device"] === "android")
    {
      $this->is_android_campaign = 1;
    }

    return 1;
  }

  function checkUsing3rdParty ($data)
  {
    $this->errorLog ("checkUsing3rdParty");

    if($data["CampaignMaster"]["has_offers"] == 1)
    {
      $this->errorLog ("Using has_offers");
      $this->has_offers = 1;
    }
    elseif($data["CampaignMaster"]["dena"] == 1)
    {
      $this->errorLog ("Using dena");
      $this->dena = 1;
    }
    elseif($data["CampaignMaster"]["fox"] == 1)
    {
      $this->errorLog ("Using fox");
      $this->fox = 1;
    }
    elseif($data["CampaignMaster"]["kochava"] == 1)
    {
      $this->errorLog ("Using kochava");
      $this->kochava = 1;
    }
    elseif($data["CampaignMaster"]["ad_x"] == 1)
    {
      $this->errorLog ("Using ad_x");
      $this->ad_x = 1;
    }
    elseif($data["CampaignMaster"]["smac"] == 1)
    {
      $this->errorLog ("Using smac");
      $this->smac = 1;
    }
    elseif($data["CampaignMaster"]["tracking_affiliates"] == 1)
    {
      $this->errorLog ("Using tracking_affiliates");
      $this->tracking_affiliates = 1;
    }
    elseif($data["CampaignMaster"]["zynga"] == 1)
    {
      $this->errorLog ("Using zynga");
      $this->zynga = 1;
    }
    elseif($data["CampaignMaster"]["gmotech"] == 1)
    {
      $this->errorLog ("Using gmotech");
      $this->gmotech = 1;
    }
    elseif($data["CampaignMaster"]["adven"] == 1)
    {
      $this->errorLog ("Using adven");
      $this->adven = 1;
    }
    elseif($data["CampaignMaster"]["motive"] == 1)
    {
      $this->errorLog ("Using motive");
      $this->motive = 1;
    }
    elseif($data["CampaignMaster"]["appflood"] == 1)
    {
      $this->errorLog ("Using appflood");
      $this->appflood = 1;
    }
    elseif($data["CampaignMaster"]["advertiser_postback"] == 1)
    {
      $this->advertiser_postback = 1;
    }
  }

  function checkPublisherID ($pubid)
  {
    $data = $this->PublisherMaster->find('all', $params = array(
      'fields' => array('id'),
      'conditions' => array(
        'id' => $pubid
        )));

    if(isset($data[0]["PublisherMaster"]["id"]))
    {
      if(strlen($this->request->query['pubid']) == 9 && is_numeric($this->request->query['pubid']))
        $this->pubid = $this->request->query['pubid'];
      else
        $this->errorFinish ("Publisher ID Is Incorrect");
    }
    else
      $this->errorFinish ("Publisher ID Doesn't Exist");
  }

  function isValidDeviceID ($str)
  {
    //return !!!preg_match('/[^a-zA-Z0-9_-]/', $str);
    return !!!preg_match('/[^a-zA-Z0-9\._-]/', $str);
  }

  function isValidHashID ($str)
  {
    return !!!preg_match('/[^a-zA-Z0-9]/', $str);
  }

  function checkAllDeviceID ()
  {
    $device_id = 0;

    // check idfa
    if(isset ($this->request->query['idfa']) && $this->request->query['idfa'])
    {
      if (!$this->isValidDeviceID ($this->request->query['idfa']))
      {
        return $this->errorLog ("idfa is not Valid ".$this->request->query['idfa']);
      }

      $this->idfa = $this->request->query['idfa'];
      $device_id = 1;
      $this->errorLog ("Find IDFA appsigid=$this->appsigid : idfa=$this->idfa");
    }

    // check idfamd5
    if(isset($this->request->query['idfamd5']) && $this->request->query['idfamd5'])
    {
      if (!$this->isValidHashID ($this->request->query['idfamd5']))
      {
        return $this->errorLog ("idfamd5 is not Valid ".$this->request->query['idfamd5']);
      }

      $this->idfamd5 = $this->request->query['idfamd5'];
      $device_id = 1;
      $this->errorLog ("Find IDFA-MD5 appsigid=$this->appsigid : idfamd5=$this->idfamd5");
    }
    else
    {
      if (isset ($this->idfa) && $this->idfa) { $this->idfamd5 = md5 ($this->idfa); }
    }

    // check idfasha1
    if(isset($this->request->query['idfasha1']) && $this->request->query['idfasha1'])
    {
      if (!$this->isValidHashID ($this->request->query['idfasha1']))
      {
        return $this->errorLog ("idfasha1 is not Valid ".$this->request->query['idfasha1']);
      }

      $this->idfasha1 = $this->request->query['idfasha1'];
      $device_id = 1;
      $this->errorLog ("Find IDFA-SHA1 appsigid=$this->appsigid : idfasha1=$this->idfasha1");
    }
    else
    {
      if (isset ($this->idfa) && $this->idfa) { $this->idfasha1 = sha1 ($this->idfa); }
    }

    // check dpidraw
    if(isset($this->request->query['dpidraw']) && $this->request->query['dpidraw'])
    {
      if (!$this->isValidDeviceID ($this->request->query['dpidraw']))
      {
        return $this->errorLog ("Find dpidraw idfa is not Valid ".$this->request->query['dpidraw']);
      }

      $this->dpidraw = $this->request->query['dpidraw'];
      $device_id = 1;
      $this->errorLog ("appsigid=$this->appsigid : dpidraw=$this->dpidraw");
    }

    // check dpidmd5
    if(isset($this->request->query['dpidmd5']) && $this->request->query['dpidmd5'])
    {
      if (!$this->isValidHashID ($this->request->query['dpidmd5']))
      {
        return $this->errorLog ("dpidmd5 is not Valid ".$this->request->query['dpidmd5']);
      }

      $this->dpidmd5 = $this->request->query['dpidmd5'];
      $device_id = 1;
      $this->errorLog ("Find dpidmd5 appsigid=$this->appsigid : dpidmd5=$this->dpidmd5");
    }
    else
    {
      if (isset ($this->dpidraw) && $this->dpidraw) { $this->dpidmd5 = md5 ($this->dpidraw); }
    }

    // check dpidsha1
    if(isset($this->request->query['dpidsha1']) && $this->request->query['dpidsha1'])
    {
      if (!$this->isValidHashID ($this->request->query['dpidsha1']))
      {
        return $this->errorLog ("dpidsha1 is not Valid ".$this->request->query['dpidsha1']);
      }

      $this->dpidsha1 = $this->request->query['dpidsha1'];
      $device_id = 1;
      $this->errorLog ("Find dpidsha1 appsigid=$this->appsigid : dpidsha1=$this->dpidsha1");
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
      $this->errorLog ("Find openudid appsigid=$this->appsigid : openudid=$this->openudid");
    }

    // check mac address
    if(isset($this->request->query['macaddr']) && $this->request->query['macaddr'])
    {
      if(strlen($this->request->query['macaddr']) <= 255)
      {
        $this->macaddr = $this->request->query['macaddr'];
        $device_id = 1;
        $this->errorLog ("Find macaddr appsigid=$this->appsigid : maccaddr=$this->macaddr");
      }
    }

    /* If the app uses SMAC, GMOTECH, APPFLOOD or "permit_transactionid_match = 1", we can recognize clickid as deviceid */
    if ($this->smac || $this->gmotech || $this->appflood || $this->permit_transactionid_match)
    {
      // In this case, we assume that the clickid should be like one of device id.
      if(isset($this->request->query['clickid']) && $this->request->query['clickid'])
      {
        if (!$this->isValidDeviceID ($this->request->query['clickid']))
        {
          return $this->errorLog ("In this case, clickid assumes deviceid but it is not Valid ".$this->request->query['clickid']);
        }

        $this->publisher_click_id = $this->request->query['clickid'];
        $device_id = 1;
        $this->errorLog ("Find clickid instead of any device ids appsigid=$this->appsigid : clickid=$this->clickid");
      }
      else
      {
        $this->publisher_click_id = $this->generateClickId ();
        $device_id = 1;
        $this->errorLog ("Making click id for permit_transactionid_match appsigid=$this->appsigid : clickid=$this->publisher_click_id");
      }
    }

    // If the publisher id is Burstly we have to make random string for click id and assume that it is transaction id.
    if ($this->isBurstly ())
    {
      $this->publisher_click_id = $this->generateClickId ();
      $device_id = 1;
      $this->errorLog ("Making click id for burstly appsigid=$this->appsigid : clickid=$this->publisher_click_id");
    }

    return $device_id;
  }

  function mobpartner ()
  {
    if ($this->appsigid === '22f156736e2e28455a54fce2f0a81c5900a25412')
      return 1;

    return 0;
  }

  function isBurstly ()
  {
    if ($this->pubid === '200000023')
      return 1;

    return 0;
  }

  function isApplifier ()
  {
    if ($this->pubid === '200000037')
      return 1;

    return 0;
  }

  function generateClickId ()
  {
    $strinit = "abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ012345679";
    $strarray = preg_split ("//", $strinit, 0, PREG_SPLIT_NO_EMPTY);
    for ($i = 0, $str = null; $i < 18; $i++)
    {
      $str .= $strarray[array_rand($strarray, 1)];
    }
    return $str;
  }

  function doRedirect ($url, &$data)
  {
    $this->errorLog ("doRedirect");
    $fields_string = "";
    foreach ($data as $key => $value)
    {
      $fields_string .= $key.'='.$value.'&';
    }
    $fields_string = rtrim ($fields_string, "&");

    $url .= "?".$fields_string;
    $this->errorLog ("$url");
    header("Location: $url");
  }

  function doPost ($url, &$data)
  {
    $this->errorLog ("doPost");
    $fields_string = "";
    foreach ($data as $key => $value)
    {
      $fields_string .= $key.'='.$value.'&';
    }
    $fields_string = rtrim ($fields_string, "&");
    //$this->errorLog ($fields_string);

    $url .= "?".$fields_string;

    $this->errorLog ("$url");
    print ("ULR = $url <br>");

    //open connection
    $ch = curl_init ();

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
      );

    curl_setopt_array( $ch, $options );
    $response = curl_exec ($ch);
    $httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);

    if ($httpCode == 301)
    {
      // Currently in DeNA case
      $info = curl_getinfo ($ch);

      $this->errorLog ("Return code is {$httpCode} \n"."Error Msg :".curl_error($ch)."\n");
      $this->errorLog ("seconds to send a request to " . $info['url']."\n");

      header("Location: ".$info['url']);
    }
    elseif ($httpCode != 200)
    {
      $info = curl_getinfo ($ch);
      echo "Curl Error : Return code is {$httpCode} \n"."Error Msg :".curl_error($ch)."\n";
      echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url']."\n";
    }
    else
    {
      print "Response<br>";
      print "<pre>".htmlspecialchars($response)."</pre>";
    }

    //close connection
    curl_close ($ch);
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

    // check campaign id
    if(isset($this->request->query['camid']) && $this->request->query['camid'])
    {
      $this->campaign_id = $this->request->query['camid'];
    }

    // check creative id
    if(isset($this->request->query['creid']) && $this->request->query['creid'])
    {
      $this->creative_id = $this->request->query['creid'];
    }

    // check publisher's publisher id
    if(isset($this->request->query['pubpid']) && $this->request->query['pubpid'])
    {
      $this->pubpid = $this->request->query['pubpid'];
    }

    // check publisher category id
    if(isset($this->request->query['pubcatid']) && $this->request->query['pubcatid'])
    {
      $this->pubcatid = $this->request->query['pubcatid'];
    }

    // check geo id
    if(isset($this->request->query['geoid']) && $this->request->query['geoid'])
    {
      $this->geoid = $this->request->query['geoid'];
    }

    // check ip
    if(isset($this->request->query['ip']) && $this->request->query['ip'])
    {
      $this->ip = $this->request->query['ip'];
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

    // check apiver
    if(isset($this->request->query['apiver']) && $this->request->query['apiver'])
    {
      $this->apiver = $this->request->query['apiver'];
    }
  }

  function isExistSameClick ()
  {
    $found_duplicate = 0;

    if($this->idfa != "")
    {
      $conditions = array('idfa' => $this->idfa,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["idfa"]))
      {
        if($this->campaign_start < $find_id["Click"]["created"] && $this->campaign_end > $find_id["Click"]["created"])
        {
          $found_duplicate = 1;
          $this->errorLog ("Duplicate Click Found(idfa=$this->idfa)");
        }
      }
    }

    if($this->idfamd5 != "")
    {
      $conditions = array('idfamd5' => $this->idfamd5,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["idfamd5"]))
      {
        if($this->campaign_start < $find_id["Click"]["created"] && $this->campaign_end > $find_id["Click"]["created"])
        {
          $found_duplicate = 1;
          $this->errorLog ("Duplicate Click Found(idfamd5=$this->idfamd5)");
        }
      }
    }

    if($this->idfasha1 != "")
    {
      $conditions = array('idfasha1' => $this->idfasha1,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["idfasha1"]))
      {
        if($this->campaign_start < $find_id["Click"]["created"] && $this->campaign_end > $find_id["Click"]["created"])
        {
          $found_duplicate = 1;
          $this->errorLog ("Duplicate Click Found(idfasha1=$this->idfasha1)");
        }
      }
    }

    if($this->dpidraw != "")
    {
      $conditions = array('dpidraw' => $this->dpidraw,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["dpidraw"]))
      {
        if($this->campaign_start < $find_id["Click"]["created"] && $this->campaign_end > $find_id["Click"]["created"])
        {
          $found_duplicate = 1;
          $this->errorLog ("Duplicate Click Found(dpidraw=$this->dpidraw)");
        }
      }
    }

    if($this->dpidmd5 != "")
    {
      $conditions = array('dpidmd5' => $this->dpidmd5,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["dpidmd5"]))
      {
        if($this->campaign_start < $find_id["Click"]["created"] && $this->campaign_end > $find_id["Click"]["created"])
        {
          $found_duplicate = 1;
          $this->errorLog ("Duplicate Click Found(dpidmd5=$this->dpidmd5)");
        }
      }
    }

    if($this->dpidsha1 != "")
    {
      $conditions = array('dpidsha1' => $this->dpidsha1,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["dpidsha1"]))
      {
        if($this->campaign_start < $find_id["Click"]["created"] && $this->campaign_end > $find_id["Click"]["created"])
        {
          $found_duplicate = 1;
          $this->errorLog ("Duplicate Click Found(dpidsha1=$this->dpidsha1)");
        }
      }
    }

    if($this->openudid != "")
    {
      $conditions = array('openudid' => $this->openudid,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["openudid"]))
      {
        $found_duplicate = 1;
        $this->errorLog ("Duplicate Click Found(openudid=$this->openudid)");
      }
    }

    if($this->macaddr != "")
    {
      $conditions = array('macaddr' => $this->macaddr,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["macaddr"]))
      {
        if($this->campaign_start < $find_id["Click"]["created"] && $this->campaign_end > $find_id["Click"]["created"])
        {
          $found_duplicate = 1;
          $this->errorLog ("Duplicate Click Found(macaddr=$this->macaddr)");
        }
      }
    }

    if (($this->publisher_click_id != "") &&
        ($this->smac || $this->gmotech || $this->permit_transactionid_match))
    {
      $conditions = array('publisher_click_id' => $this->publisher_click_id,
                          'appsigid' => $this->appsigid);
      if ($this->mobpartner ()) { $conditions['mobpartner_campaign_id'] = $this->mpid; }
      $find_id = $this->Click->find('first', $params = array('conditions' => $conditions));

      if(isset($find_id["Click"]["publisher_click_id"]))
      {
        $found_duplicate = 1;
        $this->errorLog ("Duplicate Click Found(publisher_click_id=$this->publisher_click_id)");
      }
    }

    return $found_duplicate;
  }

  function do3rdPartyTrack ()
  {
    if ($this->has_offers == 1)
    {
      $this->postBack2HasOffers ();
    }
    elseif ($this->dena == 1)
    {
      $this->postBack2DeNA ();
    }
    elseif ($this->fox == 1)
    {
      $this->postBack2Fox ();
    }
    elseif($this->kochava == 1)
    {
      $this->postBack2Kochava ();
    }
    elseif($this->ad_x == 1)
    {
      $this->postBack2AdX ();
    }
    elseif($this->smac == 1)
    {
      $this->postBack2Smac ();
    }
    elseif($this->tracking_affiliates == 1)
    {
      $this->postBack2TrackingAffiliates ();
    }
    elseif($this->zynga == 1)
    {
      $this->postBack2Zynga ();
    }
    elseif($this->gmotech == 1)
    {
      $this->postBack2Gmotech ();
    }
    elseif($this->adven == 1)
    {
      $this->postBack2Adven ();
    }
    elseif($this->motive == 1)
    {
      $this->postBack2Motive ();
    }
    elseif($this->appflood == 1)
    {
      $this->postBack2Appflood ();
    }
    elseif($this->advertiser_postback == 1)
    {
      $this->advertiserPostBack ();
    }
    else
    {
      if ($this->mobpartner ())
      {
        return $this->redirectMobPartner ();
      }

      $data = $this->PublisherMaster->find('all', $params = array(
        'conditions' => array(
          'id' => $this->pubid
          )));

      // redirect to store
      if($data[0]["PublisherMaster"]["redirect"] == 1)
      {
        return $this->redirect2Store ();
      }
    }
  }

  function postBack2HasOffers ()
  {
    $this->errorLog ("postBack2HasOffers");
    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $ha_publisher_id = $data[0]["CampaignMaster"]["has_offers_publisher_id"];
    $ha_site_id = $data[0]["CampaignMaster"]["has_offers_site_id"];
    $ha_offer_id = $data[0]["CampaignMaster"]["has_offers_offer_id"];

    if(isset($ha_site_id) && isset($ha_offer_id))
    {
      $url = 'http://hastrk1.com/serve';

      $data = array ();
      $data['action'] = 'click';
      $data['publisher_id'] = $ha_publisher_id;
      $data['site_id'] = $ha_site_id;
      $data['offer_id'] = $ha_offer_id;
      if ($this->is_android_campaign) { $data['device_id'] = $this->dpidraw; }
      if ($this->is_ios_campaign) { $data['ios_ifa'] = $this->idfa; }
      $data['sub_publisher'] = 'AMoAdDSP';
      $data['sub_ad'] = $this->creative_id;

      $this->doRedirect ($url, $data);
    }
  }

  function postBack2Fox ()
  {
    $this->errorLog ("postBack2Fox");

    $this->loadModel('MapFoxId');

    if (isset ($this->creative_id) && $this->creative_id)
    {
      $conditions = array ('appsigid' => $this->appsigid,
                           'creative_id' => $this->creative_id);
    }
    else
    {
      $conditions = array ('appsigid' => $this->appsigid,
                           'creative_id' => NULL);
    }

    $data = $this->MapFoxId->find('all', $params = array('conditions' => $conditions));

    if (!$data)
    {
      $this->manualRollbackClick ();
      $this->errorFinish ("Can NOT find the fox record (appsigid = $this->appsigid) (creative_id = $this->creative_id)");
    }

    $fox_site_id = $data[0]["MapFoxId"]["fox_site_id"];
    $fox_article_id = $data[0]["MapFoxId"]["fox_article_id"];
    $fox_link_id = $data[0]["MapFoxId"]["fox_link_id"];
    $fox_image_id = $data[0]["MapFoxId"]["fox_image_id"];

    if (!isset ($fox_site_id) || !($fox_site_id)) { return $this->errorFinish ("Can NOT find the fox_site_id (appsigid = $this->appsigid) (creative_id = $this->creative_id)"); }
    if (!isset ($fox_article_id) || !($fox_article_id)) { return $this->errorFinish ("Can NOT find the fox_article_id (appsigid = $this->appsigid) (creative_id = $this->creative_id)"); }
    if (!isset ($fox_link_id) || !($fox_link_id)) { return $this->errorFinish ("Can NOT find the fox_link_id (appsigid = $this->appsigid) (creative_id = $this->creative_id)"); }
    if (!isset ($fox_image_id) || !($fox_image_id)) { return $this->errorFinish ("Can NOT find the fox_image_id (appsigid = $this->appsigid) (creative_id = $this->creative_id)"); }

    $url = 'http://app-adforce.jp/ad/p/r';

    $data = array ();
    $data['_site'] = $fox_site_id;
    $data['_article'] = $fox_article_id;
    $data['_link'] = $fox_link_id;
    $data['_image'] = $fox_image_id;

    if ($this->is_ios_campaign)
    {
      if ($this->dpidraw) { $data['fuid'] = $this->dpidraw; }
      if ($this->macaddr) { $data['muid'] = $this->macaddr; }
      if ($this->idfa) {$data['adid'] = $this->idfa; }

      if (!$this->idfa && $this->idfasha1)
      {
        $data['adid'] = $this->idfasha1;
      }
      elseif (!$this->idfa && $this->idfamd5)
      {
        $data['adid'] = $this->idfamd5;
      }
    }
    elseif ($this->is_android_campaign)
    {
      $data['anid'] = $this->dpidraw;

      if (!$this->dpidraw && $this->dpidsha1)
      {
        $data['anid'] = $this->dpidsha1;
      }
      elseif (!$this->dpidraw && $this->dpidmd5)
      {
        $data['anid'] = $this->dpidmd5;
      }
    }

    if ($this->publisher_click_id)
    {
      $data['suid'] = $this->publisher_click_id;
    }

    $this->doRedirect ($url, $data);
  }

  function postBack2DeNA ()
  {
    $this->errorLog ("postBack2DeNA");
    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $dena_campaign_id = $data[0]["CampaignMaster"]["dena_campaign_id"];
    if (!isset ($dena_campaign_id)) { return $this->errorFinish ("Can NOT find the dena_campaign_id (appsigid = $this->appsigid)"); }

    $url = 'http://ad.mobage.com/click';
    $url = str_replace ("CCAAMMID", $dena_campaign_id, $url); // dena_campaign_id

    if (isset ($this->dpidraw) && $this->dpidraw)
    {
      $and_id_sha1 = sha1 ($this->dpidraw);
    }
    else if (isset ($this->dpidsha1) && $this->dpidsha1)
    {
      $and_id_sha1 = $this->dpidsha1;
    }
    else
    {
      return $this->errorFinish ("postBack2DeNA () : invalid and_id_sha1");
    }

    $data = array ();
    $data['campaign_id'] = $dena_campaign_id;
    $data['device_id'] = $and_id_sha1;

    $this->doPost ($url, $data);
  }

  function postBack2Kochava ()
  {
    $this->errorLog ("postBack2Kochava");

    // Kochava click URL
    $data = $this->CampaignMaster->find('all', $params = array('conditions' => array('id' => $this->request->query['appsigid'])));

    $kochava_network_id = $data[0]["CampaignMaster"]["kochava_network_id"];
    $kochava_campaign_id = $data[0]["CampaignMaster"]["kochava_campaign_id"];
    $kochava_site_id = $data[0]["CampaignMaster"]["kochava_site_id"];
    $kochava_creative_id = $data[0]["CampaignMaster"]["kochava_creative_id"];
    $kochava_device_id_type = $data[0]["CampaignMaster"]["kochava_device_id_type"];

    if (!isset ($kochava_network_id) || !($kochava_network_id)) { return $this->errorFinish ("kochava_network_id is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($kochava_campaign_id) || !($kochava_campaign_id)) { return $this->errorFinish ("kochava_campaign_id is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($kochava_site_id) || !($kochava_site_id)) { return $this->errorFinish ("kochava_site_id is invalid (appsigid = $this->appsigid)"); }
    //if (!isset ($kochava_creative_id) || !($kochava_creative_id)) { return $this->errorFinish ("kochava_creative_id is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($kochava_device_id_type) || !($kochava_device_id_type)) { return $this->errorFinish ("kochava_device_id_type is invalid (appsigid = $this->appsigid)"); }

    $url = 'https://control.kochava.com/v1/cpi/click';

    $data = array ();
    $data['network_id'] = $kochava_network_id;
    $data['campaign_id'] = $kochava_campaign_id;
    $data['site_id'] = $kochava_site_id;
    $data['device_id_type'] = $kochava_device_id_type;

    // If the publisher id Burstly, we have to set clickid as device_id
    if ($this->isBurstly () ||
        ($this->is_android_campaign && !$this->dpidraw && !$this->dpidmd5 && !$this->dpidsha1 && !$this->macaddr) ||
        ($this->is_ios_campaign && !$this->idfa && !$this->idfamd5 && !$this->idfasha1 && !$this->macaddr))
    {
      $this->errorLog ("trying Kochava fingerpriting");

      $data['device_id'] = "";
      $data['imei'] = "";
      $data['mac'] = "";
      $data['odin'] = "";
      $data['device_id_type'] = "";
      $data['click_id'] = $this->publisher_click_id;
      return $this->doRedirect ($url, $data);
    }

    if($kochava_device_id_type === 'idfa')
    {
      if ($this->idfa)
      {
        $data['device_id'] = $this->idfa;
        $data['device_id_is_hashed'] = "false";
      }
      elseif ($this->idfamd5)
      {
        $data['device_id'] = $this->idfamd5;
        $data['device_id_is_hashed'] = "true";
        $data['device_hash_method'] = "MD5";
      }
      elseif ($this->idfasha1)
      {
        $data['device_id'] = $this->idfasha1;
        $data['device_id_is_hashed'] = "true";
        $data['device_hash_method'] = "SHA1";
      }
    }
    elseif($kochava_device_id_type === 'android_id')
    {
      if ($this->advertiser_id === '1008') // DeNA
      {
        // Because they can only set hash of SHA1 Android id from thier apps.
        $data['device_id'] = $this->dpidsha1;
        $data['device_id_is_hashed'] = "true";
        $data['device_hash_method'] = "SHA1";
        $data['click_id'] = "";
      }
      else
      {
        if ($this->dpidraw)
        {
          $data['device_id'] = $this->dpidraw;
          $data['device_id_is_hashed'] = "false";
        }
        elseif ($this->dpidmd5)
        {
          $data['device_id'] = $this->dpidmd5;
          $data['device_id_is_hashed'] = "true";
          $data['device_hash_method'] = "MD5";
        }
        elseif ($this->dpidsha1)
        {
          $data['device_id'] = $this->dpidsha1;
          $data['device_id_is_hashed'] = "true";
          $data['device_hash_method'] = "SHA1";
        }
      }
    }

    if ((!isset ($data['device_id']) || $data['device_id']) &&
        ($this->macaddr))
    {
      $data['device_id_type'] = "mac";
      $data['device_id'] = $this->macaddr;
    }

    if ($this->isApplifier ())
    {
      $data['pbr'] = "1";
    }

    $this->doRedirect ($url, $data);
  }

  function postBack2AdX ()
  {
    $this->errorLog ("postBack2AdX");

    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $device = $data[0]["CampaignMaster"]["device"];
    $ad_x_id1 = $data[0]["CampaignMaster"]["ad_x_id1"];
    $ad_x_id2 = $data[0]["CampaignMaster"]["ad_x_id2"];

    if (!isset ($ad_x_id1) || !($ad_x_id1)) { return $this->errorFinish ("ad_x_id1 is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($ad_x_id2) || !($ad_x_id2)) { return $this->errorFinish ("ad_x_id2 is invalid (appsigid = $this->appsigid)"); }

    // ex) "http://ad-x.co.uk/API/click/CAYMEN2011BEASLEY/web851a4d1c0e1bcf/NET/";
    $url = "http://ad-x.co.uk/API/click/". $ad_x_id1 ."/". $ad_x_id2 ."/NET/";

    if (!$this->publisher_click_id)
    {
      if ($this->is_ios_campaign)
      {
        $this->publisher_click_id = sha1 ($this->idfa);
      }
      elseif ($this->is_android_campaign)
      {
        if ($this->dpidsha1)
        {
          $this->publisher_click_id = $this->dpidsha1;
        }
        elseif ($this->dpidraw)
        {
          $this->publisher_click_id = sha1 ($this->dpidraw);
        }
      }
    }

    $url .= "$this->publisher_click_id/AMoAd";
    $data = array ();
    if ($this->is_ios_campaign)
    {
      $data['idfa'] = $this->idfa;
    }
    elseif ($this->is_android_campaign)
    {
      if ($this->dpidsha1)
      {
        $data['android_id'] = $this->dpidsha1;
      }
      elseif ($this->dpidraw)
      {
        $data['android_id'] = $this->dpidraw;
      }
    }

    $this->doRedirect ($url, $data);
  }

  function postBack2Smac ()
  {
    $this->errorLog ("postBack2Smac");

    if (!$this->publisher_click_id)
    {
      // When the campaign use Smac, the publisher_click_id is required.
      $this->manualRollbackClick ();
      $this->errorFinish ("Can NOT find the clickid, in this campaign we need clickid parameter (appsigid = $this->appsigid)");
    }

    $this->loadModel('MapSmacId');
    if (isset ($this->creative_id) && $this->creative_id)
    {
      $conditions = array ('appsigid' => $this->appsigid,
                           'creative_id' => $this->creative_id);
    }
    else
    {
      $this->creative_id = "LD003_050_A_AMo_And_AT_DSP_battle";
      $conditions = array ('appsigid' => $this->appsigid,
                           'creative_id' => $this->creative_id);

      //$this->manualRollbackClick ();
      //$this->errorFinish ("Can NOT find the smac record (appsigid = $this->appsigid) (creative_id = NULL)");
    }

    $data = $this->MapSmacId->find('all', $params = array('conditions' => $conditions));
    if (!$data)
    {
      $this->manualRollbackClick ();
      $this->errorFinish ("Can NOT find the smac record (appsigid = $this->appsigid) (creative_id = $this->creative_id)");
    }

    $smac_id = $data[0]["MapSmacId"]["smac_id"];
    if (!isset ($smac_id) || !($smac_id)) { return $this->errorFinish ("smac_id is invalid (appsigid = $this->appsigid)"); }

    $url = "http://track-sp.sm-ac.jp/click/click.php";
    $data = array ();
    $data['mpv'] = $smac_id;
    $data['transactionid'] = $this->publisher_click_id;
    $this->doRedirect ($url, $data);
  }

  function postBack2TrackingAffiliates ()
  {
    $this->errorLog ("postBack2TrackingAffiliates");

    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $offer_id = $data[0]["CampaignMaster"]["tracking_affiliates_offer_id"];

    $url = "http://tracking.affiliates.de/aff_c";
    $data = array ();
    $data['offer_id'] = $offer_id;
    $data['aff_id'] = "3604";
    $data['aff_sub1'] = $this->publisher_click_id;
    $data['aff_sub2'] = $this->appsigid;
    $data['aff_sub3'] = "0";

    $this->doRedirect ($url, $data);
  }

  function postBack2Zynga ()
  {
    $this->errorLog ("postBack2Zynga");

    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $zynga_partner = $data[0]["CampaignMaster"]["zynga_partner"];
    $zynga_campaign = $data[0]["CampaignMaster"]["zynga_campaign"];
    $zynga_bundle_id = $data[0]["CampaignMaster"]["zynga_bundle_id"];
    $zynga_url = $data[0]["CampaignMaster"]["zynga_url"];

    if (!isset ($zynga_partner) || !($zynga_partner)) { return $this->errorFinish ("zynga_partner is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($zynga_campaign) || !($zynga_campaign)) { return $this->errorFinish ("zynga_campaign is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($zynga_bundle_id) || !($zynga_bundle_id)) { return $this->errorFinish ("zynga_bundle_id is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($zynga_url) || !($zynga_url)) { return $this->errorFinish ("zynga_url is invalid (appsigid = $this->appsigid)"); }

    $url = "http://click.zynga.com/track";
    $data = array ();
    $data['partner'] = $zynga_partner;
    $data['campaign'] = $zynga_campaign;
    $data['bundle_id'] = $zynga_bundle_id;
    $data['url'] = $zynga_url;
    $data['timestamp'] = time();

    if ($this->idfa)
    {
      $data['idfa'] = $this->idfa;
    }
    elseif ($this->dpidsha1)
    {
      $data['sha1_android_id'] = $this->dpidsha1;
    }
    elseif ($this->dpidraw)
    {
      $data['android_id'] = $this->dpidraw;
    }
    else
    {
      return $this->errorFinish ("zynga_device_id is invalid (appsigid = $this->appsigid)");
    }

    $this->doRedirect ($url, $data);
  }

  function postBack2Gmotech ()
  {
    $this->errorLog ("postBack2Gmotech");

    if (!$this->publisher_click_id)
    {
      // When the campaign use Gmotech, the publisher_click_id is required.
      $this->manualRollbackClick ();
      $this->errorFinish ("Can NOT find the clickid, in this campaign we need clickid parameter (appsigid = $this->appsigid)");
    }

    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $gmotech_zoneid = $data[0]["CampaignMaster"]["gmotech_zoneid"];
    $gmotech_adid = $data[0]["CampaignMaster"]["gmotech_adid"];
    if (!isset ($gmotech_zoneid) || !($gmotech_zoneid)) { return $this->errorFinish ("gmotech_zoneid is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($gmotech_adid) || !($gmotech_adid)) { return $this->errorFinish ("gmotech_adid is invalid (appsigid = $this->appsigid)"); }

    $url = "http://ad.smaad.jp/redirectAd.php";
    $data = array ();
    $data['zoneid'] = $gmotech_zoneid;
    $data['adid'] = $gmotech_adid;
    $data['u'] = $this->publisher_click_id;

    $this->doRedirect ($url, $data);
  }

  function postBack2Adven ()
  {
    $this->errorLog ("postBack2Adven");

    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $adeven_tracker_id = $data[0]["CampaignMaster"]["adeven_tracker_id"];

    if (!isset ($adeven_tracker_id) || !($adeven_tracker_id)) { return $this->errorFinish ("adeven_tracker_id is invalid (appsigid = $this->appsigid)"); }

    $url = "http://app.adjust.io/".$adeven_tracker_id;
    $install_callback_url = "https://dsp-cv.amoad.net/cv/v1/android/?appsigid=".$this->appsigid;

    $data = array ();
    if ($this->is_ios_campaign)
    {
      if ($this->idfa)
      {
        $data['idfa'] = $this->idfa;
      }
      elseif ($this->idfasha1)
      {
        $data['idfa_lower_sha1'] = strtolower ($this->idfasha1);
      }
      elseif ($this->idfamd5)
      {
        $data['idfa_lower_md5'] = strtolower ($this->idfamd5);
      }

      $install_callback_url .= "&idfa={idfa}";
    }
    elseif ($this->is_android_campaign)
    {
      if ($this->dpidraw)
      {
        $data['android_id'] = $this->dpidraw;
      }
      elseif ($this->dpidsha1)
      {
        $data['android_id_lower_sha1'] = strtolower ($this->dpidsha1);
      }
      elseif ($this->dpidmd5)
      {
        $data['android_id_lower_md5'] = strtolower ($this->dpidmd5);
      }

      $install_callback_url .= "&dpidraw={android_id}";
    }

    if ($this->publisher_click_id)
    {
      $data['clickid'] = $this->publisher_click_id;
      $install_callback_url .= "&transactionid={clickid}";
    }

    $install_callback_url .= "&lflag=0&appid={app_id}";
    $this->errorLog ("adven_install_callback_url = ".$install_callback_url);

    $data['install_callback'] = urlencode ($install_callback_url);
    $this->doRedirect ($url, $data);
  }

  function postBack2Motive ()
  {
    $this->errorLog ("postBack2Motive");

    if (!$this->publisher_click_id)
    {
      // When the campaign use Gmotech, the publisher_click_id is required.
      $this->manualRollbackClick ();
      $this->errorFinish ("Can NOT find the clickid, in this campaign we need clickid parameter (appsigid = $this->appsigid)");
    }

    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $motive_a_id = $data[0]["CampaignMaster"]["motive_a_id"];
    $motive_c_id = $data[0]["CampaignMaster"]["motive_c_id"];
    if (!isset ($motive_a_id) || !($motive_a_id)) { return $this->errorFinish ("motive_a_id is invalid (appsigid = $this->appsigid)"); }
    if (!isset ($motive_c_id) || !($motive_c_id)) { return $this->errorFinish ("motive_c_id is invalid (appsigid = $this->appsigid)"); }

    $url = "http://traktum.com/";
    $data = array ();
    $data['a'] = $motive_a_id;
    $data['c'] = $motive_c_id;
    $data['s2'] = $this->publisher_click_id;

    if ($this->idfa)
    {
      $data['s5'] = $this->idfa;
    }
    elseif ($this->dpidraw)
    {
      $data['s5'] = $this->dpidraw;
    }

    $this->doRedirect ($url, $data);
  }

  function postBack2Appflood ()
  {
    $this->errorLog ("postBack2Appflood");

    if (!$this->publisher_click_id)
    {
      // When the campaign use Gmotech, the publisher_click_id is required.
      $this->manualRollbackClick ();
      $this->errorFinish ("Can NOT find the clickid, in this campaign we need clickid parameter (appsigid = $this->appsigid)");
    }

    /*
    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));
        */

    $url = "http://data.appflood.com/partner_add_click_from_client";
    $data = array ();
    $data['app_key'] = "jHCzjunGHo1LowUt";
    $data['type'] = "31";
    $data['tid'] = "6211";
    $data['cid'] = "1957";
    $data['redirect'] = "1";
    $data['aid'] = $this->dpidraw;
    $data['transaction_id'] = $this->publisher_click_id;
    $data['transaction_id2'] = "com.cleanmaster.mguard";
    $data['transaction_id3'] = $this->appsigid;

    $this->doRedirect ($url, $data);
  }

  function advertiserPostBack ()
  {
    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array(
        'id' => $this->appsigid
        )));

    $url = $data[0]["CampaignMaster"]["url"];

    $url = str_replace("UUDDIIDD", $this->dpidraw, $url); // udid
    $url = str_replace("UDIDOPEN", $this->openudid, $url); // openudid
    $url = str_replace("IIDDFFAA", $this->idfa, $url); // idfa
    $url = str_replace("MMAACCAA", $this->macaddr, $url); // mac address
    $url = str_replace("CCAAMMID", $this->appsigid, $url); // appsigid

    header("Location: $url");
  }

  function redirectMobPartner ()
  {
    $data = $this->MobpartnerCampaign->find('all', $params = array(
      'conditions' => array('id' => $this->mpid)));

    $url = $data[0]["MobpartnerCampaign"]["url"];

    if (!$url)
    {
      return $this->errorLog ("No MobpartnerCampaign's URL id = ".$this->mpid);
    }

    $url .= "&tid1=".$this->mpid;

    if ($this->idfa) { $url .= "&idfa=".$this->idfa; }
    if ($this->dpidraw) { $url .= "&androidid=".$this->dpidraw; }

    $this->errorLog ("redirectMobPartner : $url");
    header("Location: $url");
  }

  function redirect2Store ()
  {
    $data = $this->CampaignMaster->find('all', $params = array(
      'conditions' => array('id' => $this->appsigid)));

    $url = $data[0]["CampaignMaster"]["url"];
    $this->errorLog ("redirect2Store : $url");
    header("Location: $url");
  }
}
