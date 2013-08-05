<?php
error_reporting(1);
ini_set('display_errors',true);

define('APP_PATH',realpath(dirname(__FILE__)));

date_default_timezone_set('Europe/Moscow');
set_include_path(implode(PATH_SEPARATOR,array(get_include_path(),APP_PATH.'/models')));
set_include_path(implode(PATH_SEPARATOR,array(
	realpath('../library'),
	APP_PATH . '/models',
	get_include_path()
)));
// autoloader init
require '../library/Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

Zend_Registry::set('app_path',APP_PATH);

// loading the config
$config=new Zend_Config_Ini(APP_PATH.'/configs/main.ini','main');
$content=new Zend_Config_Ini(APP_PATH.'/configs/content.ini','main');
Zend_Registry::set('config',$config);
Zend_Registry::set('content',$content);

// parse subdomain
$host = strtolower($_SERVER['HTTP_HOST']);
if ('spb.' === substr($host, 0, 4)) {
	Zend_Registry::set('city', 2);
} else {
	Zend_Registry::set('city', 1);
}

// database init
$db=Zend_Db::factory($config->db->adapter,$config->db->config->toArray());
Zend_Db_Table_Abstract::setDefaultAdapter($db);
Zend_Registry::set('db',$db);

// front controller inits
$front=Zend_Controller_Front::getInstance();
$front->setControllerDirectory(APP_PATH.'/controllers');
$layout=Zend_Layout::startMvc();
$layout->setLayoutPath(APP_PATH.'/layouts');

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Ps');
$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
	'basePath'	    => APP_PATH,
	'namespace'     => '',
	'resourceTypes' => array(	
		'form' => array(
			'path' => 'forms/',
			'namespace' => 'Form_'
		),
		'model' => array(
			'path'  => 'models/',
			'namespace' => 'Model_'		
		)
	),
));

$router = $front->getRouter();
/*
$routeSections = new Zend_Controller_Router_Route_Regex(
        '(\d+)\-(.+)', 
        array('controller' => 'index', 'action' => 'section'),
        array('id' => 1, 'uri' => 2),
        '%s');// %s is fix, Cannot assemble. Reversed route is not specified, Exception
*/

$arrays = array("public", "devushki", "metro", "/", "main", "_", 'uslugi','kategorii');
$id = str_replace($arrays, "", $_SERVER['REQUEST_URI']);

include('models/Arrays.php');

$router->addRoute( 'ankets', new Zend_Controller_Router_Route('anketa/:name', array('module' => 'default', 'controller' => 'anketa', 'action' => 'index') ) );
$router->addRoute('sections', new Zend_Controller_Router_Route('kategorii/:name', array('module' => 'default', 'controller' => 'index', 'action' => 'section', 'id' => $kategorii[$id])));
$router->addRoute( 'salon', new Zend_Controller_Router_Route('salon/:name', array('module' => 'default', 'controller' => 'salon', 'action' => 'index') ) );

include('models/MenuItems.php');
$mMenuItems = new MenuItems();

foreach ( $items = $mMenuItems->get_all_items() as $item ) {
	$router->addRoute($item['uri'],
		new Zend_Controller_Router_Route($item['uri'],
				array('module' => 'default', 'controller' => 'index', 'action' => 'menuItems', 'uri' => $item['uri'] )));
}

$front->dispatch();