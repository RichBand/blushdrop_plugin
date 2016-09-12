<?php
/**
 * Created by PhpStorm.
 * User: ricardobandala
 *
 * Date: 2016-08-25
 * Time: 22:38
 */
 ?>
 <div id="bdpMain">
	 <div id="bdpLefth">
		 <div id="prod_EditingPacakage" class="bdp_LeftOpt">
		 		<p>Editing Package: (ten minutes of raw material included) </p>
		 </div>
		 <div id="prod_Music" class="bdp_LeftOpt">
		 	<p>Select a song: </p>
		 	<select id="eleSelMusic"></select>
		 </div>
		 <div id="prod_Disc" class="bdp_LeftOpt">
		 	<p>Number of DVD's : </p>
		 	<p onclick = "view.setProductDVD(1)"; >+</p>
		 	<input id="eleInputDiscAmount" type="number" min="0" max="99" onblur = "view.setProductDVD(this.value);">
		 	<p onclick = "view.setProductDVD(-1)";>-</p>
		 </div>
		 <div id="prod_Raw" class="bdp_LeftOpt">
		 	<p>Include raw Footage? </p>
		 	<input id="eleCheckboxRaw" type="checkbox">
		 </div>
		 <div id="prod_Minute" class="bdp_LeftOpt">
		 	<p>No extra minutes yet </p>
		 	<input id="eleExtraMinutes" disabled="">
		 </div>
	 </div>
	 <div id="bdpRight">
	 	<div id="info_Subtotal" class="bdp_RightOpt">
	 		Total: $
		</div>
		<div id="info_Submit" class="bdp_RightOpt">
		 <button id="bdpSubmitOrder" class="" onclick="submitOrder()">Submit Order</button>
	 </div>
		 <button id="bdpSubmitOrder" class="" onclick="ajax_getMinutes()">Get minutes</button>
	 </div>
 </div>
<?php
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
			main: <?php echo $wcm->getProduct($settings['prodID_EditingPacakage'], $user)?>,
			minute: <?php echo $wcm->getProduct($settings['prodID_ExtraMinute'],  $user)?>,
			raw: <?php echo $wcm->getProduct($settings['prodID_RawMaterial'], $user)?>,
			url: <?php echo $wcm->getProduct($settings['prodID_URL'], $user)?>,
			disc: <?php echo $wcm->getProduct($settings['prodID_Disc'], $user)?>,
		};
		var x = <?php echo $wcm->getMusic($settings['prodCat_Music'], $user)?>;
		for (var i = 0, len = x.length; i < len; ++i) {
			x[i] = JSON.parse(x[i]);
		}
		model.products.music = x;
		model.url = "<?php echo get_site_url(); ?>";
		console.log(model);
	</script>
	<?php
}
