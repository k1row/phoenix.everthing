<?php

App::uses('Controller', 'Controller');

class SampleController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  function index()
  {
  }
  function index2()
  {
    $this->loadModel ('Click');
    $this->loadModel ('PublisherMaster');

    $publisher_id = "total";
    $this->set ('publisher_id', $publisher_id);

    $this->getPubliser ("5730e8d62eb8e2f2446356897032f74e01928071");
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
  function index3()
  {
  }

  function get_option($sort='')
  {
    $ingredients = array(
      'meat' => array('sirloin', 'rib', 'tongue'),
      'green' => array('cabbage', 'eggplant'),
      'fish' => array('tuna'),
      );

    if (!$this->RequestHandler->isAjax())
    {
      //die('Not found');
    }

    if (!isset($ingredients[$sort]))
    {
      $sort = 'meat';
    }

    configure::write('debug', 0);
    $this->set('ingredients', $ingredients[$sort]);
  }

  public function pagingGetOrders()
  {
    $json = array(
      'totalRecords' => 1,
      'curPage' => 1,
      'data' => array ("France", "Paul Henriot", 10248));

    $this->autoLayout = false;
    $this->autoRender = false;

    //$this->set('posts', $json);
    //$this->set('_serialize', array('posts'));

    //return new CakeResponse(array('body' => json_encode($json)));
    $json = json_encode($json);
    echo $json;
  }

  public function a()
  {
    $this->autoRender = false;
  }

  // リクエストを送る側のページ
  public function ajax_request()
  {
  }

  // リクエストを受け取るページ
  public function ajax_return()
  {
    $this->autoLayout = false;
    $this->autoRender = false;
    echo 'Hello World!';

    // $this->request->is('ajax') を使うといいかも
  }

  public function add() {
    $comps = $this->Daicho->Comp->find('list');
    $this->Daicho->Tanto->virtualFields = array('name' => "CONCAT(Tanto.sei, ' ', Tanto.mei)");
    $condition = array(
      'fields' => array(
        'Tanto.id',
        'Tanto.name'
        ),
      );
    $tantos = $this->Daicho->Tanto->find('list', $condition);
  }
  public function ajax_tantos() {
    $this->Daicho->Tanto->virtualFields = array('name' => "CONCAT(Tanto.sei, ' ', Tanto.mei)");
    $this->set('options',
               $this->Daicho->Tanto->find('list',
                                          array(
                                            'conditions' => array(
                                              'Tanto.comp_id' => $this->params['url']['data']['Daicho']['comp_id']
                                              ),
                                            'fields' => array(
                                              'Tanto.id',
                                              'Tanto.name'
                                              )
                                            )
                                          )
               );
  }

  function hoge()
  {
    // Set Title
    $this->set('title_for_layout', "CakePHP+jQueryでAjax");
    // Ajax
    if ($this->RequestHandler->isAjax())
    {
      // Configure for ajax
      Configure::write('debug', 0);
      $this->autoRender = false;

      // Output
      echo $this->params['form']['input_text'];
    }
  }

  function tako ()
  {
  }
  function getAr ()
  {
    $this->autoLayout = false;
    $this->autoRender = false;

    // この変数はMySQLの検索用クリエなどに使えば良い
    $target = $_POST['val'];
    // 本当はMySQLなどデータベースから情報を引っ張ってくるけど、
    // ここはとりあえず適当に多次元連想配列を作って返す。
    $result = array();
    $result[] = array('name'=>'織田信長','age'=>'35','address'=>'尾張');
    $result[] = array('name'=>'徳川家康','age'=>'30','address'=>'三河');
    $result[] = array('name'=>'武田信玄','age'=>'29','address'=>'甲斐');
    // 配列をエンコードしないと化けるみたい。
    $result = json_encode($result);
    echo $result;
  }

  function jqgrid ()
  {
    $this->loadModel ('Click');
    $this->loadModel ('PublisherMaster');

    $this->getPubliser ("5730e8d62eb8e2f2446356897032f74e01928071");

    $this->loadModel ('AdminAnalyzeCampaign');
    $this->loadModel ('AdminAnalyzeCampaignPerDay');
    $this->loadModel ('AdminAnalyzePublisherPerDay');
    $this->loadModel ('CampaignMaster');
    $this->loadModel ('Click');
    $this->loadModel ('PublisherMaster');

    $this->autoLayout = false;
    $this->autoRender = false;

    $page = 1;//$this->params['url']['page']; // get the requested page
    $limit = 50;//$this->params['url']['rows']; // get how many rows we want to have into the grid
    //$sidx = $this->params['url']['sidx']; // get index row - i.e. user click to sort
    //$sord = $this->params['url']['sord']; // get the direction
    //if(!$sidx) $sidx =1;

    $appsigid = "5730e8d62eb8e2f2446356897032f74e01928071";

    $publisher_id = "total";

    if (isset ($this->params['url']['pid']) && $this->params['url']['pid'] && !($this->params['url']['pid'] === 'total'))
    {
      $publisher_id = $this->params['url']['pid'];
      $count = $this->AdminAnalyzePublisherPerDay->find('count', array(
        'conditions' => array ('AdminAnalyzePublisherPerDay.appsigid' => $appsigid,
                               'AdminAnalyzePublisherPerDay.publisher_id' => $this->params['url']['pid'])));

      if ($count > 0)
      {
        $total_pages = ceil($count / $limit);
      }
      else
      {
        $total_pages = 0;
      }
      if ($page > $total_pages) $page = $total_pages;

      $responce['page'] = $page;
      $responce['total'] = $total_pages;
      $responce['records'] = $count;

      $datas = $this->AdminAnalyzePublisherPerDay->find ('all', array (
        'conditions' => array ('AdminAnalyzePublisherPerDay.appsigid' => $appsigid,
                               'AdminAnalyzePublisherPerDay.publisher_id' => $this->params['url']['pid']),
        'order' => array('AdminAnalyzePublisherPerDay.target_date' => 'ASC'),
        ));
    }
    else
    {
      $count = $this->AdminAnalyzeCampaignPerDay->find('count', array(
        'conditions' => array ('AdminAnalyzeCampaignPerDay.appsigid' => $appsigid)));

      if ($count >0)
      {
        $total_pages = ceil($count / $limit);
      }
      else
      {
        $total_pages = 0;
      }
      if ($page > $total_pages) $page = $total_pages;

      $responce['page'] = $page;
      $responce['total'] = $total_pages;
      $responce['records'] = $count;

      $datas = $this->AdminAnalyzeCampaignPerDay->find ('all', array (
        'conditions' => array ('AdminAnalyzeCampaignPerDay.appsigid' => $appsigid),
        'order' => array('AdminAnalyzeCampaignPerDay.target_date' => 'ASC'),
        ));
    }

    // 適切なヘッダー情報を設定するべき
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
    echo "<records>".$count."</records>";

    // CDATAにテキストデータを必ず置く。
    foreach ($datas as $data)
    {
      if (isset ($this->params['url']['pid']) && $this->params['url']['pid'])
      {
        echo "<row id='". $data['AdminAnalyzePublisherPerDay']['id']."'>";
        echo "<cell>". $data['AdminAnalyzePublisherPerDay']['id']."</cell>";
        echo "<cell>". $data['AdminAnalyzePublisherPerDay']['target_date']."</cell>";
        echo "<cell>". $data['AdminAnalyzePublisherPerDay']['click_num']."</cell>";
        echo "<cell>". $data['AdminAnalyzePublisherPerDay']['install_num']."</cell>";
        echo "<cell><![CDATA[". $data['AdminAnalyzePublisherPerDay']['cpi']."]]></cell>";
        echo "</row>";
      }
      else
      {
        echo "<row id='". $data['AdminAnalyzeCampaignPerDay']['id']."'>";
        echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['id']."</cell>";
        echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['target_date']."</cell>";
        echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['click_num']."</cell>";
        echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['install_num']."</cell>";
        echo "<cell><![CDATA[". $data['AdminAnalyzeCampaignPerDay']['cvr']."]]></cell>";
        echo "</row>";
      }
    }
    echo "</rows>";

    $this->set ('publisher_id', $publisher_id);
  }

  function jqgrid2 ()
  {
    $this->loadModel ('AdminAnalyzePublisherPerDay');

    $this->autoLayout = false;
    $this->autoRender = false;

    $page = 1;//$this->params['url']['page']; // get the requested page
    $limit = 50;//$this->params['url']['rows']; // get how many rows we want to have into the grid
    //$sidx = $this->params['url']['sidx']; // get index row - i.e. user click to sort
    //$sord = $this->params['url']['sord']; // get the direction
    //if(!$sidx) $sidx =1;

    $appsigid = "5730e8d62eb8e2f2446356897032f74e01928071";

    $count = $this->AdminAnalyzePublisherPerDay->find('count', array(
      'conditions' => array ('AdminAnalyzePublisherPerDay.appsigid' => $appsigid)));

    if ($count >0)
    {
      $total_pages = ceil($count / $limit);
    }
    else
    {
      $total_pages = 0;
    }
    if ($page > $total_pages) $page = $total_pages;

    $responce['page'] = $page;
    $responce['total'] = $total_pages;
    $responce['records'] = $count;

    $datas = $this->AdminAnalyzePublisherPerDay->find ('all', array (
      'conditions' => array ('AdminAnalyzePublisherPerDay.appsigid' => $appsigid),
      'order' => array('AdminAnalyzePublisherPerDay.target_date' => 'ASC'),
      ));

    // 適切なヘッダー情報を設定するべき
    if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") )
    {
      header("Content-type: application/xhtml+xml;charset=utf-8");
    } else
    {
      header("Content-type: text/xml;charset=utf-8");
    }
    echo "<?xml version='1.0' encoding='utf-8'?>";
    echo "<rows>";
    echo "<page>".$page."</page>";
    echo "<total>".$total_pages."</total>";
    echo "<records>".$count."</records>";

    // CDATAにテキストデータを必ず置く。
    foreach ($datas as $data)
    {
      echo "<row id='". $data['AdminAnalyzePublisherPerDay']['id']."'>";
      echo "<cell>". $data['AdminAnalyzePublisherPerDay']['id']."</cell>";
      echo "<cell>". $data['AdminAnalyzePublisherPerDay']['target_date']."</cell>";
      echo "<cell>". $data['AdminAnalyzePublisherPerDay']['click_num']."</cell>";
      echo "<cell><![CDATA[". $data['AdminAnalyzePublisherPerDay']['install_num']."]]></cell>";
      echo "</row>";
    }
    echo "</rows>";
  }

  function jqgrid_getgroups ()
  {
    $target_date = $this->params['url']['target_date'];

    $this->loadModel ('CampaignMaster');
    $this->loadModel ('Click');
    $this->loadModel ('Conversion');

    //$appsigid = $this->params['url']['cid'];
    $appsigid = "ded505b7192f76bb7c588643e7cfb4a07965f6a1";

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


    // 適切なヘッダー情報を設定するべき
    if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") )
    {
      header("Content-type: application/xhtml+xml;charset=utf-8");
    } else
    {
      header("Content-type: text/xml;charset=utf-8");
    }
    echo "<?xml version='1.0' encoding='utf-8'?>";
    echo "<rows>";
    //echo "<page>".$page."</page>";
    //echo "<total>".$total_pages."</total>";
    //echo "<records>".$count."</records>";

    // CDATAにテキストデータを必ず置く。
    foreach ($datas as $data)
    {
      echo "<row id='". $data['AdminAnalyzeCampaignPerDay']['id']."'>";
      echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['id']."</cell>";
      echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['target_date']."</cell>";
      echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['click_num']."</cell>";
      echo "<cell>". $data['AdminAnalyzeCampaignPerDay']['install_num']."</cell>";
      echo "<cell><![CDATA[". $data['AdminAnalyzeCampaignPerDay']['cvr']."]]></cell>";
      echo "</row>";
    }
    echo "</rows>";
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
