
/**
 * Created by ricardobandala on 2016-11-26.
 */
var blushdropCart = {};
(function($){
    window.onload = function(){
        blushdropCart.view.applyRules();
    }
    blushdropCart = {
        view: {
            rules: null,
            applyRules: function () {
                var items = ($('.shop_table')[0]).getElementsByClassName('cart_item')
                for (x = 0, j = items.length; x < j; x++) {
                    var item = items[x];
                    var productRemove = (item.getElementsByClassName('remove')[0]);
                    var productQuantityInput = (item.getElementsByClassName('product-quantity')[0]);
                    var productId = productRemove.getAttribute('data-product_id');



                        delete productRemove
                        from
                        DOM
                        productQuantityInput.setAttribute('disabled', 'true')

                }
            }
        }
    }
})(jQuery);