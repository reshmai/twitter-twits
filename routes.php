<?php
  function call($controller, $action) {
    // require the file that matches the controller name
    require_once('controllers/' . $controller . '_controller.php');

    // create a new instance of the needed controller
    switch($controller) {
      case 'pages':
        $controller = new PagesController();
      break;
      case 'apis':
        // we need the model to query the database later in the controller
        require_once('models/api.php');
        require_once('utilities/RequestParam.php');
        $controller = new apisController();
      break;
    }

    // call the action
    $controller->{ $action }();
  }

  // just a list of the controllers we have and their actions
  // we consider those "allowed" values
  $controllers = array('pages' => array('home', 'error'),

    'apis' => array('index', 'update_user',
     'get_skill_list','get_user_profile','upload_resume',
     'check_user_exist', 'get_job_list', 'get_designation_list', 'get_user_list'));

  // check that the requested controller and action are both allowed
  // if someone tries to access something else he will be redirected to the error action of the pages controller
  if (array_key_exists($controller, $controllers)) {
    //print_r(in_array($action, $controllers[$controller]));die; 
    if (in_array($action, $controllers[$controller])) {
      call($controller, $action);
    } else {
      call('pages', 'error');
    }
  } else {
    call('pages', 'error');
  }
?>