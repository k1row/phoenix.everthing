<?php

App::uses('Controller', 'Controller');

class SearchController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "dashboard";

  public function index ()
  {
  }

  function getData ()
  {
    $this->loadModel('CampaignMaster');
    $this->loadModel('Click');
    $this->loadModel('Conversion');

    $ids = split ("\n", $this->params['data']['device_ids']);
    debug ($ids);
    foreach ($ids as $id)
    {
    }
  }
  function isExistConversion ($field, $value)
  {
    $datas = $this->Conversion->find ('all', array (
      'conditions' => array ('Conversion.appsigid' => $appsigid,
                             'Conversion.created >=' => "$this->target_datetime".":00:00",
                             'Conversion.created <=' => "$this->target_datetime".":59:59")));
  }
}
