
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
        var minutes = model.currentTotalMinutes;
        var hasExtraMinutes = (minutes > 0 )? 1 : 0;
        view.get("labelExtraMinutes").innerHTML = (hasExtraMinutes)? "Extra minutes:" : "No extra minutes yet";
        ele.value = (hasExtraMinutes)? minutes : 0;
    },
    populateInfoElements:function(){
        this.populateCheckRaw("eleCheckboxRaw");
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
            htm +="<option id='"+track.ID+"' value='"+track.ID+"' ";
            htm +=">"+track.post_title+"</option>";
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
                //document.write(response);
                //return data;
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    });
        return response;
    },
    submitOrder: function () {
        var order = [];
        var products = model.products;
        order.push({ // Default editing package
            id: parseInt(products.main.ID),
            qty: 1
        });
        var disc = view.get("eleInputDiscAmount");
        if (isNaN(disc.value) || disc.value < 0 || disc.value > 99) {
            var disclabel = view.get("labeleleInputDiscAmount");
            discLabel.innerHTML = "Please enter a valid number of discs";
            disc.value = 0;
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
        if (raw.checked) {
            order.push({
                id: parseInt(products.raw.ID),
                qty: 1,
            })
        }
        var music = view.get("eleSelMusic");
        if (music.value) {
            order.push({
                id: parseInt(music.value),
                qty: 1,
            })
        }
        var updatedMinutes =  parseInt(this.ajax_getMinutes());
        var minutes = model.currentTotalMinutes;
        if(minutes != updatedMinutes){
            var updatedAreMore = (updatedMinutes > minutes)? 1 : 0;
            var newMinutes = (updatedAreMore)? updatedMinutes - minutes : minutes - updatedMinutes;
            var msg = "You have " + newMinutes + ((updatedAreMore)?
                " more extraMinutes, and will be added to the checkout"
                :" less extraMinutes, and will be taken to the checkout");
            var r = confirm(msg);
            if (r == true) {
                order.push({
                    id: parseInt(products.minute.ID),
                    qty: parseInt(updatedMinutes),// or zero?
                })
            } else {
                return false
            }
        }
        console.log(order);
        this.ajax_addTocart(order);
    },
    zzz_controller:0,
};