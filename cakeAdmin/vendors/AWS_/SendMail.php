<?php

// include SDK
require_once('AWSSDKforPHP/sdk.class.php');
require_once('AWSSDKforPHP/services/ses.class.php');


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
