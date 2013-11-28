<?php

App::uses('Controller', 'Controller');

class IndividualapptimelineController extends Controller
{
  public $name = 'Individualapptimeline';

  public function index()
  {
    $this->loadModel ('CampaignMaster');
    $this->loadModel ('Click');
    $this->loadModel ('Conversion');

    $appsigid = $this->params['url']['cid'];
    $this->set ('appsigid', $appsigid);

    $begin_time = sprintf ("%s 00:00:00", $this->params['url']['target_date']);
    $end_time = sprintf ("%s 23:59:59", $this->params['url']['target_date']);

    $campaignmaster = $this->CampaignMaster->find ('all', array (
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    $this->set ('campaignmaster', $campaignmaster);

    // Get contents
    $clicks = $this->collectPerTimeline (
      "Click",
      $this->exceptDuplicateRecord (
        "Click",
        $this->Click->find (
          'all',
          array('conditions' => array ('Click.appsigid' => $this->params['url']['cid'],
                                       array ('Click.created >=' => $begin_time),
                                       array ('Click.created <=' => $end_time))))));

    $conversions = $this->collectPerTimeline (
      "Conversion",
      $this->exceptDuplicateRecord (
        "Conversion",
        $this->Conversion->find (
          'all',
          array('conditions' => array ('Conversion.appsigid' => $this->params['url']['cid'],
                                       array ('Conversion.created >=' => $begin_time),
                                       array ('Conversion.created <=' => $end_time))))));

    $result = array ();
    foreach (array_keys ($clicks) as $key)
    {
      $insert_data;
      $insert_data['click_num'] = isset ($clicks[$key]) && $clicks[$key] ? $clicks[$key] : 0;
      $insert_data['install_num'] = isset ($conversions[$key]) && $conversions[$key]? $conversions[$key] : 0;

      $result{$key} = $insert_data;
    }

    //debug ($result);
    $this->set ('datas', $result);
  }
  function collectPerTimeline ($table, $datas)
  {
    $result = array ();
    foreach ($datas as $data)
    {
      $timeline = substr ($data["$table"]['created'], 0, 13);

      if (array_key_exists ($timeline, $result))
      {
        $result{"$timeline"} = $result{"$timeline"} + 1;
      }
      else
      {
        $result{"$timeline"} = 1;
      }
    }

    return $result;
  }
  function exceptDuplicateRecord ($table_name, $datas)
  {
    $result = array ();
    $dup = array ();
    foreach ($datas as $data)
    {
      $key = 0;

      if ($data["$table_name"]['dpidraw'])
      {
        if (array_key_exists ($data["$table_name"]['dpidraw'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['dpidraw']; }
      }
      if ($data["$table_name"]['dpidmd5'])
      {
        if (array_key_exists ($data["$table_name"]['dpidmd5'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['dpidmd5']; }
      }
      if ($data["$table_name"]['dpidsha1'])
      {
        if (array_key_exists ($data["$table_name"]['dpidsha1'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['dpidsha1']; }
      }
      if ($data["$table_name"]['openudid'])
      {
        if (array_key_exists ($data["$table_name"]['openudid'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['openudid']; }
      }
      if ($data["$table_name"]['idfa'])
      {
        if (array_key_exists ($data["$table_name"]['idfa'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['idfa']; }
      }
      if ($data["$table_name"]['idfamd5'])
      {
        if (array_key_exists ($data["$table_name"]['idfamd5'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['idfamd5']; }
      }
      if ($data["$table_name"]['idfasha1'])
      {
        if (array_key_exists ($data["$table_name"]['idfasha1'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['idfasha1']; }
      }
      if ($data["$table_name"]['macaddr'])
      {
        if (array_key_exists ($data["$table_name"]['macaddr'], $dup))
          continue;

        if ($key == 0) { $key = $data["$table_name"]['macaddr']; }
      }

      $dup{$key} = $data;
      array_push ($result, $data);
    }

    return $result;
  }
}
