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
	'dropbox_path' => '/blushdrop',
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