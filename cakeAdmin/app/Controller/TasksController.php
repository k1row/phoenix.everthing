<?php

App::uses('Controller', 'Controller');

class TasksController extends AppController
{
  var $name = 'Tasks';
  var $uses = array('Tasks');
  
  // ajax呼び出しに必要
  var $components = array('RequestHandler');
  // とりあえずタスクの一覧表示
  function index() {
    $this->loadModel('Task');
    $this->set('tasks', $this->Task->find('all'));
  }
  // ajaxで呼び出される関数
  function ajax_add() {
    // デバッグ情報出力を抑制
    Configure::write('debug', 0);
    // ajax用のレイアウトを使用
    $this->layout = "ajax";
    // ajaxによる呼び出し？
    if($this->RequestHandler->isAjax()) {
      // POST情報は$this->params['form']で取得
      $title = $this->params['form']['title'];
      // DBに突っ込みます
      $this->Task->id = null;
      $this->data['Task']['title'] = $title;
      $this->Task->save($this->data);
      // 表示用のデータをviewに渡す
      $this->set('t', $title);
    }
  }
}
