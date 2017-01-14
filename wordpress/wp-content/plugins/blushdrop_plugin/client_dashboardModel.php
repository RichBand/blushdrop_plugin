<?php
/**
 * Created by PhpStorm.
 * User: ricardobandala
 *
 * Date: 2016-08-25
 * Time: 22:38
 */

function loadData($bdp, $userID)
{
	$settings = $bdp->getSettings();
	$wcm = $bdp->getBdpWcm();
	$dpx = $bdp->getBdpDpx();
	$user = get_user_by('id', $userID);
	$path = $bdp->getPath().$user->user_login;
	$author =  0 + ( get_the_author_meta('ID') );
    $ajaxurl = admin_url('admin-ajax.php');
    $currentTotalMinutes = 0 + $dpx->getVideoMinutes($path);
    $disc = json_encode($wcm->getProduct($settings['prodID_Disc'], $user));
    $main = json_encode($wcm->getProduct($settings['prodID_EditingPacakage'], $user));
    $minute = json_encode($wcm->getProduct($settings['prodID_ExtraMinute'],  $user));
    $raw = json_encode($wcm->getProduct($settings['prodID_RawMaterial'], $user));
    $url = json_encode($wcm->getProduct($settings['prodID_URL'], $user));
    $siteUrl = get_site_url();
	?>

	$response =
    '<script type="text/javascript">'.
		'model.ajaxurl ='.$ajaxurl.';'.
        'model.customer ='.$author.';'.
		'model.currentTotalMinutes =' 0 + $currentTotalMinutes.';'.
		'model.products = {'.
		'disc:'.$disc.';'.
		'main:'.$main.';'.
		minute: <?= json_encode($wcm->getProduct($settings['prodID_ExtraMinute'],  $user))?>,
		music: (function () {
			<? $soundtrack = $wcm->getMusic($settings['prodCat_Music'], $user);
			for($i = 0, $j = count($soundtrack); $i<$j; $i++){
				$soundtrack[$i] = json_encode($soundtrack[$i]);
			}
			?>
			var soundtrack = <?= json_encode($soundtrack) ?>;
			for (var i = 0, len = soundtrack.length; i < len; ++i) {
				soundtrack[i] = JSON.parse(soundtrack[i]);
			}
			return soundtrack;
		}()),
		raw: <?= json_encode($wcm->getProduct($settings['prodID_RawMaterial'], $user));?>,
		url: <?= json_encode($wcm->getProduct($settings['prodID_URL'], $user));?>
		};
		model.url = "<?= get_site_url(); ?>";
		console.log(model);
	</script>
	<?php
}
