
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
        view.setProducts();
        view.populateInputs();
    },
    ajaxCart:function(){
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
    get: function(container) {
        return document.getElementById(container);
    },
    populateInputs:function(){
        view.populateCheckRaw("eleCheckboxRaw");
        view.populateDiscAmount("eleInputDiscAmount");
        view.populateExtraMinutes("eleExtraMinutes");
        view.populateSelMusic("eleSelMusic");
    },
    populateCheckRaw: function(container){
        var ele = view.get(container);
        var raw = model.products.raw;
        ele.checked = (raw.isInCart)? 1 : 0;
    },
    populateDiscAmount: function(container){
        var ele = view.get(container);
        var disc = model.products.disc;
        ele.value = (disc.isInCart)? disc.quantityInCart:0;
    },
    populateExtraMinutes: function(container){
        var ele = view.get(container);
        var minutes = model.currentTotalMinutes;
        ele.value = (minutes > 0 )? minutes : 0;
    },
    setProducts:function(){
      view.products =  JSON.parse(JSON.stringify(model.products));
    },
    populateSelMusic: function (container) {
        $music_Catalog = model.products.music;
        var htm ="";
        for (nP = 0, nPz = $music_Catalog.length; nP < nPz; nP++) {
            var track = $music_Catalog[nP];
            htm +="<option id='"+track.ID+"' value='"+track.ID+"' ";
            htm += (track.isInCart)?"disabled":"";
            htm +=">"+track.post_title+"</option>";
        }
        view.get(container).innerHTML = htm;
    },
    setCheckout:function(){
        view.checkout.push(view.main);
        var products = view.products;
        var valid = view.validateAddtoCheckout;
        if(products.music.checkoutQuantity > 0){
            var x = {id:null};
            view.checkout.push(view.music)
        };
        if(view.dvd.quantity > 0 ){
            view.checkout.push(view.dvd)
        };
        if(view.raw.quantity > 0 ){
            view.checkout.push(view.raw)
        };
        if(view.minutes.quantity > 0){
            view.checkout.push(view.minutes)
        };
        view.ajaxCart();
    },
    validateAddtoCheckout:function(key){
        var products = view.products;
        var res = 0;
        if(products.hasOwnProperty(key)){
                res = 1;
        }
        return res;
    },
    setDVD:function(quantity){
        var input = document.getElementById("eleInputDiscAmount");
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
    zzz_view:0,
};
function ajax_getMinutes(){
    jQuery(document).ready(function($){
        $.ajax({
            url: model.ajaxurl,
            data: {
                'action':'getMinutes',
                'userID' : model.customer
            },
            success:function(data) {
                console.log(data);
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    });
}
function ajax_addTocart(){

    jQuery(document).ready(function($){
        $.ajax({
            url: model.ajaxurl,
            data: {
                'action':'addOrderToCart',
                'order' : [{"id":31, "qty":2}, {"id":67, "qty":1}, {"id":100, "qty":0}, {"id":200, "qty":1}]
            },
            success:function(data) {
                console.log(data);
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    });
}
function submitOrder(){
    ajax_addTocart();
}/**
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
 */
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
