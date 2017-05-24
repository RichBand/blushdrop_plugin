<?
$currentTotalMinutes = empty($currentTotalMinutes)? 0 : intval($currentTotalMinutes);
$user = empty($user)? 0 : intval($user->ID);
$theresMusicInCart = empty($products['musicInCart']);
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
        <div class="bdp-leftBlock <?=mdlGrid(8,6,4)?>">
            <div class="mdl-grid mdl-grid--nested">
                <div class="<?= mdlGrid(4,4,3, 'mdl-cell--middle')?> text-right">
                    <span> Editing: </span> <br/>
                    <span class="text-smaller"> (includes 10 minutes of raw material)</span>
                </div>
                <div class="<?= mdlGrid(8,2,1, 'mdl-cell--middle')?> text-left">
                    <label for="eleCheckboxEditing" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                        <input type="checkbox" id="eleCheckboxEditing" class="mdl-checkbox__input" checked disabled>
                    </label>
                </div>
                <div class="<?= mdlGrid(4,4,3, 'mdl-cell--middle') ?> text-right">
                    <span> Extra minutes of raw material:</span>
                </div>
                <div class="<?= mdlGrid(8,2,1, 'mdl-cell--middle')?> text-left">
                    <div class="mdl-textfield mdl-js-textfield">
                        <input id="eleExtraMinutes" onchange="bdp.updateSubtotal();" class="mdl-textfield__input"
                               type="text" pattern="-?[0-9]*(\.[0-9]+)?" disabled>
                    </div>
                </div>
                <div class="<?= mdlGrid(4,4,3, 'mdl-cell--middle') ?>  text-right">
                    <span> DVD disc quantity:</span>
                </div>
                <div class="<?= mdlGrid(8,2,1, 'mdl-cell--middle')?> text-left">
                    <div class="mdl-textfield mdl-js-textfield">
                        <input id="eleInputDiscAmount" onchange="bdp.updateSubtotal();" class="mdl-textfield__input" type="text" pattern="-?[0-9]*(\.[0-9]+)?" >
                        <span class="mdl-textfield__error">Input is not a number!</span>
                    </div>
                </div>
                <div class="<?= mdlGrid(4,4,3, 'mdl-cell--middle')?> text-right">
                    <span> Ship raw footage</span>
                </div>
                <div class="<?= mdlGrid(8,2,1, 'mdl-cell--middle')?> text-left">
                    <label for="eleCheckboxRaw" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                        <input type="checkbox" id="eleCheckboxRaw" class="mdl-checkbox__input"
                               onchange="bdp.updateSubtotal();" />
                    </label>
                </div>
                <div class="<?= mdlGrid(4,4,4, 'mdl-cell--middle')?>  text-right">
                    Selected song:
                </div>
                <div id="selectedSongName" class="<?= mdlGrid(8,4,4, 'mdl-cell--middle')?> text-left"></div>
                <input id="eleSongCode" type="hidden" val ="" onchange="bdp.updateSubtotal();">

                <div id="audioWrapper" class="<?= mdlGrid( 12, 8, 4,'mdl-grid mdl-grid--nested mdl-cell--middle')?>">
                    <div id="interface--info" class="<?= mdlGrid()?>" >
                        <div class="<?= mdlGrid(0,2,0, 'mdl-cell--middle')?>"></div>
                        <div id="info__cover"  class="backgroundCover inlineFloatLeft <?= mdlGrid(3,4,4, 'mdl-cell--middle')?>" >
                            
                        </div>
                        <div id="info__title_artist" class="inlineFloatLeft cellTight text-left <?= mdlGrid(8,8,4, 'mdl-cell--middle')?>" >
                            <span id="info__title"></span>
                            (<span id="info__artist"></span>)
                        </div>
                        <div class="inlineFloatLeft cellTight text-left <?= mdlGrid(8,8,4, 'mdl-cell--middle')?>" >
                            <span>Select this</span>
                            <label for="eleCheckboxPlayer" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                                <input type="checkbox" id="eleCheckboxPlayer" class="mdl-checkbox__input" />
                            </label>
                        </div>
                        <div id="audioControllers" class="inlineFloatLeft cellTight <?= mdlGrid(8,8,4, 'mdl-cell--middle')?>">
                            <div class="_33p text-right cellTight"><button id="player__prev" disabled>prev</button></div>
                            <div class="_33p  cellTight"><button id="player__play"> play </button></div>
                            <div class="_33p text-left cellTight"><button id="player__next">next</button></div>
                            <progress id="seekbar" value="0" max="1" style="width:100%;"></progress>
                            <div id="info__metadata" class="" >
                                <div class="_33p text-left cellTight" >
                                    <span id="info__timeupdated" class="text-left info__metadata--timer" >0:00</span>
                                </div>
                                <div class="_33p cellTight" >
                                    <span id="info__status"></span>
                                </div>
                                <div class="_33p text-right cellTight" >
                                    <span id="info__timeduration" class="text-right info__metadata--timer">0:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="interface--player">
                        <div id="player">
                            <audio preload id="player__audio">
                                <source src="" type="audio.mp3"/>
                                Your browser does not support HTML5 Audio!
                            </audio>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end grid left block-->
        <div class="bdp-rightBlock <?=mdlGrid(4,2,4, '')?>">
            <div class="mdl-grid mdl-grid--nested">
                <div id="info_Subtotal" class="<?= mdlGrid()?> text-left">
                    <span>
                        <div>Total: $</div>&nbsp
                    <div id="info_Subtotal__amount"></div>
                    </span>
                </div>
                <div class="<?= mdlGrid()?> text-left">
                    <button id="bdpSubmitOrder" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onclick="bdp.submitOrder()">Submit Order</button>
                </div>
            </div>
        </div>
    </div>
</div>