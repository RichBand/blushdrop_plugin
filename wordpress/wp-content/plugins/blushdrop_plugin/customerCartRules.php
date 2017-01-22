<?
$onePerCart = $settings['cartRules']['onePerCart']; //=>['prodID_EditingPacakage','prodID_URL'],
$noModifyQuantity = $settings['cartRules']['noModifyQuantity']; //=>['minutes'],
?>
<div id="bdp_background" class="bdp_background">
    <div class="bdp-spinner mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active"></div>
</div>
<div
    id="bdp_cr"
    data-opc="<?= json_encode($onePerCart)?>"
    data-nmq="<?= json_encode($noModifyQuantity)?>"
></div>