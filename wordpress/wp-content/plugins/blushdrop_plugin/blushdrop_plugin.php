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
	    'path' => '/blushdrop',
        'appInfo' => ['key' => 'jknvogyzj4jwpru', 'secret' => 'do6nvyzatxynkef'],
        'token' =>'aJhr5imPEkAAAAAAAAAA6ZHoBxaPNjU8nJF_fc611dRLa_EycP18skNkPmPkR4dc'
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