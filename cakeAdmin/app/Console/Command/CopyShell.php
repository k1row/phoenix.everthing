
<?php

/*
���ۂ�Terminal������s����ꍇ�́A
/usr/bin/php /CakePHP�̃p�X/app/Console/cake.php Hoge -app /CakePHP�̃p�X/app/

1) /usr/bin/php                        ��� php�܂ł̃p�X
2) /CakePHP�̃p�X/app/Console/cake.php ��� cake.php�܂ł̃p�X(�Œ�j
3) Hoge                                ��� Shell.php���������V�F����
4) -app                                ��� app�R�}���h
5) /CakePHP�̃p�X/app/                 ��� app�܂ł̃p�X(�Œ�j

���\�b�h�����w�肵�Ȃ��ꍇ�����o���ɁAmain()���\�b�h���Ăяo�����
php /usr/local/nginx/cakeAdmin/app/Console/cake.php Copy /usr/local/nginx/cakeAdmin/app

�V�F�����̌�ɔC�ӂ̃��\�b�h���w��ł���
php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeCampaign test /usr/local/nginx/cakeAdmin/app

php /usr/local/nginx/cakeAdmin/app/Console/cake.php AnalyzeCampaign specifiedDate 2013-03-10 -app /usr/local/nginx/cakeAdmin/app
�p�����[�^��n�����Ƃ��\
*/


App::uses('Shell', 'Console');

class CopyShell extends Shell
{
  var $uses = array('Click', 'Conversion');
  var $target_date;

  // �I�[�o�[���C�h���āAWelcome to CakePHP����̃��b�Z�[�W���o���Ȃ��悤�ɂ���B
  function startup ()
  {
    $this->log (Configure::version(), LOG_DEBUG);

    Configure::write ('debug', 2);
    //$debug = Configure::read ('debug'); // �ݒ�t�@�C����ǂނ��Ƃ��ł���
    $this->target_date = date ("Y-m-d", strtotime ("-1 day"));
    $this->User = ClassRegistry::init ('User');
  }

  public function main ()
  {
	//$this->getClicks ();
	$this->getConversions ();
  }

  function getClicks ()
  {
    $datas = $this->Click->find ('all');
    foreach ($datas as $data)
    {
	  $this->updateClick ($data);
    }
  }

  function updateClick ($data)
  {
    $this->Click->create ();

    $field = array ('id' => $data['Click']['id'],
                    'dpidraw' => $data['Click']['dpidraw'],
                    );
    $this->Click->set ($field);
    $this->Click->save ();
  }

  function getConversions ()
  {
    $datas = $this->Conversion->find ('all');
    foreach ($datas as $data)
    {
	  $this->updateConversion ($data);
    }
  }

  function updateConversion ($data)
  {
	$this->Conversion->create ();
	
	$field = array ('id' => $data['Conversion']['id'],
					'dpidraw' => $data['Conversion']['dpidraw'],
					);
	$this->Conversion->set ($field);
	$this->Conversion->save ();
  }

  function sqlDump ($dbConfig = 'default')
  {
    ConnectionManager::getDataSource ($dbConfig)->showLog ()."\n";
  }
}
