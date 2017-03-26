<?
$currentTotalMinutes = empty($currentTotalMinutes)? 0 : intval($currentTotalMinutes);
$user = empty($user)? 0 : intval($user->ID);
$products = empty($products)? json_encode([]) : htmlspecialchars(json_encode($products), ENT_QUOTES, 'UTF-8');
$siteUrl = get_site_url();

function mdlGrid($screen = 12, $tablet = 8, $phone = 4, $allign=''){
   return "mdl-cell $allign mdl-cell--$screen-col mdl-cell--$tablet-col-tablet mdl-cell--$phone-col-phone";
}


?>
<div id="bdp_background" class="bdp_background">
    <div class="bdp-spinner mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active"></div>
</div>
<div id="bdpMain"
     data-currenttotalminutes="<?=$currentTotalMinutes?>"
     data-products="<?=$products?>"
     data-customer="<?=$user?>"
     data-ajaxurl="<?=admin_url('admin-ajax.php')?>"
     data-siteurl="<?= $siteUrl?>">

    <div class="mdl-grid">
        <div class="<?=mdlGrid(8,6,4)?>">
            <div class="mdl-grid mdl-grid--nested">
                <div class="<?= mdlGrid(8,6,4, 'mdl-cell--middle')?> text-right">
                    <span> Editing: </span> <br/>
                    <span class="text-smaller"> (includes 10 minutes of raw material)</span>
                </div>
                <div class="<?= mdlGrid(4,2,4, 'mdl-cell--middle')?>">
                    <label for="eleCheckboxEditing" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                        <input type="checkbox" id="eleCheckboxEditing" class="mdl-checkbox__input" checked disabled>
                    </label>
                </div>
                <div class="<?= mdlGrid(8,6,4, 'mdl-cell--middle') ?> text-right">
                    <span> Extra minutes of raw material:</span>
                </div>
                <div class="<?= mdlGrid(4,2,4, 'mdl-cell--middle')?>">
                    <div class="mdl-textfield mdl-js-textfield">
                        <input id="eleExtraMinutes" onchange="bdp.updateSubtotal();" class="mdl-textfield__input"
                               type="text" pattern="-?[0-9]*(\.[0-9]+)?" disabled>
                    </div>
                </div>
                <div class="<?= mdlGrid(8,6,4, 'mdl-cell--middle') ?>  text-right">
                    <span> DVD disc quantity:</span>
                </div>
                <div class="<?= mdlGrid(4,2,4, 'mdl-cell--middle')?>">
                    <div class="mdl-textfield mdl-js-textfield">
                        <input id="eleInputDiscAmount" onchange="bdp.updateSubtotal();" class="mdl-textfield__input" type="text" pattern="-?[0-9]*(\.[0-9]+)?" >
                        <span class="mdl-textfield__error">Input is not a number!</span>
                    </div>
                </div>
                <div class="<?= mdlGrid(8,6,4, 'mdl-cell--middle')?> text-right">
                    <span> Ship Raw Footage</span>
                </div>
                <div class="<?= mdlGrid(4,2,4, 'mdl-cell--middle')?>">
                    <label for="eleCheckboxRaw" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                        <input type="checkbox" id="eleCheckboxRaw" class="mdl-checkbox__input"
                               onchange="bdp.updateSubtotal();" />
                    </label>
                </div>
                <div class="<?= mdlGrid(8,6,4, 'mdl-cell--middle')?>  text-right">
                    <span> Song Selected:</span>
                </div>
                <div id="selectedSongName" class="<?= mdlGrid(4,2,4, 'mdl-cell--middle')?>">

                </div>
                <input id="eleSongCode" type="hidden" val ="" onchange="bdp.updateSubtotal();">
                <div id="audioWrapper" class="mdl-grid mdl-grid--nested">
                    <div id="interface--info" class="<?= mdlGrid()?>" >
                        <div id="info__cover"  class="backgroundCover inlineFloatLeft <?= mdlGrid(3,4,4, 'mdl-cell--middle')?>" ></div>
                        <div id="info__status" class="inlineFloatLeft <?= mdlGrid(8,4,4, 'mdl-cell--middle')?>" ></div>
                        <div id="info__title"  class="inlineFloatLeft <?= mdlGrid(8,4,4, 'mdl-cell--middle')?>" ></div>
                        <div id="info__artist" class="inlineFloatLeft <?= mdlGrid(8,4,4, 'mdl-cell--middle')?>" ></div>
                    </div>
                    <div id="interface--player" class="<?= mdlGrid()?>">
                        <div id="player">
                            <audio preload id="player__audio" controls="controls" class="<?= mdlGrid(4,4,4, 'mdl-cell--middle')?>>
                                <source src="" type="audio.mp3"/>
                                Your browser does not support HTML5 Audio!
                            </audio>
                        </div>
                        <div id="controllers">
                            <a id="player__prev">prev</a>
                            <a id="player__next">next</a>
                        </div>
                    </div>
                    <div id="interface--playlist" class="<?= mdlGrid()?>">
                        <ul id="playlist" class="<?= mdlGrid()?>"></ul>
                    </div>
                </div>
            </div>
        </div><!--end grid left block-->
        <div class="bdp-leftBlock mdl-cell mdl-cell--4-col mdl-cell--2-col-tablet mdl-cell--4-col-phone">
            <div class="mdl-grid">
                <div id="info_Subtotal" class="mdl-cell mdl-cell--12-col">
                    <div>Total: $</div>
                    <div id="info_Subtotal__amount"></div>
                </div>
                <div class="mdl-cell mdl-cell--12-col">
                    <button id="bdpSubmitOrder" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onclick="bdp.submitOrder()">Submit Order</button>
                </div>
            </div>
        </div>
    </div>
</div>