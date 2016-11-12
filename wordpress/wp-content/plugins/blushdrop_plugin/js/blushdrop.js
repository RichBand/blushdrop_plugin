
/**
 * Created by ricardobandala on 2016-08-15.
 */
window.onload = function(){
    view.aaa_view();
}
var model = {};
var view = {
    checkout:[],
    products:{},
    aaa_view:function(){
        view.populateInfoElements();
    },
    get: function(container) {
        return document.getElementById(container);
    },
    populateCheckRaw: function(container){
        var ele = this.get(container);
        var raw = model.products.raw;
        ele.checked = (raw.isInCart.ok)? 1 : 0;
    },
    populateDiscAmount: function(container){
        var ele = view.get(container);
        var disc = model.products.disc;
        ele.value = (disc.isInCart.ok)? disc.isInCart.qty:0;
    },
    populateExtraMinutes: function(container){
        var ele = view.get(container);
        var minutes = model.currentTotalMinutes - 10;
        var hasExtraMinutes = (minutes > 0 )? 1 : 0;
        ele.value = (hasExtraMinutes)? minutes : 0;
    },
    populateInfoElements:function(){
        //this.populateCheckRaw("eleCheckboxRaw");
        this.populateDiscAmount("eleInputDiscAmount");
        this.populateExtraMinutes("eleExtraMinutes");
        this.populateSelMusic("eleSelMusic");
        this.updateSubtotal();
    },
    populateSelMusic: function (container) {
        $music_Catalog = model.products.music;
        var htm ="";
        for (nP = 0, nPz = $music_Catalog.length; nP < nPz; nP++) {
            var track = $music_Catalog[nP];
            htm +="<option id='" + track.ID + "' value='" + track.ID + "'"
                + ( (track.isInCart.ok)? "selected" : "") + ">" + track.post_title + "</option>";
        }
        this.get(container).innerHTML = htm;
    },
    setDVD:function(qty) {
        var input = this.get("eleInputDiscAmount");
        if (qty == 1) {
            input.value++;
        }
        if (qty == -1 && input.value > 0) {
            input.value--;
        }
        this.updateSubtotal();
    },
    updateSubtotal:function(){
        var products = model.products;
        var sum = 0;
        sum += ( model.products.main.price );
        sum += ( this.get("eleInputDiscAmount").value ) * ( products.disc.price );
        sum += ( this.get("eleExtraMinutes").value ) * ( products.minute.price );
        sum += ( this.get("eleCheckboxRaw").checked )? products.raw.price : 0;
        this.get("info_Subtotal__amount").innerHTML = sum;
    },
    zzz_view: 0
};
var controller = {
    ajaxURLCart:function(){
        var order = view.checkout;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                console.log(xhttp.responseText);
            }
        };
        for(var i = 0, j = order.length; i < j ; i++){
            var product = order[i];
            url = model.url+"?add-to-cart="+product.id+"&quantity="+product.quantity+"&t=" + Math.random()
            xhttp.open("GET", url, true);
            xhttp.send();
        }
    },
    ajax_addTocart:function(order){
        var response = "";
    jQuery(document).ready(function($){
        $.ajax({
            async: false,
            url: model.ajaxurl,
            data: {
                'action':'addOrderToCart',
                'order' : order
            },
            success:function(data) {
                console.log(data);
                response = data;
                return data;
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    })
        return response;
    },
    ajax_getMinutes:function(){
        var response = "";
    jQuery(document).ready(function($){
        $.ajax({
            async: false,
            url: model.ajaxurl,
            data: {
                'action':'getMinutes',
                'userID' : model.customer
            },
            success:function(data) {
                console.log(data);
                response = data;
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    });
        return response;
    },
    submitOrder: function () {
        view.get("bdp_background").classList.add("bdp_background--on");
        var products = model.products;
        var order = [{id: parseInt(products.main.ID), qty: 1}];//Always add the main product
        var disc = view.get("eleInputDiscAmount");
        var discValue = parseInt(disc.value);
        if (isNaN(discValue) || discValue < 0 || discValue > 99){
            var disclabel = view.get("labeleleInputDiscAmount");
            discLabel.innerHTML = "Please enter a valid number of discs";
            discValue = 0;
            disc.focus();
            return false;
        }
        else {
            order.push({
                id: parseInt(products.disc.ID),
                qty: parseInt(disc.value),
            })
        }
        var raw = view.get("eleCheckboxRaw");
        order.push({
            id: parseInt(products.raw.ID),
            qty: (raw.checked)? 1  : 0
        });
        var music = view.get("eleSelMusic");
        if (music.value) {
            order.push({
                id: parseInt(music.value),
                qty: 1
            })
        }
        var updatedMinutes = parseInt(this.ajax_getMinutes() - 10);
        updatedMinutes = (updatedMinutes > 0)? updatedMinutes : 0
        var minutes = model.products.minute.isInCart.qty;
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
                    view.get("bdp_background").classList.remove("bdp_background--on");
                    return false
                }
            }
        }
        if (this.ajax_addTocart(order)) {
            window.location.href = model.url + '/cart';
        }
        else {
            alert("an unexpected error happen, please reload or try in few minutes")
        }
        console.log(order);
    },
    zzz_controller:0,
};