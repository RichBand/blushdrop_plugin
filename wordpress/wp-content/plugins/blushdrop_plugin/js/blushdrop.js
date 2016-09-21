
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
        controller.populateInputs();
    },
    get: function(container) {
        return document.getElementById(container);
    },
    setDVD:function(qty) {
        var input = view.get("eleInputDiscAmount");
        if (qty == 1) {
            input.value++;
        }
        if (qty == -1 && input.value > 0) {
            input.value--;
        }
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
    populateInputs:function(){
        controller.populateCheckRaw("eleCheckboxRaw");
        controller.populateDiscAmount("eleInputDiscAmount");
        controller.populateExtraMinutes("eleExtraMinutes");
        controller.populateSelMusic("eleSelMusic");
    },
    populateCheckRaw: function(container){
        var ele = view.get(container);
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
    populateSelMusic: function (container) {
        $music_Catalog = model.products.music;
        var htm ="";
        for (nP = 0, nPz = $music_Catalog.length; nP < nPz; nP++) {
            var track = $music_Catalog[nP];
            htm +="<option id='"+track.ID+"' value='"+track.ID+"' ";
            htm += (track.isInCart.ok)?"disabled":"";
            htm +=">"+track.post_title+"</option>";
        }
        view.get(container).innerHTML = htm;
    },
    submitOrder: function () {
        var order = [];
        var products = model.products;
        var disc = view.get("eleInputDiscAmount");
        if (isNaN(disc.value) || disc.value == 0 || disc.value > 99) {
            var disclabel = view.get("labeleleInputDiscAmount");
            discLabel.innerHTML = "Please enter a valid number of discs";
            disc.value = 0;
            disc.focus();
            return false;
        }
        else {
            order.push({
                id: products.disc.ID,
                qty: disc.value,
            })
        }
        var raw = view.get("eleCheckboxRaw");
        if (raw.checked) {
            order.push({
                id: products.raw.ID,
                qty: 1,
            })
        }
        var music = view.get("eleSelMusic");
        if (music.value != "" || music.value != null) {
            order.push({
                id: music.value,
                qty: 1,
            })
        }
        var updatedMinutes =  controller.ajax_getMinutes();//minus 10 from url
        var minutes = model.currentTotalMinutes;
        if(minutes != updatedMinutes){
            var areMore = (updatedMinutes > minutes)? 1 : 0;
            var newMinutes = updatedMinutes - minutes;
            var msg = "You have " + newMinutes + ((areMore)?
                " more extraMinutes, and will be added to the checkout" //TODO RD, check grammar
                :" less extraMinutes, and will be taken to the checkout");
            var r = confirm(msg);
            if (r == true) {
                order.push({
                    id: products.minute.ID,
                    qty: updatedMinutes,
                })
            } else {
                return false
            }
        }
        console.log(order);
        controller.ajax_addTocart(order);
    },
    zzz_controller:0,
};


function submitOrder(){
    ajax_addTocart();
}
/**
    checkout: [],
    main: {name: "main", id: 51, quantity:1, price:0},
    minutes: {name: "minutes", id: 79, quantity:0, price:0},
    music:{name: "music", id: 0, quantity:1, price:0},
    dvd:{name: "dvd", id:0, quantity:0, price:0},
    raw:{name: "raw", id:0, quantity:0, price:0},
    order: new Array(),
    totalOrder:0,
    setDVD:function(quantity){
      var input = document.getElementById("eleInputDVDAmount");
      var q = (parseInt(input.value) == quantity)? quantity : parseInt(input.value) + quantity;
      if(q <= 0 || q == null ){
        view.dvd.quantity = 0;
        input.value = 0;
      }
      else{
view.dvd.id = model.baseProducts[1].id;
        view.dvd.quantity = q;
        input.value = q;
      }
    },
    setRAW:function(obj){
      if(obj.checked){
        view.raw.id = model.baseProducts[0].id;
        view.raw.quantity = 1;
        setTotalOrder(view.minutes.price);
      }
      else{
        view.raw.quantity = 0;
        setTotalOrder(-(view.minutes.price));
      }
    },

    setMinutes:function(amount){
      view.minutes.quantity = amount;
      amount = amount * view.minutes.price;
      view.setTotalOrder(amount);
    },
    setMusic:function(product){
      view.music.id = product;
    },
    setTotalOrder: function(amount){
        view.totalOrder += amount;
        //document.getElementById('bdpRight2').innerHTML = "Total: $"+view.totalOrder;
    }
};
var dropboxUIContainer = {
    container: null,
    onClick: function(){
        //this.container.addEventListener("mouseout", function(){
            console.log("hola");
        //});
    }
}
function populateBaseProducts(container){
    var parent = document.getElementById(container);
    //var parentExcertp = parentSelect.nextSibling;
    for(nP = 0, nPz = model.baseProducts.length; nP < nPz; nP++ ){
        var product = model.baseProducts[nP];
        if(!product.inCart){
            var ele = document.createElement("DIV");
            ele.id = "opt-"+product.id;
            ele.className = "baseProduct";
            ele.innerHTML = product.title;
            parent.appendChild(ele);
        }
    };
};
function renderElements(){


                var eleSelectMusic = document.createElement("SELECT");
                eleSelectMusic.onchange = function(){
                  view.setMusic(this.value);
                }
                elePlusDVDAmount.onclick = function(){
                   view.setProductDVD(1);
                };
                eleInputDVDAmount.onblur = function(){
                  view.setProductDVD(this.value);
                };
                eleMinusDVDAmount.onclick = function(){
                  view.setProductDVD(-1);
                };
                eleCheckboxRaw.onclick = function(){
                  view.setRAW(this);
                };
            var currentTotalTime = model.currentTotalTime || 0;
                eleParagraphMinutes.innerHTML = (currentTotalTime <= 0)? "No extra minutes yet " : "Extra minutes: ";
                eleExtraMinutes.disabled = true;

                buttonSubmitOrder.onclick = function(){
                  view.setCheckout();
                };
}
**/