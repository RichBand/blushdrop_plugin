<?
$author =  $author->data;
$siteUrl = get_site_url();

function mdlGrid($screen = 12, $tablet = 8, $phone = 4, $allign=''){
   return "mdl-cell $allign mdl-cell--$screen-col mdl-cell--$tablet-col-tablet mdl-cell--$phone-col-phone";
}


?>
<div id="bdp_background" class="bdp_background">
    <div class="bdp-spinner mdl-spinner mdl-spinner--single-color mdl-js-spinner is-active"></div>
</div>
<div id="bdpMain"
    <div class="mdl-grid">
        <script>

            (function($){
                $(document).ready(function(){
                    $('#OutoftheBox').remove()
                    $('#visitor_tos').on('click', function(){
                        console.log(this.checked);
                        $('#visitor_name').prop('disabled',!this.checked);
                        $('#visitor_email').prop('disabled',!this.checked);
                    });
                    $('#visitor_agree').on('click', function(){
                        var name = $('#visitor_name').val();
                        var email =  $('#visitor_email').val();

                        if(true) { // if TOS is checked
                            console.log('<?= $siteUrl ?>/newVisitor');
                            console.log('<?= $siteUrl . '/' . (urlencode($author->display_name)) ?>/' + name);
                            $.ajax({
                                async: false,
                                url: '<?= $siteUrl ?>/newVisitor',
                                data: {
                                    'name': name,
                                    'email': email,
                                    'owner' : <?= $author->ID ?>
                                },
                                dataType: 'json',
                                success: function (data, textStatus, request) {
                                    window.location.href = '<?= $siteUrl . '/' . (urlencode($author->display_name)) ?>/' + name;
                                },
                                error: function (data, textStatus, request) {

                                }
                            });
                        }
                    });
                });

            })(jQuery)
        </script>

        Welcome to the page of <?= $author->display_name ?>!
        They are really happy to get your point of view, before start, please
        read and check if you agree in the TOS:
        <input id="visitor_tos" type="checkbox">
        <br/>
        also, <?= $author->display_name ?> would love to know who upload the material,
        <input id="visitor_name" type="text" disabled>
        <br/>
        even though you don't need to login, we strongly suggest you to create an account to come back
        <input id="visitor_email" type="email" disabled>
        <input id="visitor_agree" type="submit">
    </div>
</div>