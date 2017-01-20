<?
$onePerCart = $settings['cartRules']['onePerCart']; //=>['prodID_EditingPacakage','prodID_URL'],
$noModifyQuantity = $settings['cartRules']['noModifyQuantity']; //=>['minutes'],
?>
<div
    id="bdp_cr"
    data-opc="<?= json_encode($onePerCart)?>"
    data-nmq="<?= json_encode($noModifyQuantity)?>"
></div>