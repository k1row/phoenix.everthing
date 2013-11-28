<?php

App::import('Vendor', 'aws/sdk.class.php');
App::import('Vendor', 'aws/services/ses.class.php');


class AWSSESComponent extends Object
{
  public $components = array('AWSSES');

  public $ses;
  public $emailViewPath = '/Emails';
  public $emailLayouts = 'Emails';
  public $htmlMessage;
  public $from = 'from_email_address';
  public $to;

  public function initialize($controller)
  {
  }

  function startup(&$controller)
  {
    $this->controller =& $controller;

    $this->ses = new AmazonSES(array('certificate_authority' => false,
                                     'key' => 'AWS_Key',
                                     'secret' => 'AWS_secrete'));

  }

  public function beforeRender()
  {
  }

  public function shutdown()
  {
  }

  public function beforeRedirect()
  {
  }

  public function _aws_ses($viewTemplate, $mailContent = null)
  {
    if(!empty($this->controller->request->data) && $mailContent == null){
      $mailContent = $this->controller->request->data[$this->controller->modelClass];
    }

    $mail = $this->email_templates($viewTemplate);

    $destination = array(
      'ToAddresses' => explode(',', $this->to)
      );
    $message = array(
      'Subject' => array(
        'Data' => $mail['Subject']
        ),
      'Body' => array()
      );


    $this->controller->set('data', $mailContent);

    $this->htmlMessage = $this->_getHTMLBodyFromEmailViews($mail['ctp']);

    if ($this->htmlMessage != NULL) {
      $message['Body']['Html'] = array(
        'Data' => $this->htmlMessage
        );
    }

    $response = $this->ses->send_email($this->from, $destination, $message);

    $ok = $response->isOK();

    if (!$ok) {
      $this->log('Error sending email from AWS SES: ' . $response->body->asXML(), 'debug');
    }
    return $ok;
  }

  public function email_templates($name)
  {
    $this->templates = array('email_name' => array('ctp' => 'ctp_file_name', 'Subject' => 'email_subject'),
                             'email_name' => array('ctp' => 'reset_passwordctp_file_name', 'Subject' => 'email_subject'));

    return $this->templates[$name];
  }

  public function _getHTMLBodyFromEmailViews($view)
  {
    $currentLayout = $this->controller->layout;
    $currentAction = $this->controller->action;
    $currentView = $this->controller->view;
    $currentOutput = $this->controller->output;

    ob_start();
    $this->controller->output = null;

    $viewPath = $this->emailViewPath . DS . 'html' . DS . $view;
    $layoutPath = $this->emailLayouts . DS . 'html' . DS . 'default';

    $bodyHtml = $this->controller->render($viewPath, $layoutPath);

    ob_end_clean();

    $this->controller->layout = $currentLayout;
    $this->controller->action = $currentAction;
    $this->controller->view = $currentView;
    $this->controller->output = $currentOutput;

    return $bodyHtml;
  }
}
