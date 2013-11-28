<?php

App::uses('Controller', 'Controller');

class IndividualappController extends Controller
{
  public $name = 'Individualapp';

  public function index()
  {
    $this->loadModel ('AdminAnalyzeCampaign');
    $this->loadModel ('AdminAnalyzeCampaignPerDay');
    $this->loadModel ('CampaignMaster');
    $this->loadModel ('Click');
    $this->loadModel ('PublisherMaster');

    $appsigid = $this->params['url']['cid'];
    $this->set ('appsigid', $appsigid);

    $datas = $this->AdminAnalyzeCampaign->find ('all', array (
      'conditions' => array ('AdminAnalyzeCampaign.appsigid' => $appsigid)));

    $this->set ('datas', $datas);

    $this->paginate = array (
      'conditions' => array ('AdminAnalyzeCampaignPerDay.appsigid' => $appsigid),
      'limit' => 50,
      'order' => array('AdminAnalyzeCampaignPerDay.target_date' => 'ASC'),
      );

    $this->set ('dailydatas', $this->paginate ('AdminAnalyzeCampaignPerDay'));

    $campaignmaster = $this->CampaignMaster->find ('all', array (
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    $this->set ('campaignmaster', $campaignmaster);

    $this->getPubliser ($appsigid);

  }
  function getPubliser ($appsigid)
  {
    $datas = $this->Click->find ('all', array (
      'fields' => 'DISTINCT Click.publisher_id',
      'conditions' => array ('Click.appsigid' => $appsigid)));

    $conditions = array ();
    foreach ($datas as $data)
    {
      array_push ($conditions, $data['Click']['publisher_id']);
    }

    $publishers = $this->PublisherMaster->find ('all', array (
      'conditions' => array ('PublisherMaster.id' => $conditions)));

    $this->set ('publishers', $publishers);
  }
}
