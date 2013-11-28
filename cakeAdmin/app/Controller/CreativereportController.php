<?php

App::uses('Controller', 'Controller');

class CreativeReportController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "dashboard";

  public function index ()
  {
    $this->loadModel('AdvertiserMaster');
    $this->loadModel('CampaignMaster');

    $today = date ("Y-m-d", strtotime ("now"));

    $this->paginate = array(
      'fields' => array ('AdvertiserMaster.*'));

    $datas = $this->paginate('AdvertiserMaster');
    $result = array ();
    foreach ($datas as $data)
    {
      if ($data['AdvertiserMaster']['id'] === '100000000') { continue; }
      $insert_data;
      $insert_data['id'] = $data['AdvertiserMaster']['id'];
      $insert_data['company_name'] = $data['AdvertiserMaster']['company_name'];
      $insert_data['owner_name'] = $data['AdvertiserMaster']['owner_name'];
      $insert_data['owner_email_address'] = $data['AdvertiserMaster']['owner_email_address'];
      $insert_data['created'] = $data['AdvertiserMaster']['created'];
      $insert_data['modified'] = $data['AdvertiserMaster']['modified'];
      $insert_data['enable_campaign_num'] = $this->getCampaignEnable ($data['AdvertiserMaster']['id'], $today);
      array_push ($result, $insert_data);
    }
    $this->set ('advertiser_datas', $result);

    // Do process first data
    $this->getCampaignData ($result[0]['id']);

    $this->set ('publishers', array ());

    $selectedAppsigid = 0;
    $selectedAdvertiser = 0;
    if (isset ($this->params['url']['appsigid']))
    {
      $selectedAppsigid = $this->params['url']['appsigid'];
      $selectedAdvertiser = $this->getSelectedAdvertiser ($this->params['url']['appsigid']);

      $this->Cookie->write('selectedAppsigid', $selectedAppsigid, null, '1 day');
      $this->Cookie->write('selectedAdvertiser', $selectedAdvertiser, null, '1 day');
    }
    $this->set ('selectedAppsigid', $selectedAppsigid);
    $this->set ('selectedAdvertiser', $selectedAdvertiser);
  }

  function getCampaignEnable ($advertiser_id, $today)
  {
    $datas = $this->CampaignMaster->find ('count', array (
      'conditions' => array ('CampaignMaster.advertiser_id' => $advertiser_id,
                             array ('CampaignMaster.end_time >' => "$today 23:59:59"))));

    return $datas;
  }

  function getSelectedAdvertiser ($appsigid)
  {
    if (!isset ($appsigid) && !$appsigid) { return 0; }

    $datas = $this->CampaignMaster->find ('all', array (
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    if (!$datas) { return 0; }

    return $datas[0]['CampaignMaster']['advertiser_id'];
  }

  function getCampaignData ($aid = '')
  {
    if (!$this->RequestHandler->isAjax())
    {
      //debug ('Not Ajax');
    }

    $this->loadModel('CampaignMaster');

    if (!isset ($aid) || !$aid) { return; }

    $datas = $this->CampaignMaster->find ('all', array (
      'conditions' => array ('CampaignMaster.advertiser_id' => $aid)));

    $this->set ('campaign_datas', $datas);
  }

  function getPubliser ($appsigid)
  {
    $this->click_table_name = $appsigid."Click";

    // Change table
    $this->Click->setSource ($this->click_table_name);

    $datas = $this->Click->find ('all', array (
      'fields' => 'DISTINCT Click.publisher_id',
      'conditions' => array ('Click.appsigid' => $appsigid)));

    $conditions = array ();
    foreach ($datas as $data)
    {
      if ($data['Click']['publisher_id'] === '200000000') { continue; }
      array_push ($conditions, $data['Click']['publisher_id']);
    }

    $publishers = $this->PublisherMaster->find ('all', array (
      'conditions' => array ('PublisherMaster.id' => $conditions)));

    $this->set ('publishers', $publishers);
  }

  function getCreativeData ($aid = '')
  {
    $this->loadModel('AdminAnalyzeCreative');
    $datas = $this->AdminAnalyzeCreative->find ('all', array (
      'fields' => array ('DISTINCT creative_id'),
      'conditions' => array ('AdminAnalyzeCreative.appsigid' => $aid),
      'order' => array('AdminAnalyzeCreative.publisher_id', 'AdminAnalyzeCreative.creative_id')));

    $this->set ('creatives', $datas);
  }

  function output2csv ($appsigid, $creative_id)
  {
    //$this->layout = false;
    //$this->autoRender = false;

    $this->loadModel('CampaignMaster');
    $this->loadModel('AdminAnalyzeCreative');
    $this->loadModel('MapFoxId');

    if (isset ($this->params['url']['from']))
    {
      $from = substr($this->params['url']['from'], 6, 4)."-".substr($this->params['url']['from'], 0, 2)."-".substr($this->params['url']['from'], 3, 2);
    }
    if (isset ($this->params['url']['to']))
    {
      $to = substr($this->params['url']['to'], 6, 4)."-".substr($this->params['url']['to'], 0, 2)."-".substr($this->params['url']['to'], 3, 2);
    }

    $campaign_masters = $this->CampaignMaster->find ('all', array (
      'conditions' => array ('CampaignMaster.id' => $appsigid)));
    if (!$campaign_masters) { return; };

    $conditions = array('AdminAnalyzeCreative.appsigid' => $appsigid,
                        'AdminAnalyzeCreative.target_date >=' => "$from",
                        'AdminAnalyzeCreative.target_date <=' => "$to");

    if ($creative_id != 0)
    {
      $conditions['AdminAnalyzeCreative.creative_id'] = $creative_id;
    }

    $datas = $this->AdminAnalyzeCreative->find ('all', array (
      'conditions' => $conditions,
      'order' => array('AdminAnalyzeCreative.target_datetime', 'AdminAnalyzeCreative.creative_id', 'AdminAnalyzeCreative.publisher_id')));

    $result = array();
    foreach ($datas as $data)
    {
      $insert_data;
      $insert_data['date'] = $data['AdminAnalyzeCreative']['target_date'];
      $insert_data['date_time'] = $data['AdminAnalyzeCreative']['target_datetime'];
      $insert_data['App'] = $campaign_masters[0]['CampaignMaster']['name'];
      $insert_data['creative'] = $data['AdminAnalyzeCreative']['creative_id'];
      $insert_data['os'] = $campaign_masters[0]['CampaignMaster']['device'];
      $insert_data['url'] = $this->get3rdpartyURL($campaign_masters[0], $data['AdminAnalyzeCreative']['appsigid'], $data['AdminAnalyzeCreative']['creative_id']);
      $insert_data['size'] = "";
      $insert_data['cost'] = "";
      $insert_data['impression'] = "";
      $insert_data['clicks'] = $data['AdminAnalyzeCreative']['click_num'];
      $insert_data['ctr'] = "";
      $insert_data['cpc'] = "";
      $insert_data['installs'] = $data['AdminAnalyzeCreative']['install_num'];
      $data['AdminAnalyzeCreative']['click_num'] == 0 ? 0 : $insert_data['cvr'] = $data['AdminAnalyzeCreative']['install_num'] / $data['AdminAnalyzeCreative']['click_num'];
      $insert_data['cpi'] = $campaign_masters[0]['CampaignMaster']['cpi'];
      array_push($result, $insert_data);
    }

    $delimiter = ',';
    $enclosure = '"';

    $filename = 'Daily_report_creatives_'.$from."_".$to;

    // The sheet first row
    $th = array('Date', 'DateTime', 'App', 'Creative', 'OS', 'URL', 'Size', 'Cost', 'Impressions', 'Clicks', 'CTR', 'CPC', 'Installs', 'CVR', 'CPI');
    $fp = fopen ('php://temp','r+');
    fputcsv ($fp, $th);
    foreach ($result as $ret)
    {
      fputcsv ($fp, $ret, $delimiter, $enclosure);
    }

    rewind ($fp);

    $csv = stream_get_contents ($fp);
    $csv = mb_convert_encoding ($csv, 'SJIS', mb_internal_encoding ());
    fclose ($fp);

    $filename = basename ($filename);

    header ('Content-Disposition:attachment; filename="' . $filename . '.csv"');
    header ('Content-Type:application/octet-stream');
    echo $csv;
    exit;
  }

  function get3rdpartyURL($campaign_master, $appsigid, $creative_id)
  {
    if ($campaign_master['CampaignMaster']['fox'] == 1)
    {
      $urls = $this->MapFoxId->find ('all', array (
        'conditions' => array ('MapFoxId.appsigid' => $appsigid,
                               'MapFoxId.creative_id' => $creative_id)));

      if (!$urls) { return; }

      return "http://app-adforce.jp/ad/p/r?_site=".$urls[0]['MapFoxId']['fox_site_id']."&_article=".$urls[0]['MapFoxId']['fox_article_id']."&_link=".$urls[0]['MapFoxId']['fox_link_id']."&_image=".$urls[0]['MapFoxId']['fox_image_id'];
    }
  }
}
