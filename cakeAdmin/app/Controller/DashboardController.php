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
  }

  function jqgrid ()
  {
    $this->loadModel ('CampaignMaster');
    $this->loadModel ('AdminAnalyzePerPublisher');

    $this->autoLayout = false;
    $this->autoRender = false;

    $today = date ("Y-m-d", strtotime ("now"));
    $six_days_ago = date ("Y-m-d", strtotime ("-6 day"));

    $conditions = array (array ('AdminAnalyzePerPublisher.target_date >=' => $six_days_ago),
                         array ('AdminAnalyzePerPublisher.target_date <=' => $today));

    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'fields' => 'DISTINCT AdminAnalyzePerPublisher.appsigid',
      'conditions' => $conditions,
      'order' => array('AdminAnalyzePerPublisher.advertiser_id', 'AdminAnalyzePerPublisher.campaign_name')));

    $result = array ();
    foreach ($datas as $data)
    {
      if ($data['AdminAnalyzePerPublisher']['appsigid'] === '2045444883139e79c4246183595c2df2613d6192') { continue; }
      if ($data['AdminAnalyzePerPublisher']['appsigid'] === '40b247a5c58ea510c773942a6ba0aa3a7467cc35') { continue; }

      $conditions{'appsigid'} = $data['AdminAnalyzePerPublisher']['appsigid'];
      $result{$data['AdminAnalyzePerPublisher']['appsigid']} = $this->collectEachCampaignData ($data['AdminAnalyzePerPublisher']['appsigid'], $conditions);
    }

    //$this->element('sql_dump');
    $this->makeGridData ($result);
  }

  function collectEachCampaignData ($appsigid, $conditions)
  {
    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'conditions' => $conditions,
      'order' => array('AdminAnalyzePerPublisher.target_datetime' => 'DESC')));

    $name = $this->getAppName ($appsigid);

    $isInit = 0;
    $result = array ();
    foreach ($datas as $data)
    {
      $key = $data['AdminAnalyzePerPublisher']['target_date'];
      if (!array_key_exists ($key, $result))
      {
        $result{$key}['appsigid'] = $appsigid;
        $result{$key}['name'] = $name;
        $result{$key}['click_num'] = $data['AdminAnalyzePerPublisher']['click_num'];
        $result{$key}['install_num'] = $data['AdminAnalyzePerPublisher']['install_num'];
        $isInit = 1;
        continue;
      }

      $result{$key}['click_num'] += $data['AdminAnalyzePerPublisher']['click_num'];
      $result{$key}['install_num'] += $data['AdminAnalyzePerPublisher']['install_num'];
    }

    if (!$isInit)
    {
      $index = date ("Y-m-d", strtotime ("now"));
      $result{$index}['appsigid'] = $appsigid;
      $result{$index}['name'] = $name;
    }

    //debug($result);
    return $result;
  }

  function makeGridData ($datas)
  {
    //debug($datas);
    $count = count($datas);
    $page = ($this->params['url']['page']) ? $this->params['url']['page'] : 1;
    $limit = ($this->params['url']['rows']) ? $this->params['url']['rows'] : 50;
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

    $this->echoDate ();

    $install_total_today = 0;
    $install_total_yesterday = 0;
    $install_total_2days = 0;
    $install_total_3days = 0;
    $install_total_4days = 0;
    $install_total_5days = 0;
    $install_total_6days = 0;

    foreach ($datas as $data)
    {
      if (!$this->initGridHeader ($data))
      {
      }
      $index = date ("Y-m-d", strtotime ("now"));
      $index2 = date ("Y-m-d", strtotime ("-1 day"));

      for ($i = 0; $i <= 6; $i++)
      {
        $index = date ("Y-m-d", strtotime ("-$i day"));
        if (isset ($data{"$index"}) && $data{"$index"})
        {
          if (isset ($data{"$index"}{'install_num'}))
          {
            echo "<cell>".number_format($data{"$index"}{'install_num'})."</cell>";
            $current_install_num = $data{"$index"}['install_num'];
          }
          else
          {
            echo "<cell> - </cell>";
            $current_install_num = 0;
          }
        }
        else
        {
          echo "<cell> - </cell>";
          $current_install_num = 0;
        }

        if ($i == 0) { $install_total_today += $current_install_num; }
        else if ($i == 1) { $install_total_yesterday += $current_install_num; }
        else if ($i == 2) { $install_total_2days += $current_install_num; }
        else if ($i == 3) { $install_total_3days += $current_install_num; }
        else if ($i == 4) { $install_total_4days += $current_install_num; }
        else if ($i == 5) { $install_total_5days += $current_install_num; }
        else if ($i == 6) { $install_total_6days += $current_install_num; }
      }
      echo "</row>";
    }
    echo "<row id='0'>";
    echo "<cell> </cell>";
    echo "<cell>Total</cell>";
    echo "<cell> </cell>";
    echo "<cell>". $install_total_today ."</cell>";
    echo "<cell>". $install_total_yesterday ."</cell>";
    echo "<cell>". $install_total_2days ."</cell>";
    echo "<cell>". $install_total_3days ."</cell>";
    echo "<cell>". $install_total_4days ."</cell>";
    echo "<cell>". $install_total_5days ."</cell>";
    echo "<cell>". $install_total_6days ."</cell>";
    echo "</row>";
    echo "</rows>";
  }

  function initGridHeader ($data)
  {
    for ($i = 0; $i <= 6; $i++)
    {
      $index = date ("Y-m-d", strtotime ("-$i day"));
      if (isset ($data{"$index"}) && $data{"$index"})
      {
        echo "<row id='".$data{"$index"}{'appsigid'}."'>";
        echo "<cell>".$data{"$index"}{'appsigid'}."</cell>";
        echo "<cell>".$data{"$index"}{'name'}."</cell>";
        $cpi = $this->getCurrectCPI ($data{"$index"}{'appsigid'});
        echo "<cell>$".$cpi."</cell>";
        return 1;
      }
    }
    return 0;
  }

  function getAppName ($appsigid)
  {
    $this->loadModel('CampaignMaster');
    $datas = $this->CampaignMaster->find ('all', array (
      'fields' => 'name',
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    return $datas[0]['CampaignMaster']['name'];
  }

  function getCurrectCPI ($appsigid)
  {
    $this->loadModel('CampaignMaster');
    $datas = $this->CampaignMaster->find ('all', array (
      'fields' => 'cpi',
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    return $datas[0]['CampaignMaster']['cpi'];
  }

  function putEachDaysData (&$insert_data, $day, $click_num, $install_num)
  {
    $click_str = $day . "_click_num";
    $install_str = $day . "_install_num";

    $insert_data["$click_str"] = $click_num;
    $insert_data["$install_str"] = $install_num;
  }

  function echoDate ()
  {
    echo "<row id='0'>";
    echo "<cell></cell>";
    echo "<cell></cell>";
    echo "<cell></cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("now"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-1 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-2 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-3 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-4 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-5 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-6 day"))."</cell>";
    echo "</row>";
  }
  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ();
  }
}
