<?php

App::uses('Controller', 'Controller');

class ListController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "dashboard";

  public function getCampaignList ()
  {
  }

  public function getPublisherList ()
  {
  }

  function campaignGrid ()
  {
    $this->loadModel ('CampaignMaster');

    $this->autoLayout = false;
    $this->autoRender = false;

    $datas = $this->CampaignMaster->find ('all', array (
      'order' => array('CampaignMaster.name')));
  }

  function getAdvertiserData ($advertiser_id)
  {
    $this->loadModel ('AdvertiserMaster');
  }

  function publisherGrid ()
  {
    $this->loadModel ('PublisherMaster');

    $this->autoLayout = false;
    $this->autoRender = false;

    $datas = $this->PublisherMaster->find ('all', array (
      'order' => array('PublisherMaster.id')));

    $page = (isset ($this->params['url']['page']) && $this->params['url']['page']) ? $this->params['url']['page'] : 1;
    $limit = (isset ($this->params['url']['rows']) && $this->params['url']['rows']) ? $this->params['url']['rows'] : 50;
    $sidx = (isset ($this->params['url']['sidx']) && $this->params['url']['sidx']) ? $this->params['url']['sidx'] : 1;
    $sord = (isset ($this->params['url']['sord']) && $this->params['url']['sord']) ? $this->params['url']['sord'] : "";

    if (count ($datas) > 0)
    {
      $total_pages = ceil (count ($datas) / $limit);
    }
    else
    {
      $total_pages = 0;
    }
    if ($page > $total_pages) $page = $total_pages;

    if (stristr ($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml"))
    {
      header("Content-type: application/xhtml+xml;charset=utf-8");
    }
    else
    {
      header("Content-type: text/xml;charset=utf-8");
    }

    echo "<?xml version='1.0' encoding='utf-8'?>";
    echo "<rows>";
    echo "<page>".$page."</page>";
    echo "<total>".$total_pages."</total>";
    echo "<records>".count ($datas)."</records>";

    foreach ($datas as $data)
    {
      //debug ($data);
      if ($data['PublisherMaster']['id'] === '200000000') { continue; }

      echo "<row id='". $data['PublisherMaster']['id']."'>";
      echo "<cell>". $data['PublisherMaster']['id']."</cell>";
      echo "<cell><![CDATA[".$data['PublisherMaster']['owner_name']."]]></cell>";
      echo "<cell><![CDATA[".$data['PublisherMaster']['owner_email_address']."]]></cell>";
      echo "<cell><![CDATA[".$data['PublisherMaster']['url']."]]></cell>";
      echo "<cell><![CDATA[".$data['PublisherMaster']['url2']."]]></cell>";
      if ($data['PublisherMaster']['enable']) { echo "<cell><![CDATA[Active]]></cell>"; } else{ echo "<cell><![CDATA[Non-Active]]></cell>"; }
      echo "</row>";
    }
    echo "</rows>";
  }
}
