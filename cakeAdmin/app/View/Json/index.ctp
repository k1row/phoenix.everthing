<?php
/// paginatorの情報
$params = $paginator->params();
$data = array(// レコードの全件数
              'total' => $params['count'],
              // 取得したレコード
              'raws'  => $books,);
// CakePHP 1.3.x
echo $this->Js->object($data);
// CakePHP 1.2.x
// echo $this->Javascript->object($data);

// ストアのコンフィグオプションを指定した場合
$data=array('metaData'=>array(
                              //取得したレコードのプロパティ名
                              'rootProperty'=>'records',
                              //レコードの全件数のプロパティ名
                              'totalProperty'=>'count',
                              //レコードのフィールド
                              'fields'=>array('Book.id','Book.name','Author.name'),
                              ),
            //取得したレコード
            'records'=>$books,
            //レコードの全件数
            'count' =>$params['count'],
            );
