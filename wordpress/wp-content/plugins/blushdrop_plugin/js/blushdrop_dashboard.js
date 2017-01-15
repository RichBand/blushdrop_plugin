var bdp = {};
(function($){

    $(document).ready(function(){
        var dataModel = $('#bdpMain').data();
        bdp.model = {
            ajaxUrl : dataModel.ajaxurl,
            currentTotalMinutes : dataModel.currenttotalminutes,
            customer : dataModel.customer,
            products : dataModel.products,
            siteUrl : dataModel.siteurl,
        };

        if(bdp.model.products){
            bdp.populateInfoElements();
        }
    });
    bdp = {
        model:{},
        collectOrder:function(){
            var products = this.model.products;
            if(typeof products === 'undefined'){
                console.log('not able to access products')
                return [];
            }
            var order = [{id: parseInt(products.main.ID), qty: 1}];//Always add the main product

            var disc = $("#eleInputDiscAmount");
            var discValue = parseInt(disc.val());
            if (isNaN(discValue) || discValue < 0 || discValue > 99){
                var disclabel = $("#labeleleInputDiscAmount");
                discLabel.innerHTML = "Please enter a valid number of discs";
                discValue = 0;
                disc.focus();
                return false;
            }
            else {
                order.push({
                    id: parseInt(products.disc.ID),
                    qty: parseInt(discValue),
                })
            }

            var checked = $("#eleCheckboxRaw").prop('checked');
            if(checked){
                order.push({
                    id: parseInt(products.raw.ID),
                    qty: (checked)? 1  : 0,
                });
            }

            var musicVal = $("#eleSelMusic").val();
            if (musicVal) {
                order.push({
                    id: parseInt(musicVal),
                    qty: 1
                })
            }

            var updatedMinutes = parseInt(this.controller.ajax_getMinutes() - 10);
            updatedMinutes = (updatedMinutes > 0)? updatedMinutes : 0;
            var minutes = products.minute.isInCart.qty;
            var diff = updatedMinutes - minutes;
            if(diff) {
                order.push({
                    id: parseInt(products.minute.ID),
                    qty: updatedMinutes
                });
                if(minutes){
                    var msg = "You have " + Math.abs(diff) + ((diff > 0) ?
                            " more extraMinutes, and will be added to the checkout"
                            : " less extraMinutes, and will be taken to the checkout");
                    if (!confirm(msg)) {
                        $("#bdp_background").classList.remove("bdp_background--on");
                        return false
                    }
                }
            }
            return order;
        },
        controller : {
            ajaxURLCart:function(order){
                //TODO, future application
                for(var i = 0, j = order.length; i < j ; i++) {
                    var product = order[i];
                    $.ajax({
                        url: "/?add-to-cart="+product.id+"&quantity="+product.quantity+"&t=" + Math.random(),
                        success: function (data) {
                            window.location.href = bdp.model.siteUrl + '/cart';
                        },
                        error: function (errorThrown) {
                            alert("an unexpected error happen, please reload or try in few minutes");
                            $("#bdp_background").removeClass("bdp_background--on");
                        }
                    });
                }
            },
            ajax_addTocart:function(order){
                var response = "";
                $.ajax({
                    url: bdp.model.ajaxUrl,
                    data: {
                        'action':'addOrderToCart',
                        'order' : order
                    },
                    success:function(data) {
                        window.location.href = bdp.model.siteUrl + '/cart';
                    },
                    error: function(errorThrown){
                        alert("an unexpected error happen, please reload or try in few minutes");
                        $("#bdp_background").removeClass("bdp_background--on");
                    }
                });
                return response;
            },
            ajax_getMinutes:function(){
                var response ='';
                $.ajax({
                    async: false,
                    url: bdp.model.ajaxUrl,
                    data: {
                        'action':'getMinutes',
                        'userID' : bdp.model.customer,
                    },
                    success:function(data, textStatus, request) {
                        response = data;
                    },
                    error: function(errorThrown){
                        $("#bdp_background").removeClass("bdp_background--on");
                    }
                });
                return response;
            },
            zzz_controller:0,
        },
        populateInfoElements : function(){
            var prod = this.model.products;

            $("#eleCheckboxRaw").prop('checked', (prod.raw.isInCart.ok ? true : false));

            $("#eleInputDiscAmount").val( (prod.disc.isInCart.ok)?  prod.disc.isInCart.qty || 0 : 0 );

            $("#eleSelMusic").html( (function(){
                catalog = prod.music;
                var htm ="";
                catalog.forEach(function(track){
                    htm +="<option id='" + track.ID + "' value='" + track.ID + "'"
                        + ( (track.isInCart.ok)? "selected" : "") + ">" + track.post_title + "</option>";
                });
                return htm;
            })());

            $("#eleExtraMinutes").val( ( this.model.currentTotalMinutes - 10 > 0 )? this.model.currentTotalMinutes : 0 );

            this.updateSubtotal();
        },
        setDVD : function(qty) {
            var value = $("#eleInputDiscAmount").val();
            value = (qty == 1)? value++ : (qty == -1 && input.value > 0)? value-- : value;
            $("#eleInputDiscAmount").val(value);
            this.updateSubtotal();
        },
        submitOrder: function () {
            $("#bdp_background").addClass("bdp_background--on");
            this.controller.ajax_addTocart(this.collectOrder());
        },
        updateSubtotal : function(){
            var products = this.model.products;
            var sum = 0;
            sum += ( products.main.price );
            sum += ( $("#eleInputDiscAmount").val() ) * ( products.disc.price );
            sum += ( $("#eleExtraMinutes").val() ) * ( products.minute.price );
            sum += ( $("#eleCheckboxRaw").prop('checked') )? products.raw.price : 0;
            $("#info_Subtotal__amount").html(sum);
        },
    }

})(jQuery);
