
<?php

/*

���\�b�h�����w�肵�Ȃ��ꍇ�����o���ɁAmain()���\�b�h���Ăяo�����
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

  // �I�[�o�[���C�h���āAWelcome to CakePHP����̃��b�Z�[�W���o���Ȃ��悤�ɂ���B
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
                                'Subject' => array('Data' => "�����AWS-SES���M�e�X�g�ł�",
                                                   'Charset' => 'ISO-2022-JP'),
                                'Body' => array('Text' => array(
                                  'Data' => '�֌W�̖���������M���ꂽ�ꍇ�͔j�����Ă�������',
                                  'Charset' => 'ISO-2022-JP'))
                                ),
                              array('ReturnPath' => 'k.nagashima@cyberagentamerica.com'));

      echo $res->isOK() ? "OK": "NG";
     */
  }
}
