<?php

App::uses('Controller', 'Controller');

class TasksController extends AppController
{
  var $name = 'Tasks';
  var $uses = array('Tasks');
  
  // ajax�Ăяo���ɕK�v
  var $components = array('RequestHandler');
  // �Ƃ肠�����^�X�N�̈ꗗ�\��
  function index() {
    $this->loadModel('Task');
    $this->set('tasks', $this->Task->find('all'));
  }
  // ajax�ŌĂяo�����֐�
  function ajax_add() {
    // �f�o�b�O���o�͂�}��
    Configure::write('debug', 0);
    // ajax�p�̃��C�A�E�g���g�p
    $this->layout = "ajax";
    // ajax�ɂ��Ăяo���H
    if($this->RequestHandler->isAjax()) {
      // POST����$this->params['form']�Ŏ擾
      $title = $this->params['form']['title'];
      // DB�ɓ˂����݂܂�
      $this->Task->id = null;
      $this->data['Task']['title'] = $title;
      $this->Task->save($this->data);
      // �\���p�̃f�[�^��view�ɓn��
      $this->set('t', $title);
    }
  }
}
