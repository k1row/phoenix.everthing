
<?php

/*

メソッド名を指定しない場合自動出来に、main()メソッドが呼び出される
php /usr/local/nginx/cakeAdmin/app/Console/cake.php Email /usr/local/nginx/cakeAdmin/app
*/


App::uses('Shell', 'Console');
//App::uses('CakeEmail', 'Network/Email');

App::import('Vendor', 'aws/sdk.class.php');
App::import('Vendor', 'aws/services/ses.class.php');


class EmailShell extends Shell
{
  //var $uses = array('AmazonSES');
  var $ses;

  // オーバーライドして、Welcome to CakePHP･･･のメッセージを出さないようにする。
  function startup ()
  {
    if ($this->AWSSES->_aws_ses('email')) {
    }
  }

  public function main ()
  {
    /*
      $ses = new AmazonSES();
      $res = $ses->send_email('k.nagashima@cyberagentamerica.com',
                              array('ToAddresses' => array('k.nagashima@cyberagentamerica.com')),
                              array(
                                'Subject' => array('Data' => "これはAWS-SES送信テストです",
                                                   'Charset' => 'ISO-2022-JP'),
                                'Body' => array('Text' => array(
                                  'Data' => '関係の無い方が受信された場合は破棄してください',
                                  'Charset' => 'ISO-2022-JP'))
                                ),
                              array('ReturnPath' => 'k.nagashima@cyberagentamerica.com'));

      echo $res->isOK() ? "OK": "NG";
     */
  }
}
