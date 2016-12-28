<?php
/**
 * Plugin Name: Blushdrop Plugin
 * Description: handle all the custom needs of blushdropCart.
 * Version: 1.1
 * Author: Ricardo Bandala
 * Author URI: http://ricardobandala.com
 * License: private
 */
require_once 'blushdrop.php';
$args = array(
	'dropbox' => [
	    'path' => '/blushdrop/',
        'appInfo' => ['key' => 'kulo8xx7kugwfzo', 'secret' => '5gqns9krb5b2v7i'],
        'token' =>'xZ1AXx94nAoAAAAAAAH2vYuaGl5d9RNlwAEJ3XacJ6JRqDfxAIZhe0ift20P7f9M'
    ],
	'prodCat_Music' => 'music',
	'prodID_Disc' => '31',
	'prodID_EditingPacakage' => '32',
	'prodID_ExtraMinute' => '79',
	'prodID_RawMaterial' => '67',
	'prodID_URL' => '34',
	'cartRules' =>[
		'atLeastOnePerCart'=>['prodID_EditingPacakage','prodID_URL'],
	]
);
$bdp = new Blushdrop($args);