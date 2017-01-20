(function($){
    $('div.woocomerce > form > table.cart ').ready(function(){
            var rules = $('#bdp_cr').data();
            $('.cart_item').each(function(){
                var anchor = $(this).find('a.remove');
                var id = $.inArray(anchor.data('product_id'), rules.opc);
                if(id != -1){
                    anchor.remove()
                }
                var input =  $(this).find('div.quantity input');
                id = $.inArray(anchor.data('product_id'), rules.nmq);
                if(id != -1) {
                    input.prop('type', 'hidden').before(input.val());
                }
            })
        var x = 0;
    })
})(jQuery);