<?php

App::uses('Controller', 'Controller');

class CampaignreportController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler', 'Cookie');
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

  function getCampaignDetail ($appsigid = '', $pid = '')
  {
    if (!isset ($appsigid) || !$appsigid) { return; }

    $this->loadModel ('Click');
    $this->loadModel ('PublisherMaster');
    $this->loadModel ('AdminAnalyzeCreative');

    $this->set ('appsigid', $appsigid);

    $publisher_id = "total";
    if ($pid)
    {
      $publisher_id = $pid;
    }

    $this->getPubliser ($appsigid);
    //$this->getCreative ($appsigid, $publisher_id);

    $this->set ('publisher_id', $publisher_id);
    $this->set ('time', time ());
  }

  function getCreativeDetail ($appsigid = '', $pid = '')
  {
    $this->set ('appsigid', $appsigid);
    $this->set ('publisher_id', $pid);

    if (!isset ($appsigid) || !$appsigid) { return; }
    if (!isset ($pid) || !$pid || $pid == 'total')
    {
      $this->set ('creatives', array ());
      return;
    }

    $this->loadModel ('AdminAnalyzeCreative');
    $this->getCreative ($appsigid, $pid);
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

  function getCreative ($appsigid, $publisher_id)
  {
    $datas = $this->AdminAnalyzeCreative->find ('all', array (
      'conditions' => array ('AdminAnalyzeCreative.appsigid' => $appsigid,
                             'AdminAnalyzeCreative.publisher_id' => $publisher_id)));

    $this->set ('creatives', $datas);
  }

  function jqgrid ($appsigid = '', $date = '')
  {
    $this->loadModel ('Click');
    $this->loadModel ('PublisherMaster');
    $this->loadModel ('CampaignMaster');
    $this->loadModel ('AdminAnalyzePerPublisher');
    $this->loadModel ('AdminAnalyzeCreative');

    $this->getPubliser ($appsigid);

    $this->autoLayout = false;
    $this->autoRender = false;

    $publisher_id = "total";

    if (isset ($this->params['url']['creid']) && $this->params['url']['creid'])
    {
      $creative_id = $this->params['url']['creid'];
      $publisher_id = $this->params['url']['pid'];
      $this->doProcessWithCreative ($appsigid, $date, $publisher_id, $creative_id);
    }
    else
    {
      if (!isset ($this->params['url']['pid']) || $this->params['url']['pid'] === 'total')
      {
        $this->doProcessTotal ($appsigid, $date);
      }
      else
      {
        $publisher_id = $this->params['url']['pid'];
        $this->doProcessNotTotal ($appsigid, $date, $publisher_id);
      }
    }

    $this->set ('publisher_id', $publisher_id);
  }

  function doProcessTotal ($appsigid, $date)
  {
    $conditions =  array ('AdminAnalyzePerPublisher.appsigid' => $appsigid,
                          'AdminAnalyzePerPublisher.target_date LIKE' => "$date%");

    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'conditions' => $conditions,
      'order' => array('AdminAnalyzePerPublisher.target_datetime' => 'ASC')));

    $this->makeGrid ($datas);
  }

  function doProcessNotTotal ($appsigid, $date, $publisher_id)
  {
    $conditions =  array ('AdminAnalyzePerPublisher.appsigid' => $appsigid,
                          'AdminAnalyzePerPublisher.publisher_id' => $this->params['url']['pid'],
                          'AdminAnalyzePerPublisher.target_date LIKE' => "$date%");

    $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
      'conditions' => $conditions,
      'order' => array('AdminAnalyzePerPublisher.target_datetime' => 'ASC')));

    $this->makeGrid ($datas);
  }

  function makeGrid ($datas)
  {
    $result = array ();
    foreach ($datas as $data)
    {
      $key = $data['AdminAnalyzePerPublisher']['target_date'];
      if (!array_key_exists ($key, $result))
      {
        $result{$key} = $data['AdminAnalyzePerPublisher'];
        continue;
      }

      $result{$key}['click_num'] += $data['AdminAnalyzePerPublisher']['click_num'];
      $result{$key}['install_num'] += $data['AdminAnalyzePerPublisher']['install_num'];
    }

    $count = count($result);
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

    $count = 0;
    $sum_click_num = 0;
    $sum_install_num = 0;
    $sum_cpi = 0;
    $sum_sales = 0;

    foreach ($result as $ret)
    {
      echo "<row id='". $ret['id']."'>";
      echo "<cell>". $ret['target_date']."</cell>";
      echo "<cell><![CDATA[".number_format ($ret['click_num'])."]]></cell>";
      echo "<cell><![CDATA[".number_format ($ret['install_num'])."]]></cell>";

      if ($ret['click_num'] == 0 || $ret['install_num'] == 0)
      {
        $ret['cvr'] = 0;
      }
      else
      {
        $ret['cvr'] = round ($ret['install_num'] / $ret['click_num'], 3);
      }
      echo "<cell><![CDATA[".$ret['cvr']."]]></cell>";
      echo "<cell>$".$ret['cpi']."</cell>";

      $ret['sales'] = $ret['install_num'] * $ret['cpi'];
      echo "<cell>$". $ret['sales']."</cell>";
      echo "</row>";

      $count = $count + 1;

      $sum_click_num += $ret['click_num'];
      $sum_install_num += $ret['install_num'];
      $sum_cpi += $ret['cpi'];
      $sum_sales += $ret['sales'];
    }
    // Summary
    {
      echo "<row id='total'>";
      echo "<cell>This month total</cell>";
      echo "<cell><![CDATA[".number_format ($sum_click_num)."]]></cell>";
      echo "<cell><![CDATA[".number_format ($sum_install_num)."]]></cell>";

      if ($sum_install_num == 0 || $sum_click_num == 0)
      {
        echo "<cell><![CDATA[0]]></cell>";
      }
      else
      {
        echo "<cell><![CDATA[".round ($sum_install_num / $sum_click_num, 3)."]]></cell>";
      }
      echo "<cell> - </cell>";
      echo "<cell><![CDATA[$".number_format ($sum_sales)."]]></cell>";
      echo "</row>";
    }
    echo "</rows>";
  }

  function doProcessWithCreative ($appsigid, $date, $publisher_id, $creative_id)
  {
    $conditions =  array ('AdminAnalyzeCreative.appsigid' => $appsigid,
                          'AdminAnalyzeCreative.publisher_id' => $this->params['url']['pid'],
                          'AdminAnalyzeCreative.creative_id' => $this->params['url']['creid'],
                          'AdminAnalyzeCreative.target_date LIKE' => "$date%");

    $count = $this->AdminAnalyzeCreative->find('count', array(
      'conditions' => $conditions));

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

    $datas = $this->AdminAnalyzeCreative->find ('all', array (
      'conditions' => $conditions,
      'order' => array('AdminAnalyzeCreative.target_date' => 'ASC')));

    if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") )
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

    $count = 0;
    $sum_click_num = 0;
    $sum_install_num = 0;
    $sum_cpi = 0;
    $sum_sales = 0;

    // CDATAにテキストデータを必ず置く。
    foreach ($datas as $data)
    {
      echo "<row id='". $data['AdminAnalyzeCreative']['id']."'>";
      echo "<cell>". $data['AdminAnalyzeCreative']['target_date']."</cell>";
      echo "<cell><![CDATA[".number_format ($data['AdminAnalyzeCreative']['click_num'])."]]></cell>";
      echo "<cell><![CDATA[".number_format ($data['AdminAnalyzeCreative']['install_num'])."]]></cell>";
      echo "<cell>". $data['AdminAnalyzeCreative']['cvr']."</cell>";
      echo "<cell>". $data['AdminAnalyzeCreative']['cpi']."</cell>";
      echo "<cell>$". $data['AdminAnalyzeCreative']['sales']."</cell>";
      echo "</row>";

      $count = $count + 1;

      $sum_click_num += $data['AdminAnalyzeCreative']['click_num'];
      $sum_install_num += $data['AdminAnalyzeCreative']['install_num'];
      $sum_cpi += $data['AdminAnalyzeCreative']['cpi'];
      $sum_sales += $data['AdminAnalyzeCreative']['sales'];
    }

    // Summary
    {
      echo "<row id='total'>";
      echo "<cell>This month total</cell>";
      echo "<cell><![CDATA[".number_format ($sum_click_num)."]]></cell>";
      echo "<cell><![CDATA[".number_format ($sum_install_num)."]]></cell>";

      if ($sum_install_num == 0 && $sum_click_num == 0)
      {
        echo "<cell><![CDATA[0]]></cell>";
      }
      else
      {
        echo "<cell><![CDATA[".round ($sum_install_num / $sum_click_num, 3)."]]></cell>";
      }
      echo "<cell> - </cell>";
      echo "<cell><![CDATA[$".number_format ($sum_sales)."]]></cell>";
      echo "</row>";
    }

    echo "</rows>";
  }

  function jqgridSub ($appsigid = '', $date = '', $publisher_id = '')
  {
    $this->autoLayout = false;
    $this->autoRender = false;

    if (!isset ($appsigid) || !$appsigid) { return; }
    if (!isset ($date) || !$date) { return; }

    $this->loadModel ('CampaignMaster');
    $this->loadModel ('AdminAnalyzePerPublisher');

    $conditions =  array ('AdminAnalyzePerPublisher.appsigid' => $appsigid,
                          'AdminAnalyzePerPublisher.target_date LIKE' => "$date%");
    if ($publisher_id == 'total' || !$publisher_id)
    {
      $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
        'conditions' => $conditions,
        'order' => array('AdminAnalyzePerPublisher.target_datetime' => 'ASC')));

      $this->makeSubGridTotal ($datas);
    }
    else
    {
      $conditions['AdminAnalyzePerPublisher.publisher_id'] = $publisher_id;

      $datas = $this->AdminAnalyzePerPublisher->find ('all', array (
        'conditions' => $conditions,
        'order' => array('AdminAnalyzePerPublisher.target_datetime' => 'ASC')));

      $this->makeSubGrid ($datas);
    }
  }

  function makeSubGridTotal ($datas)
  {
    $result = array ();
    foreach ($datas as $data)
    {
      $key = $data['AdminAnalyzePerPublisher']['target_datetime'];
      if (!array_key_exists ($key, $result))
      {
        $result{$key} = $data['AdminAnalyzePerPublisher'];
        continue;
      }

      $result{$key}['click_num'] += $data['AdminAnalyzePerPublisher']['click_num'];
      $result{$key}['install_num'] += $data['AdminAnalyzePerPublisher']['install_num'];
      $result{$key}['sales'] += $data['AdminAnalyzePerPublisher']['sales'];
    }

    $count = count($result);
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

    foreach ($result as $ret)
    {
      echo "<row id='". $ret['id']."'>";
      echo "<cell>". substr($ret['target_datetime'], 0, 13)."</cell>";
      echo "<cell><![CDATA[".number_format ($ret['click_num'])."]]></cell>";
      echo "<cell><![CDATA[".number_format ($ret['install_num'])."]]></cell>";
      echo "<cell> - </cell>";
      echo "<cell>". $ret['cpi']."</cell>";

      $ret['sales'] = $ret['install_num'] * $ret['cpi'];
      echo "<cell>$". $ret['sales']."</cell>";
      echo "</row>";
    }
    echo "</rows>";

  }

  function makeSubGrid ($datas)
  {
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

    foreach ($datas as $data)
    {
      echo "<row id='". $data['AdminAnalyzePerPublisher']['id']."'>";
      echo "<cell>". substr($data['AdminAnalyzePerPublisher']['target_datetime'], 0, 13)."</cell>";
      echo "<cell><![CDATA[".number_format ($data['AdminAnalyzePerPublisher']['click_num'])."]]></cell>";
      echo "<cell><![CDATA[".number_format ($data['AdminAnalyzePerPublisher']['install_num'])."]]></cell>";
      echo "<cell>". $data['AdminAnalyzePerPublisher']['cvr']."</cell>";
      echo "<cell>". $data['AdminAnalyzePerPublisher']['cpi']."</cell>";

      $data['AdminAnalyzePerPublisher']['sales'] = $data['AdminAnalyzePerPublisher']['install_num'] * $data['AdminAnalyzePerPublisher']['cpi'];
      echo "<cell>$". $data['AdminAnalyzePerPublisher']['sales']."</cell>";
      echo "</row>";
   }
    echo "</rows>";
  }

  function csv ()
  {
    $this->loadModel ('CampaignMaster');
    $this->loadModel ('PublisherMaster');

    $this->layout = false;
    $this->autoRender = false;

    if (!empty ($_POST['data']))
    {
      $data = $_POST['data'];
      $numRows = count($data);

      $appsigid = $data[0][0];
      $publisher_id = $data[0][1];
      $creative_id = $data[0][2];

      if ($creative_id)
      {
        $filename = $this->getAppName ($appsigid)."[".$this->getPublisherName ($publisher_id)."] - (". $creative_id. ").csv";
      }
      else
      {
        $filename = $this->getAppName ($appsigid)."[".$this->getPublisherName ($publisher_id)."].csv";
      }

      header("Content-Type: application/octet-stream");
      header("Content-Disposition: attachment; filename=$filename");

      // output title
      $header = $data[1];
      $str = implode(",", $header);
      echo mb_convert_encoding($str, "SJIS-win", "UTF-8");
      echo "\r\n";

      // output data
      for ($i = 2; $i < $numRows; $i++)
      {
        $str = implode(",", str_replace (",", "", $data[$i]));
        echo mb_convert_encoding($str, "SJIS-win", "UTF-8");
        echo "\r\n";
      }
    }
  }
  function getAppName ($appsigid)
  {
    $datas = $this->CampaignMaster->find ('all', array (
      'conditions' => array ('CampaignMaster.id' => $appsigid)));

    return $datas[0]['CampaignMaster']['name'];
  }
  function getPublisherName ($pid)
  {
    if ($pid === 'total') { return "total"; }
    $datas = $this->PublisherMaster->find ('all', array (
      'conditions' => array ('PublisherMaster.id' => $pid)));

    return $datas[0]['PublisherMaster']['owner_name'];
  }
}
