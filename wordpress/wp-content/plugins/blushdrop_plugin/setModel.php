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
	$author =  get_the_author_meta('ID');
	?>

	<script type="text/javascript">
		model.ajaxurl ='<?php echo admin_url('admin-ajax.php'); ?>';
		model.customer = 0<?php echo $author ?>;
		model.currentTotalMinutes = 0<?php echo $dpx->getVideoMinutes($path);?>;
		model.products = {
		disc: <?php echo json_encode($wcm->getProduct($settings['prodID_Disc'], $user))?>,
		main: <?php echo json_encode($wcm->getProduct($settings['prodID_EditingPacakage'], $user))?>,
		minute: <?php echo json_encode($wcm->getProduct($settings['prodID_ExtraMinute'],  $user))?>,
		music: (function () {
			<?php $soundtrack = $wcm->getMusic($settings['prodCat_Music'], $user);
			for($i = 0, $j = count($soundtrack); $i<$j; $i++){
				$soundtrack[$i] = json_encode($soundtrack[$i]);
			}
			?>
			var x = <?php echo json_encode($soundtrack) ?>;
			for (var i = 0, len = x.length; i < len; ++i) {
				x[i] = JSON.parse(x[i]);
			}
			return x;
		}()),
		raw: <?php echo json_encode($wcm->getProduct($settings['prodID_RawMaterial'], $user));?>,
		url: <?php echo json_encode($wcm->getProduct($settings['prodID_URL'], $user));?>
		};
		model.url = "<?php echo get_site_url(); ?>";
		console.log(model);
	</script>
	<?php
}
