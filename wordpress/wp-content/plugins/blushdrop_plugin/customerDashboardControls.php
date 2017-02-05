<?
$currentTotalMinutes = empty($currentTotalMinutes)? 0 : intval($currentTotalMinutes);
$user = empty($user)? 0 : intval($user->ID);
$products = empty($products)? json_encode([]) : htmlspecialchars(json_encode($products), ENT_QUOTES, 'UTF-8');
$siteUrl = get_site_url();


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
        <div class="mdl-cell mdl-cell--8-col mdl-cell--6-col-tablet mdl-cell--4-col-phone">
            <div class="bdp-cell mdl-grid">
                <div class="bdp-cell mdl-cell mdl-cell--8-col mdl-cell--6-col-tablet mdl-cell--4-col-phone">
                    <span> Editing: </span> <br/>
                    </span> (includes 10 minutes of raw material)</span>
                </div>
                <div class="bdp-cell mdl-cell mdl-cell--middle mdl-cell--4-col mdl-cell--2-col-tablet mdl-cell--4-col-phone">
                    <label for="eleCheckboxEditing" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                        <input type="checkbox" id="eleCheckboxEditing" class="mdl-checkbox__input" checked disabled>
                    </label>
                </div>
                <div class="bdp-cell mdl-cell mdl-cell--8-col mdl-cell--6-col-tablet mdl-cell--4-col-phone">
                    <span> Extra minutes of raw material:</span>
                </div>
                <div class="mdl-cell mdl-cell--middle mdl-cell--4-col mdl-cell--2-col-tablet mdl-cell--4-col-phone">
                    <div class="mdl-textfield mdl-js-textfield">
                        <input id="eleExtraMinutes" onchange="bdp.updateSubtotal();" class="mdl-textfield__input"
                               type="text" pattern="-?[0-9]*(\.[0-9]+)?" disabled>
                    </div>
                </div>
                <div class="bdp-cell mdl-cell mdl-cell--8-col mdl-cell--6-col-tablet mdl-cell--4-col-phone">
                    <span> DVD disc quantity:</span>
                </div>
                <div class="mdl-cell mdl-cell--middle mdl-cell--4-col mdl-cell--2-col-tablet mdl-cell--4-col-phone">
                    <div class="mdl-textfield mdl-js-textfield">
                        <input id="eleInputDiscAmount" onchange="bdp.updateSubtotal();" class="mdl-textfield__input" type="text" pattern="-?[0-9]*(\.[0-9]+)?" >
                        <span class="mdl-textfield__error">Input is not a number!</span>
                    </div>
                </div>
                <div class="bdp-cell mdl-cell mdl-cell--8-col mdl-cell--6-col-tablet mdl-cell--4-col-phone">
                    <span> Ship Raw Footage</span>
                </div>
                <div class="mdl-cell mdl-cell--middle mdl-cell--4-col mdl-cell--2-col-tablet mdl-cell--4-col-phone">
                    <label for="eleCheckboxRaw" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                        <input type="checkbox" id="eleCheckboxRaw" class="mdl-checkbox__input"
                               onchange="bdp.updateSubtotal();" />
                    </label>
                </div>
                <div class="mdl-cell mdl-cell--middle mdl-cell--12-col mdl-cell--8-col-tablet mdl-cell--4-col-phone">
                    <!--div class="mdl-textfield mdl-js-textfield">
                        <select id="eleSelMusic" class="mdl-textfield__input"></select>

                    </div-->
                    <div id="interface--info">
                        <div id="info__cover"    class="mdl-cell mdl-cell--4-col mdl-cell--2-col-tablet mdl-cell--4-col-phone" ></div>
                        <div id="info__status"   class="mdl-cell mdl-cell--8-col mdl-cell--2-col-tablet mdl-cell--4-col-phone" ></div>
                        <div id="info__title"    class="mdl-cell mdl-cell--8-col mdl-cell--2-col-tablet mdl-cell--4-col-phone" ></div>
                        <div id="info__artist"   class="mdl-cell mdl-cell--8-col mdl-cell--2-col-tablet mdl-cell--4-col-phone" ></div>
                        <div id="info__duration" class="mdl-cell mdl-cell--12-col mdl-cell--2-col-tablet mdl-cell--4-col-phone" ></div>
                    </div>
                    <div id="interface--player">
                        <div id="player">
                            <audio preload id="player__audio" controls="controls">
                                <source src="" type="audio.mp3"/>
                                Your browser does not support HTML5 Audio!
                            </audio>
                        </div>
                        <div id="tracks">
                            <a id="btnPrev">&laquo;</a>
                            <a id="btnNext">&raquo;</a>
                        </div>
                    </div>
                    <div id="interface--playlist">
                        <ul id="playlist"></ul>
                    </div>

            </div><!--end grid left block-->
        </div> <!--end left block-->
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