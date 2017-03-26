
(function($){
    $(document).ready(function(){
        cartRules.wait();
        //cartRules.apply();
    });
    $(document).ajaxStart(function(){
        //cartRules.wait();
    });
    $(document).ajaxComplete(function(){
        cartRules.apply();

    });
    var cartRules = {
        apply:function(){
            var rules = $('#bdp_cr').data();
            $('#content').find('div.woocommerce').first().find('tr.cart_item').each(function(){
                var anchor = $(this).find('a.remove');
                var id = $.inArray(anchor.data('product_id'), rules.opc);
                if(id != -1){
                    anchor.remove()
                }
                var input =  $(this).find('div.quantity input');
                id = $.inArray(anchor.data('product_id'), rules.nmq);
                if(id != -1) {
                    input.prop('disabled', true);
                    input.css('border', 0);
                    
                }
            })
            cartRules.ready();
        },
        wait:function(){
            $('#bdp_background').css({
                display:'block',
                'background-color':'rgba(255,255,255,0.95)',
            })
        },
        ready:function(){
            $('#bdp_background').css({
                display:'none',
                'background-color':'rgba(255,255,255,0.2)',
            })
        },
    }
    cartRules.wait();
})(jQuery);