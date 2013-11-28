<?php

App::uses('Controller', 'Controller');


class UploadController extends Controller
{
  public $helpers = array('Js'=> array('Jquery'));
  var $uses = null;
  var $components = array('RequestHandler');

  var $layout = "upload";

  public function index ()
  {
    session_start ();
  }

  public function fileRead ()
  {
    $this->loadModel ('CreativeDashboardForDena');

    debug ($_FILES);
    $filename = basename ($_FILES['file']['name']);
    $filepath = '/tmp/'.$filename;

    if (!move_uploaded_file ($_FILES['file']['tmp_name'], $filepath))
    {
      return debug ('Failed to save');
    }

    $data = array ('filename' => $filename);
    $this->fileGetContents ($filename,$filepath);
    //header ('Content-type: text/html');
    //echo json_encode ($data);
  }

  function fileGetContents ($filename, $filepath)
  {
    $fp = fopen ($filepath, 'r');

    if (!$fp)
    {
      return debug ("Couldn't open the $filepath");
    }

    if (!flock ($fp, LOCK_SH))
    {
      return debug ("Couldn't lock the $filepath");
    }

    $count = 0;
    while (!feof ($fp))
    {
      try
      {
        $count++;
        $buf = fgets ($fp);
        //debug ($buf);

        $buffer = split (",", $buf);

        /*
        if ($count == 1 ||
            (!isset ($buffer[0]) || !$buffer[0]) ||
            $this->checkIgnoreData ($buffer))
        {
          continue;
        }
          */

        //debug ($buffer);
        $this->insertData ($filename, $buffer);
      }
      catch (Exception $e)
      {
        debug ($e);
      }
    }

    flock ($fp, LOCK_UN);
    fclose ($fp);

    print ("$count datas were inserted.");
  }

  function checkIgnoreData ($buffer)
  {
    // impressions
    if (intval ($buffer[4]) < 1000 ) { return 1; }

    // clicks
    if (intval ($buffer[5]) < 0 ) { return 1; }

    return 0;
  }

  function insertData ($filename, $buf)
  {
    $this->CreativeDashboardForDena->create ();

    $field = array (
      'org_filename' => trim (mb_convert_kana ($filename, "s")),
      'campaign_name' => trim (mb_convert_kana ($buf[0], "s")),
      'creative' => trim (mb_convert_kana ($buf[1], "s")),
      'impression' => $buf[2],
      'click' => $buf[3],
      'ctr' => str_replace ("%", "", $buf[4]),
      'install' => $buf[5],
      'cvr' => str_replace ("%", "", $buf[6]),
      'relevancy' => $buf[7],
      'target_date' => $buf[8],
      );

    debug($field);

    $already_data = $this->isExistsData ($filename, $buf);
    if ($already_data)
    {
      $field['id'] = $already_data['CreativeDashboardForDena']['id'];
    }

    $this->CreativeDashboardForDena->set ($field);
    $this->CreativeDashboardForDena->save ();
    echo $this->sqlDump ();
  }

  function isExistsData ($filename, $buf)
  {
    $datas = $this->CreativeDashboardForDena->find ('all', array (
      'conditions' => array ('CreativeDashboardForDena.org_filename' => $filename,
                             'CreativeDashboardForDena.target_date' => $buf[8],
                             'CreativeDashboardForDena.campaign_name' => $buf[0],
                             'CreativeDashboardForDena.creative' => $buf[1])));
    return count ($datas) >= 1 ? $datas[0] : 0;
  }

  function isExistsField ($filed_data)
  {
    if (is_null ($filed_data))
      return 0;

    if (!isset ($filed_data))
      return 0;

    if (empty ($filed_data))
      return 0;

    return 1;
  }

  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ();
  }

  public function processProgress ()
  {
    $this->autoLayout = false;
    $this->autoRender = false;

    debug ("progress");

    $key = ini_get("session.upload_progress.prefix") . "example";
    var_dump ($_SESSION[$key]);
  }
}
