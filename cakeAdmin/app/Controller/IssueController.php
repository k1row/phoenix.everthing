<?php

App::uses('Controller', 'Controller');

class IssueController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "dashboard";

  public function Click ()
  {
    $this->init();
  }

  public function Conversion ()
  {
  }

  function init ()
  {
    $this->loadModel('AdvertiserMaster');
    $this->loadModel('CampaignMaster');
    $this->loadModel ('PublisherMaster');

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

    $publishers = $this->PublisherMaster->find ('all', array (
      'conditions' => array('PublisherMaster.enable ' => 1,
                            'PublisherMaster.id !=' => '200000000')));
    $this->set ('publishers', $publishers);

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
}
