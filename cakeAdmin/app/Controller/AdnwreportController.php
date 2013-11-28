<?php

App::uses('Controller', 'Controller');

class AdnwreportController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "dashboard";

  public function index ()
  {
    $this->loadModel('PublisherMaster');

    $datas = $this->PublisherMaster->find ('all', array (
      'conditions' => array('PublisherMaster.enable ' => 1,
                            'PublisherMaster.id !=' => '200000000')));

    $this->set ('publisher_datas', $datas);
  }

  function getPublisherDetail ($pid = '')
  {
    if (!isset ($pid) || !$pid) { return; }
    $this->set ('publisher_id', $pid);
  }

  function jqgrid ($pid = '')
  {
    $this->loadModel ('CampaignMaster');
    $this->loadModel ('AdminAnalyzePerPublisher');

    $this->autoLayout = false;
    $this->autoRender = false;

    $today = date ("Y-m-d", strtotime ("now"));
    $thirteen_days_ago = date ("Y-m-d", strtotime ("-13 day"));

    $conditions = array ('AdminAnalyzePerPublisher.publisher_id' => $pid,
                         array ('AdminAnalyzePerPublisher.target_date >=' => $thirteen_days_ago),
                         array ('AdminAnalyzePerPublisher.target_date <=' => $today));

    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'fields' => 'DISTINCT AdminAnalyzePerPublisher.appsigid',
      'conditions' => $conditions,
      'order' => array('AdminAnalyzePerPublisher.appsigid', 'AdminAnalyzePerPublisher.target_date')));

    $result = array ();
    foreach ($datas as $data)
    {
      if ($data['AdminAnalyzePerPublisher']['appsigid'] === '2045444883139e79c4246183595c2df2613d6192') { continue; }
      if ($data['AdminAnalyzePerPublisher']['appsigid'] === '40b247a5c58ea510c773942a6ba0aa3a7467cc35') { continue; }

      $conditions{'appsigid'} = $data['AdminAnalyzePerPublisher']['appsigid'];
      $result{$data['AdminAnalyzePerPublisher']['appsigid']} = $this->collectEachCampaignData ($data['AdminAnalyzePerPublisher']['appsigid'], $conditions);
    }

    $this->makeGridData ($result);
  }

  function collectEachCampaignData ($appsigid, $conditions)
  {
    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'conditions' => $conditions,
      'order' => array('AdminAnalyzePerPublisher.target_datetime' => 'DESC')));

    $name = $this->getAppName ($appsigid);

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
        continue;
      }

      $result{$key}['click_num'] += $data['AdminAnalyzePerPublisher']['click_num'];
      $result{$key}['install_num'] += $data['AdminAnalyzePerPublisher']['install_num'];
    }

    //debug($result);
    return $result;
  }

  function getAppName ($appsigid)
  {
    $this->loadModel('CampaignMaster');
    $datas = $this->CampaignMaster->find ('all', array (
      'file' => 'name',
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    return $datas[0]['CampaignMaster']['name'];
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

    /*
    $install_total_today = 0;
    $install_total_yesterday = 0;
    $install_total_2days = 0;
    $install_total_3days = 0;
    $install_total_4days = 0;
    $install_total_5days = 0;
    $install_total_6days = 0;
     */

    foreach ($datas as $data)
    {
      if (!$this->initGridHeader ($data))
      {
      }
      $index = date ("Y-m-d", strtotime ("now"));
      $index2 = date ("Y-m-d", strtotime ("-1 day"));

      for ($i = 0; $i <= 13; $i++)
      {
        $index = date ("Y-m-d", strtotime ("-$i day"));

        if (isset ($data{"$index"}) && $data{"$index"})
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
      echo "</row>";
    }
    echo "</rows>";
  }

  function initGridHeader ($data)
  {
    for ($i = 0; $i <= 13; $i++)
    {
      $index = date ("Y-m-d", strtotime ("-$i day"));
      if (isset ($data{"$index"}) && $data{"$index"})
      {
        echo "<row id='".$data{"$index"}{'appsigid'}."'>";
        echo "<cell>".$data{"$index"}{'appsigid'}."</cell>";
        echo "<cell>".$data{"$index"}{'name'}."</cell>";
        return 1;
      }
    }
    return 0;
  }

  function initInsertData (&$insert_data)
  {
    for ($i = 0; $i < 14; $i++)
    {
      if ($i == 0)
      {
        $this->putEachDaysData ($insert_data, "today", "-", "-");
      }
      else if ($i == 1)
      {
        $this->putEachDaysData ($insert_data, "yesterday", "-", "-");
      }
      else
      {
        $str = $i . "_days_ago";
        $this->putEachDaysData ($insert_data, $str, "-", "-");
      }
    }
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
    echo "<cell>".date ("Y-m-d", strtotime ("now"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-1 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-2 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-3 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-4 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-5 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-6 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-7 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-8 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-9 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-10 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-11 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-12 day"))."</cell>";
    echo "<cell>".date ("Y-m-d", strtotime ("-13 day"))."</cell>";
    echo "</row>";
  }

  function jqgridSub ($pid = '', $appsigid = '')
  {
    $this->autoLayout = false;
    $this->autoRender = false;

    if (!isset ($pid) || !$pid) { return; }
    if (!isset ($appsigid) || !$appsigid) { return; }

    $this->loadModel('AdminAnalyzePerPublisher');

    $today = date ("Y-m-d", strtotime ("now"));
    $two_month_ago = date ("Y-m-d", strtotime ("-2 month"));

    $conditions = array ('AdminAnalyzePerPublisher.publisher_id' => $pid,
                         'AdminAnalyzePerPublisher.appsigid' => $appsigid,
                         array ('AdminAnalyzePerPublisher.target_date >=' => $two_month_ago),
                         array ('AdminAnalyzePerPublisher.target_date <=' => $today));

    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'conditions' => $conditions,
      'order' => array('AdminAnalyzePerPublisher.target_datetime' => 'DESC')));

    if (!$datas) { return; }

    $result = array ();
    foreach ($datas as $data)
    {
      $key = $data['AdminAnalyzePerPublisher']['target_date'];
      if (!array_key_exists ($key, $result))
      {
        $result{$key}['target_date'] = $key;
        $result{$key}['click_num'] = $data['AdminAnalyzePerPublisher']['click_num'];
        $result{$key}['install_num'] = $data['AdminAnalyzePerPublisher']['install_num'];
        continue;
      }

      $result{$key}['click_num'] += $data['AdminAnalyzePerPublisher']['click_num'];
      $result{$key}['install_num'] += $data['AdminAnalyzePerPublisher']['install_num'];
    }

    $count = count($result);

    $page = (isset ($this->params['url']['page']) && $this->params['url']['page']) ? $this->params['url']['page'] : 1;
    $limit = (isset ($this->params['url']['rows']) && $this->params['url']['rows']) ? $this->params['url']['rows'] : 70;
    $sidx = (isset ($this->params['url']['sidx']) && $this->params['url']['sidx']) ? $this->params['url']['sidx'] : 1;
    $sord = (isset ($this->params['url']['sord']) && $this->params['url']['sord']) ? $this->params['url']['sord'] : "";

    if ($count > 0)
    {
      $total_pages = ceil ($count / $limit);
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
    echo "<records>".$count."</records>";

    foreach ($result as $ret)
    {
      echo "<row id='". $ret['target_date'] ."'>";
      echo "<cell>". $ret['target_date'] ."</cell>";
      echo "<cell>". $ret['click_num']  ."</cell>";
      echo "<cell>". $ret['install_num']  ."</cell>";

      if ($ret['click_num'] == 0 || $ret['install_num'] == 0)
      {
        $ret['cvr'] = 0;
      }
      else
      {
        $ret['cvr'] = round ($ret['install_num'] / $ret['click_num'], 3);
      }
      echo "<cell><![CDATA[".$ret['cvr']."]]></cell>";
      echo "</row>";
    }
    echo "</rows>";
  }
}
