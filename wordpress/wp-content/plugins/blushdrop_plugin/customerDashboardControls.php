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
                    <div class="container">
                        <div class="column center">
                            <h1>HTML5 Audio Player</h1>
                            <h6>w/ responsive playlist</h6>
                        </div>
                        <div class="column add-bottom">
                            <div id="mainwrap">
                                <div id="nowPlay">
                                    <span class="left" id="npAction">Paused...</span>
                                    <span class="right" id="npTitle"></span>
                                </div>
                                <div id="audiowrap">
                                    <div id="audio0">
                                        <audio preload id="audio1" controls="controls">Your browser does not support HTML5 Audio!</audio>
                                    </div>
                                    <div id="tracks">
                                        <a id="btnPrev">&laquo;</a>
                                        <a id="btnNext">&raquo;</a>
                                    </div>
                                </div>
                                <div id="plwrap">
                                    <ul id="plList">
                                        <li>
                                            <div class="plItem">
                                                <div class="plNum">01.</div>
                                                <div class="plTitle">All This Is - Joe L.'s Studio</div>
                                                <div class="plLength">2:46</div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="plItem">
                                                <div class="plNum">02.</div>
                                                <div class="plTitle">The Forsaken - Broadwing Studio (Final Mix)</div>
                                                <div class="plLength">8:31</div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="plItem">
                                                <div class="plNum">36.</div>
                                                <div class="plTitle">The Forsaken (Take 2) - Smith St. Basement (Nov. '03)</div>
                                                <div class="plLength">8:37</div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
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