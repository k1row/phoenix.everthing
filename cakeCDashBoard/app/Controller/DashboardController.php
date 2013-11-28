<?php

App::uses('Controller', 'Controller');

class DashboardController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "dashboard";

  public function index ()
  {
    $this->loadModel ('CreativeDashboardForDena');

    $datas = $this->CreativeDashboardForDena->find ('all', array (
      'fields' => 'DISTINCT CreativeDashboardForDena.campaign_name',
      'order' => array('CreativeDashboardForDena.campaign_name')));

    $this->set ('campaigns', $datas);
  }

  function getImpressionGridData ($campaign_name = '')
  {
    $result = $this->getGridData ($campaign_name, "views");
    $this->makeGridData ($result);
  }

  function getClickGridData ($campaign_name = '')
  {
    $result = $this->getGridData ($campaign_name, "views");
    $this->makeGridData ($result);
  }

  function getInstallGridData ($campaign_name = '')
  {
    $result = $this->getGridData ($campaign_name, "conversions");
    $this->makeGridData ($result);
  }

  function getRelevancyGridData ($campaign_name = '')
  {
    $result = $this->getGridData ($campaign_name, "ctvr");
    $this->makeGridData ($result, 1);
  }

  function getCtrGridData ($campaign_name = '')
  {
    $result = $this->getGridData ($campaign_name, "ctr");
    $this->makeGridData ($result, 1);
  }

  function getCvrGridData ($campaign_name = '')
  {
    $result = $this->getGridData ($campaign_name, "cvr");
    $this->makeGridData ($result, 1);
  }

  function getGridData ($campaign_name = '', $field)
  {
    $this->autoLayout = false;
    $this->autoRender = false;

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

    $sidx = "creative";
    if (isset ($this->params['url']['sidx']) || ($this->params['url']['sidx']))
    {
      $sidx = $this->params['url']['sidx'];
    }
    $sord = "desc";
    if (isset ($this->params['url']['sord']) || ($this->params['url']['sord']))
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
      $result{$key}[$data['CreativeDashboardForDena']['target_date']] = $data['CreativeDashboardForDena'][$field];
    }

    //debug ($result);
    return $result;
  }

  function makeGridData ($datas, $is_rate = 0)
  {
    // To get last Sunday
    $sunday1 = (strtotime ('sunday') == strtotime ('today')) ? strtotime ('last sunday') : strtotime ('-2 sunday');

    // To get 2 week ago Sunday
    $sunday2 = strtotime('-1 week', $sunday1);

    // To get 3 week ago Sunday
    $sunday3 = strtotime('-2 week', $sunday1);

    // To get 4 week ago Sunday
    $sunday4 = strtotime('-3 week', $sunday1);

    //debug($datas);
    $count = count($datas);
    $page = ($this->params['url']['page']) ? $this->params['url']['page'] : 1;
    $limit = ($this->params['url']['rows']) ? $this->params['url']['rows'] : 100;
    $sidx = ($this->params['url']['sidx']) ? $this->params['url']['sidx'] : 1;
    $sord = ($this->params['url']['sord']) ? $this->params['url']['sord'] : "";

    if ($count > 0)
    {
      $total_pages = ceil($count / $limit);
    }
    else
    {
      $total_pages = 0;
    }
    if ($page > $total_pages) $page = $total_pages;

    if (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") )
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
    echo "<records>".($count + 1)."</records>";

    $ymd_sunday1 = date ('Y-m-d', $sunday1);
    $ymd_sunday2 = date ('Y-m-d', $sunday2);
    $ymd_sunday3 = date ('Y-m-d', $sunday3);
    $ymd_sunday4 = date ('Y-m-d', $sunday4);

    // Header
    {
      echo "<row id='0'>";
      echo "<cell></cell>";
      echo "<cell>".$ymd_sunday4." -   ".date ('Y-m-d', strtotime('6 days', $sunday4))."</cell>";
      echo "<cell>".$ymd_sunday3." -   ".date ('Y-m-d', strtotime('6 days', $sunday3))."</cell>";
      echo "<cell>".$ymd_sunday2." -   ".date ('Y-m-d', strtotime('6 days', $sunday2))."</cell>";
      echo "<cell>".$ymd_sunday1." -   ".date ('Y-m-d', strtotime('6 days', $sunday1))."</cell>";
      echo "</row>";
    }

    foreach ($datas as $key => $data)
    {
      echo "<row id='". $data['creative'] ."'>";
      echo "<cell>".$data['creative']."</cell>";

      if ($is_rate)
      {
        if (isset ($data[$ymd_sunday4])){ echo "<cell><![CDATA[".$data[$ymd_sunday4]."]]></cell>"; }else { echo "<cell>-</cell>"; }
        if (isset ($data[$ymd_sunday3])){ echo "<cell><![CDATA[".$data[$ymd_sunday3]."]]></cell>"; }else { echo "<cell>-</cell>"; }
        if (isset ($data[$ymd_sunday2])){ echo "<cell><![CDATA[".$data[$ymd_sunday2]."]]></cell>"; }else { echo "<cell>-</cell>"; }
        if (isset ($data[$ymd_sunday1])){ echo "<cell><![CDATA[".$data[$ymd_sunday1]."]]></cell>"; }else { echo "<cell>-</cell>"; }
      }
      else
      {
        if (isset ($data[$ymd_sunday4])){ echo "<cell><![CDATA[".number_format ($data[$ymd_sunday4])."]]></cell>"; }else { echo "<cell>-</cell>"; }
        if (isset ($data[$ymd_sunday3])){ echo "<cell><![CDATA[".number_format ($data[$ymd_sunday3])."]]></cell>"; }else { echo "<cell>-</cell>"; }
        if (isset ($data[$ymd_sunday2])){ echo "<cell><![CDATA[".number_format ($data[$ymd_sunday2])."]]></cell>"; }else { echo "<cell>-</cell>"; }
        if (isset ($data[$ymd_sunday1])){ echo "<cell><![CDATA[".number_format ($data[$ymd_sunday1])."]]></cell>"; }else { echo "<cell>-</cell>"; }
      }

      echo "</row>";
    }
    echo "</rows>";
  }
}
