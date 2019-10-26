<?php
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Path to the list definition file
$list_def_file = "list/sites.list.php";

//* Check permissions for module
$app->auth->check_module_permissions('appdetect');

//* Loading the class
$app->load('listform_actions');

$query_type = 'vhost';
$show_type = 'domain';
if(isset($_GET['type']) && $_GET['type'] == 'subdomain') {
	$show_type = 'subdomain';
	$query_type = 'vhostsubdomain';
} elseif(isset($_GET['type']) && $_GET['type'] == 'aliasdomain') {
	$show_type = 'aliasdomain';
	$query_type = 'vhostalias';
} elseif(!isset($_GET['type']) && isset($_SESSION['s']['var']['vhostdomain_type']) && $_SESSION['s']['var']['vhostdomain_type'] == 'subdomain') {
	$show_type = 'subdomain';
	$query_type = 'vhostsubdomain';
} elseif(!isset($_GET['type']) && isset($_SESSION['s']['var']['vhostdomain_type']) && $_SESSION['s']['var']['vhostdomain_type'] == 'aliasdomain') {
	$show_type = 'aliasdomain';
	$query_type = 'vhostalias';
}

$_SESSION['s']['var']['vhostdomain_type'] = $show_type;

class list_action extends listform_actions {

    // Defining names and sources.
    private $available_joomla_version;
    private $available_wordpress_version;
    private $available_gsales_version;
    private $available_shopware_version;
    private $available_gambio_version;
    
    function __construct()
    {
        // Get the latest Joomla version.
        $this->available_joomla_version = json_decode(file_get_contents('https://downloads.joomla.org/api/v1/latest/cms'))->branches[3]->version;
        // Get the latest Wordpress version.
        $this->available_wordpress_version = json_decode(file_get_contents('https://api.wordpress.org/core/version-check/1.7'))->offers[0]->version;
        
        // Get the latest Gsales Version.
        $client = new SoapClient('http://www.gsales.de/licence/currentversion.wsdl');
        $this->available_gsales_version = $client->getVersion();
       
        // Get the latest Shopware version.
        // Special UserAgent Header.
        // Details see https://stackoverflow.com/a/37142247
        $opts = [
                'http' => [
                        'method' => 'GET',
                        'header' => [
                                'User-Agent: PHP'
                        ]
                ]
        ];
        $context = stream_context_create($opts);
        $this->available_shopware_version = json_decode(file_get_contents('https://api.github.com/repos/shopware/shopware/releases/latest', false, $context))->tag_name;
        
        // TODO: Get the latest Gambio Version.
    }
        

    function prepareDataRow($rec)
	{
		global $app;

		// Detect Joomla sites.
		// Also detect in subfolder CMS & MCS.
        if (file_exists($rec['document_root'] . '/web/configuration.php') && file_exists($rec['document_root'] . '/web/libraries/src/Version.php') OR
            file_exists($rec['document_root'] . '/web/cms/configuration.php') && file_exists($rec['document_root'] . '/web/cms/libraries/src/Version.php') OR
            file_exists($rec['document_root'] . '/web/mcs/configuration.php') && file_exists($rec['document_root'] . '/web/mcs/libraries/src/Version.php'))
        {
            $version = file($rec['document_root'] . '/web/libraries/src/Version.php');
            if (!$version)
            {
                $version = file($rec['document_root'] . '/web/cms/libraries/src/Version.php');
            }
            if (!$version)
            {
                $version = file($rec['document_root'] . '/web/mcs/libraries/src/Version.php');
            }
            $detected_version = preg_replace('![^0-9]!', '', $version[35]) . '.' . preg_replace('![^0-9]!', '', $version[43]) . '.' . preg_replace('![^0-9]!', '', $version[51]);

            $rec['detected_app'] = 'Joomla!';
            $rec['detected_version'] = $detected_version;
            $rec['available_version'] = $this->available_joomla_version;
            
            if ($detected_version != $rec['available_version'])
            {
                $rec['red_style'] = 'color: #f00;';
            }
        }
        
        // Detect Wordpress sites.
        if (file_exists($rec['document_root'] . '/web/wp-config.php') && file_exists($rec['document_root'] . '/web/wp-includes/version.php'))
        {
            $version = file($rec['document_root'] . '/web/wp-includes/version.php');
            $detected_version = preg_replace('![^0-9.]!', '', $version[6]);

            if (!$detected_version)
            {
                $detected_version = preg_replace('![^0-9.]!', '', $version[15]);
            }

            $rec['detected_app'] = 'Wordpress';
            $rec['detected_version'] = $detected_version;
            $rec['available_version'] = $this->available_wordpress_version;
            
            if ($detected_version != $rec['available_version'])
            {
                $rec['red_style'] = 'color: #f00;';
            }
        }
        
        // Detect gSales sites.
        if (file_exists($rec['document_root'] . '/web/article/') && file_exists($rec['document_root'] . '/web/customer/') && file_exists($rec['document_root'] . '/web/invoice/'))
        {
            $version = file($rec['document_root'] . '/web/install/class.installer.php');
            $detected_version = preg_replace('![^0-9]!', '', $version[2]);

            $rec['detected_app'] = 'gSales2';
            $rec['detected_version'] = $detected_version;
            $rec['available_version'] = $this->available_gsales_version;

            if ($detected_version != $rec['available_version'])
            {
                $rec['red_style'] = 'color: #f00;';
            }
        }
        
        // Detect Shopware sites.
        if (file_exists($rec['document_root'] . '/web/_swfk/shopware.php'))
        {
            $version = file($rec['document_root'] . '/web/_swfk/engine/Shopware/Application.php');
            $detected_version = preg_replace('![^0-9.]!', '', $version[44]);

            $rec['detected_app'] = 'Shopware';
            $rec['detected_version'] = $detected_version;
            $rec['available_version'] = preg_replace('![^0-9.]!', '', $this->available_shopware_version);

            if ($detected_version != $rec['available_version'])
            {
                $rec['red_style'] = 'color: #f00;';
            }
        }
        
        // Detect Gambio sites.
        if (file_exists($rec['document_root'] . '/web/gambio_updater'))
        {
            $version = file($rec['document_root'] . '/web/release_info.php');
            $detected_version = preg_replace('![^0-9.]!', '', $version[5]);

            $rec['detected_app'] = 'Gambio';
            $rec['detected_version'] = $detected_version;
            $rec['available_version'] = preg_replace('![^0-9.]!', '', $this->available_gambio_version);
            
            if ($detected_version != $rec['available_version'])
            {
                $rec['red_style'] = 'color: #f00;';
            }
        }
        
		return $rec;
	}


	function onShow() {
		global $app;
		$app->tpl->setVar('vhostdomain_type', $_SESSION['s']['var']['vhostdomain_type'], true);
		
		parent::onShow();
	}

}

$list = new list_action;
$list->SQLExtWhere = "web_domain.type = '" . $query_type . "'" . ($show_type == 'domain' ? " AND web_domain.parent_domain_id = '0'" : "");
$list->SQLOrderBy = 'ORDER BY web_domain.domain';
$list->onLoad();



?>
