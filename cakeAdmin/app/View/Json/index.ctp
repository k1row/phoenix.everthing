<?php
/// paginator�̏��
$params = $paginator->params();
$data = array(// ���R�[�h�̑S����
              'total' => $params['count'],
              // �擾�������R�[�h
              'raws'  => $books,);
// CakePHP 1.3.x
echo $this->Js->object($data);
// CakePHP 1.2.x
// echo $this->Javascript->object($data);

// �X�g�A�̃R���t�B�O�I�v�V�������w�肵���ꍇ
$data=array('metaData'=>array(
                              //�擾�������R�[�h�̃v���p�e�B��
                              'rootProperty'=>'records',
                              //���R�[�h�̑S�����̃v���p�e�B��
                              'totalProperty'=>'count',
                              //���R�[�h�̃t�B�[���h
                              'fields'=>array('Book.id','Book.name','Author.name'),
                              ),
            //�擾�������R�[�h
            'records'=>$books,
            //���R�[�h�̑S����
            'count' =>$params['count'],
            );
