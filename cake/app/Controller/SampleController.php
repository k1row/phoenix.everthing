<?php

App::uses('Controller', 'Controller');

class SampleController extends Controller {
  public function index()
  {
    $this->autoLayout = false;
    $this->autoRender = false;

    for( $sum = $i = 0; $i < 1000000; $i ++ )
    {
      $sum += $i;
    }

  ob_start();
  phpinfo();
  $html = ob_get_contents();
  ob_end_clean();
  echo $html;
  }
}
