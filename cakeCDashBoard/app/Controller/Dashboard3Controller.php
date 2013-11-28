<?php

App::uses('Controller', 'Controller');

class Dashboard3Controller extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "dashboard";

  public function index ()
  {
  }

  function getEachNetworksCampaign ($network_name ='')
  {
    $this->loadModel ('CreativeDashboardForDena');
    $conditions = array('1' => 1);

    if ($network_name && $network_name != 0)
    {
      $conditions = array('CreativeDashboardForDena.org_filename LIKE' => "$network_name%");
    }

    $datas = $this->CreativeDashboardForDena->find ('all', array (
      'fields' => 'DISTINCT CreativeDashboardForDena.campaign_name',
      'conditions' => $conditions,
      'order' => array('CreativeDashboardForDena.campaign_name')));

    echo $this->sqlDump ();
    $this->set ('campaigns', $datas);
  }

  function getGridData ($campaign_name = '', $network_name = '')
  {
    $fetch = $this->fetchGridData ($campaign_name, $network_name);
    $result = $this->makeGridData ($fetch);
    return $this->set ('item', $result);
  }

  function fetchGridData ($campaign_name = '', $network_name = '')
  {
    //$this->autoLayout = false;
    //$this->autoRender = false;

    if (!$campaign_name) { return; }

    $this->loadModel ('CreativeDashboardForDena');

    // To get last Sunday
    $sunday1 = (strtotime ('sunday') == strtotime ('today')) ? strtotime ('last sunday') : strtotime ('-2 sunday');
    //debug ('Last Sunday : '.date ('Y-m-d', $sunday1));

    // To get 4 week ago Sunday
    $sunday4 = strtotime('-4 week', $sunday1);
    //debug ('4 week ago Sunday : '.date ('Y-m-d', $sunday4));

    $conditions = array ('CreativeDashboardForDena.campaign_name' => $campaign_name,
                         array ('CreativeDashboardForDena.target_date >=' => date ('Y-m-d', $sunday4)),
                         array ('CreativeDashboardForDena.target_date <=' => date ('Y-m-d', $sunday1)));

    if ($network_name && $network_name != 0)
    {
      $conditions['org_filename LIKE'] = $network_name."%";
    }

    $sidx = "creative";
    if (isset ($this->params['url']['sidx']) && ($this->params['url']['sidx']))
    {
      $sidx = $this->params['url']['sidx'];
    }
    $sord = "desc";
    if (isset ($this->params['url']['sord']) && ($this->params['url']['sord']))
    {
      $sord = $this->params['url']['sord'];
    }

    $datas = $this->CreativeDashboardForDena->find ('all', array (
      'conditions' => $conditions,
      'order' => array("CreativeDashboardForDena.$sidx $sord", 'CreativeDashboardForDena.target_date')));

    $result = array ();
    foreach ($datas as $data)
    {
      $key = $data['CreativeDashboardForDena']['creative'];
      $result{$key}['creative'] = $data['CreativeDashboardForDena']['creative'];
      //$result{$key}[$data['CreativeDashboardForDena']['target_date']] = $data['CreativeDashboardForDena'][$field];
      $result{$key}[$data['CreativeDashboardForDena']['target_date']] = $data['CreativeDashboardForDena'];
    }

    //echo $this->sqlDump ();
    //debug ($result);
    return $result;
  }

  function makeGridData ($datas)
  {
    // To get last Sunday
    $sunday1 = (strtotime ('sunday') == strtotime ('today')) ? strtotime ('last sunday') : strtotime ('-2 sunday');

    // To get 2 week ago Sunday
    $sunday2 = strtotime('-1 week', $sunday1);

    // To get 3 week ago Sunday
    $sunday3 = strtotime('-2 week', $sunday1);

    // To get 4 week ago Sunday
    $sunday4 = strtotime('-3 week', $sunday1);

    $ymd_sunday1 = date ('Y-m-d', $sunday1);
    $ymd_sunday2 = date ('Y-m-d', $sunday2);
    $ymd_sunday3 = date ('Y-m-d', $sunday3);
    $ymd_sunday4 = date ('Y-m-d', $sunday4);

    $result = array ();

    {
      $key = "header";
      $result{$key}['creative'] = "";
      $result{$key}['impression_4weeks_ago'] = date ('md', $sunday4)."-".date ('md', strtotime('6 days', $sunday4));
      $result{$key}['impression_3weeks_ago'] = date ('md', $sunday3)."-".date ('md', strtotime('6 days', $sunday3));
      $result{$key}['impression_2weeks_ago'] = date ('md', $sunday2)."-".date ('md', strtotime('6 days', $sunday2));
      $result{$key}['impression_1weeks_ago'] = date ('md', $sunday1)."-".date ('md', strtotime('6 days', $sunday1));

      $result{$key}['click_4weeks_ago'] = date ('md', $sunday4)."-".date ('md', strtotime('6 days', $sunday4));
      $result{$key}['click_3weeks_ago'] = date ('md', $sunday3)."-".date ('md', strtotime('6 days', $sunday3));
      $result{$key}['click_2weeks_ago'] = date ('md', $sunday2)."-".date ('md', strtotime('6 days', $sunday2));
      $result{$key}['click_1weeks_ago'] = date ('md', $sunday1)."-".date ('md', strtotime('6 days', $sunday1));

      $result{$key}['install_4weeks_ago'] = date ('md', $sunday4)."-".date ('md', strtotime('6 days', $sunday4));
      $result{$key}['install_3weeks_ago'] = date ('md', $sunday3)."-".date ('md', strtotime('6 days', $sunday3));
      $result{$key}['install_2weeks_ago'] = date ('md', $sunday2)."-".date ('md', strtotime('6 days', $sunday2));
      $result{$key}['install_1weeks_ago'] = date ('md', $sunday1)."-".date ('md', strtotime('6 days', $sunday1));

      $result{$key}['relevancy_4weeks_ago'] = date ('md', $sunday4)."-".date ('md', strtotime('6 days', $sunday4));
      $result{$key}['relevancy_3weeks_ago'] = date ('md', $sunday3)."-".date ('md', strtotime('6 days', $sunday3));
      $result{$key}['relevancy_2weeks_ago'] = date ('md', $sunday2)."-".date ('md', strtotime('6 days', $sunday2));
      $result{$key}['relevancy_1weeks_ago'] = date ('md', $sunday1)."-".date ('md', strtotime('6 days', $sunday1));

      $result{$key}['ctr_4weeks_ago'] = date ('md', $sunday4)."-".date ('md', strtotime('6 days', $sunday4));
      $result{$key}['ctr_3weeks_ago'] = date ('md', $sunday3)."-".date ('md', strtotime('6 days', $sunday3));
      $result{$key}['ctr_2weeks_ago'] = date ('md', $sunday2)."-".date ('md', strtotime('6 days', $sunday2));
      $result{$key}['ctr_1weeks_ago'] = date ('md', $sunday1)."-".date ('md', strtotime('6 days', $sunday1));

      $result{$key}['cvr_4weeks_ago'] = date ('md', $sunday4)."-".date ('md', strtotime('6 days', $sunday4));
      $result{$key}['cvr_3weeks_ago'] = date ('md', $sunday3)."-".date ('md', strtotime('6 days', $sunday3));
      $result{$key}['cvr_2weeks_ago'] = date ('md', $sunday2)."-".date ('md', strtotime('6 days', $sunday2));
      $result{$key}['cvr_1weeks_ago'] = date ('md', $sunday1)."-".date ('md', strtotime('6 days', $sunday1));
    }

    foreach ($datas as $key => $data)
    {
      $result{$key}['creative'] = $key;
      $result{$key}['impression_4weeks_ago'] = isset ($data[$ymd_sunday4]) ? number_format ($data[$ymd_sunday4]['impression']) : "";
      $result{$key}['impression_3weeks_ago'] = isset ($data[$ymd_sunday3]) ? number_format ($data[$ymd_sunday3]['impression']) : "";
      $result{$key}['impression_2weeks_ago'] = isset ($data[$ymd_sunday2]) ? number_format ($data[$ymd_sunday2]['impression']) : "";
      $result{$key}['impression_1weeks_ago'] = isset ($data[$ymd_sunday1]) ? number_format ($data[$ymd_sunday1]['impression']) : "";

      $result{$key}['click_4weeks_ago'] = isset ($data[$ymd_sunday4]) ? number_format ($data[$ymd_sunday4]['click']) : "";
      $result{$key}['click_3weeks_ago'] = isset ($data[$ymd_sunday3]) ? number_format ($data[$ymd_sunday3]['click']) : "";
      $result{$key}['click_2weeks_ago'] = isset ($data[$ymd_sunday2]) ? number_format ($data[$ymd_sunday2]['click']) : "";
      $result{$key}['click_1weeks_ago'] = isset ($data[$ymd_sunday1]) ? number_format ($data[$ymd_sunday1]['click']) : "";

      $result{$key}['install_4weeks_ago'] = isset ($data[$ymd_sunday4]) ? number_format ($data[$ymd_sunday4]['install']) : "";
      $result{$key}['install_3weeks_ago'] = isset ($data[$ymd_sunday3]) ? number_format ($data[$ymd_sunday3]['install']) : "";
      $result{$key}['install_2weeks_ago'] = isset ($data[$ymd_sunday2]) ? number_format ($data[$ymd_sunday2]['install']) : "";
      $result{$key}['install_1weeks_ago'] = isset ($data[$ymd_sunday1]) ? number_format ($data[$ymd_sunday1]['install']) : "";

      $result{$key}['relevancy_4weeks_ago'] = isset ($data[$ymd_sunday4]) ? $data[$ymd_sunday4]['relevancy'] : "";
      $result{$key}['relevancy_3weeks_ago'] = isset ($data[$ymd_sunday3]) ? $data[$ymd_sunday3]['relevancy'] : "";
      $result{$key}['relevancy_2weeks_ago'] = isset ($data[$ymd_sunday2]) ? $data[$ymd_sunday2]['relevancy'] : "";
      $result{$key}['relevancy_1weeks_ago'] = isset ($data[$ymd_sunday1]) ? $data[$ymd_sunday1]['relevancy'] : "";

      $result{$key}['ctr_4weeks_ago'] = isset ($data[$ymd_sunday4]) ? $data[$ymd_sunday4]['ctr']."%" : "";
      $result{$key}['ctr_3weeks_ago'] = isset ($data[$ymd_sunday3]) ? $data[$ymd_sunday3]['ctr']."%" : "";
      $result{$key}['ctr_2weeks_ago'] = isset ($data[$ymd_sunday2]) ? $data[$ymd_sunday2]['ctr']."%" : "";
      $result{$key}['ctr_1weeks_ago'] = isset ($data[$ymd_sunday1]) ? $data[$ymd_sunday1]['ctr']."%" : "";

      $result{$key}['cvr_4weeks_ago'] = isset ($data[$ymd_sunday4]) ? $data[$ymd_sunday4]['cvr']."%" : "";
      $result{$key}['cvr_3weeks_ago'] = isset ($data[$ymd_sunday3]) ? $data[$ymd_sunday3]['cvr']."%" : "";
      $result{$key}['cvr_2weeks_ago'] = isset ($data[$ymd_sunday2]) ? $data[$ymd_sunday2]['cvr']."%" : "";
      $result{$key}['cvr_1weeks_ago'] = isset ($data[$ymd_sunday1]) ? $data[$ymd_sunday1]['cvr']."%" : "";
    }
    //debug ($result);
    return $result;
  }
  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ();
  }
}
