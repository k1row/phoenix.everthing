<?php

App::uses('Controller', 'Controller');
App::uses('FileUploadAppController', 'Controller');

class FileUploadController extends FileUploadAppController
{
  function beforeFilter()
  {
    parent::beforeFilter();
    $this->Auth->allow("*");
  }

  public function index()
  {
  }
}
?>
