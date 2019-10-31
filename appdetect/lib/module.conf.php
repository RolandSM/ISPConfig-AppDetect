<?php

//* Name of the module. The module name must match the name of the module directory. The module name may not contain spaces.
$module['name']      = 'appdetect';

//* Title of the module. The title is dispalayed in the top navigation.
$module['title']     = 'AppDetect';

//* The template file of the module. This is always module.tpl.htm if you do not have any special requirements like a 3 column layout.
$module['template']  = 'module.tpl.htm';

//* The page that is displayed when the module is loaded. the path must is relative to the web directory
$module['startpage'] = 'appdetect/sites_list.php';

//* The width of the tab. Normally you should leave this empty and let the browser define the width automatically.
$module['tab_width'] = '';

$module['order']    = '30';

//*** Menu Definition *****************************************

//* make sure that the items array is empty
$items = array();

//* Add a menu item with the label 'View messages'
$items[] = array( 'title'   => 'View sites',
	'target'  => 'content',
	'link'    => 'appdetect/sites_list.php',
	'html_id' => 'appdetect_sites_list');


//* Add the menu items defined above to a menu section labeled 'Support'
$module['nav'][] = array( 'title' => 'Sites',
	'open'  => 1,
	'items' => $items);

if($_SESSION['s']['user']['typ'] == 'admin') {
	//* make sure that the items array is empty
	$items = array();

	//* Add a menu item with the label 'Version'
	$items[] = array( 'title'   => 'Version',
		'target'  => 'content',
		'link'    => 'appdetect/version.php',
		'html_id' => 'appdetect_version' );


	//* Add the menu items defined above to a menu section labeled 'Support'
	$module['nav'][] = array( 'title' => 'About AppDetect',
		'open'  => 1,
		'items' => $items);

}

?>
