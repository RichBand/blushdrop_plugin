
/**
 * Created by ricardobandala on 2016-08-15.
 */

var model = {
    main: {name: "", id: 0, price:0, sale_price:0, regular_price:0},
    minutes: {name: "", id: 0, price:0, sale_price:0, regular_price:0},
    music:{name: "", id: 0, price:0, sale_price:0, regular_price:0},
    dvd:{name: "", id: 0, price:0, sale_price:0, regular_price:0},
    raw:{name: "", id: 0, price:0, sale_price:0, regular_price:0},
    currentTotalTime:0,
    isCustomer:"",
    mainProduct: 0,
    musicTrack:[],
    baseProducts: [],
    abspath:"",
    url:""
};
var view = {
    checkout: [],
    main: {name: "main", id: 51, quantity:1, price:0},
    minutes: {name: "minutes", id: 79, quantity:0, price:0},
    music:{name: "music", id: 0, quantity:1, price:0},
    dvd:{name: "dvd", id:0, quantity:0, price:0},
    raw:{name: "raw", id:0, quantity:0, price:0},
    order: new Array(),
    totalOrder:0,
    ajaxCart:function(){
      var order = view.order;
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
    setOrder:function(){
      view.order.push(view.main);

      if(view.music.quantity > 0 ){
        view.order.push(view.music)
      };
      if(view.dvd.quantity > 0 ){
        view.order.push(view.dvd)
      };
      if(view.raw.quantity > 0 ){
        view.order.push(view.raw)
      };
      if(view.minutes.quantity > 0){
        view.order.push(view.minutes)
      };
      view.ajaxCart();
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
    var parent  = document.getElementsByClassName("entry-content")[0];
    var eleMain = document.createElement("DIV");
    eleMain.id = "bdpMain";
    parent.appendChild(eleMain);
        var eleLefth = document.createElement("DIV");
        eleLefth.id = "bdpLefth";
        eleMain.appendChild(eleLefth);

            var eleLefth0 = document.createElement("DIV");
            eleLefth0.id = "bdpLefth1";
            eleLefth0.className = "bdpLefthN";
            eleLefth.appendChild(eleLefth0);

              var eleParagraphMain = document.createElement("P");
              eleParagraphMain.innerHTML="Editing Package: (ten minutes of raw material included) ";
              eleLefth0.appendChild(eleParagraphMain);

            var eleLefth1 = document.createElement("DIV");
            eleLefth1.id = "bdpLefth1";
            eleLefth1.className = "bdpLefthN";
            eleLefth.appendChild(eleLefth1);

                var eleParagraphMusic = document.createElement("P");
                eleParagraphMusic.innerHTML="Select a song: ";
                eleLefth1.appendChild(eleParagraphMusic);

                var eleSelectMusic = document.createElement("SELECT");
                eleSelectMusic.onchange = function(){
                  view.setMusic(this.value);
                }
                eleLefth1.appendChild(eleSelectMusic);
                selectMusic(eleSelectMusic);

            var eleLefth2 = document.createElement("DIV");
            eleLefth2.id = "bdpLefth2";
            eleLefth2.className = "bdpLefthN";
            eleLefth.appendChild(eleLefth2);

                var eleParagraphDVD = document.createElement("P");
                eleParagraphDVD.innerHTML="Number of DVD's : ";
                eleLefth2.appendChild(eleParagraphDVD);

                var elePlusDVDAmount = document.createElement("P");
                elePlusDVDAmount.innerHTML = "+";
                elePlusDVDAmount.onclick = function(){
                   view.setDVD(1);
                };
                eleLefth2.appendChild(elePlusDVDAmount);

                var eleInputDVDAmount = document.createElement("INPUT");
                eleInputDVDAmount.id = "eleInputDVDAmount";
                eleInputDVDAmount.type = "number";
                eleInputDVDAmount.min = "0";
                eleInputDVDAmount.max = "99";
                eleInputDVDAmount.value = "0";
                eleInputDVDAmount.onblur = function(){
                  view.setDVD(this.value);
                };
                eleLefth2.appendChild(eleInputDVDAmount);

                var eleMinusDVDAmount = document.createElement("P");
                eleMinusDVDAmount.innerHTML = "-";
                eleMinusDVDAmount.onclick = function(){
                  view.setDVD(-1);
                };
                eleLefth2.appendChild(eleMinusDVDAmount);

            var eleLefth3 = document.createElement("DIV");
            eleLefth3.id = "bdpLefth3";
            eleLefth3.className = "bdpLefthN";
            eleLefth.appendChild(eleLefth3);

                var eleParagraphRaw = document.createElement("P");
                eleParagraphRaw.innerHTML="Include raw Footage? ";
                eleLefth3.appendChild(eleParagraphRaw);

                var eleCheckboxRaw = document.createElement("INPUT");
                eleCheckboxRaw.id = 'eleCheckboxRaw';
                eleCheckboxRaw.type = 'checkbox';
                eleCheckboxRaw.onclick = function(){
                  view.setRAW(this);
                };
                eleLefth3.appendChild(eleCheckboxRaw);

            var eleLefth4 = document.createElement("DIV");
            eleLefth4.id = "bdpLefth4";
            eleLefth4.className = "bdpLefthN";
            eleLefth.appendChild(eleLefth4);

            var currentTotalTime = model.currentTotalTime || 0;
            view.setMinutes(currentTotalTime);
                var eleParagraphMinutes = document.createElement("P");
                eleLefth4.appendChild(eleParagraphMinutes);
                eleParagraphMinutes.innerHTML = (currentTotalTime <= 0)? "No extra minutes yet " : "Extra minutes: ";
                var eleExtraMinutes = document.createElement("INPUT");
                eleExtraMinutes.id = "eleExtraMinutes";
                eleExtraMinutes.disabled = true;
                eleExtraMinutes.value = (currentTotalTime > 0)? currentTotalTime : 0;
                eleLefth4.appendChild(eleExtraMinutes);

        var eleRight = document.createElement("DIV");
        eleRight.id = "eleRight";
        eleMain.appendChild(eleRight);

            var eleRight1 = document.createElement("DIV");
            eleRight1.id = "bdpRight1";
            eleRight1.className = "bdpRightN";
            eleRight.appendChild(eleRight1);

            var eleRight2 = document.createElement("DIV");
            eleRight2.id = "bdpRight2";
            eleRight2.className = "bdpRightN";
            eleRight2.innerHTML = "Total: $";
            eleRight.appendChild(eleRight2);

                var buttonSubmitOrder = document.createElement("BUTTON");
                buttonSubmitOrder.id = "bdpSubmitOrder";
                buttonSubmitOrder.innerHTML = "Submit Order";
                buttonSubmitOrder.className = "bdpRightN";
                buttonSubmitOrder.onclick = function(){
                  view.setOrder();
                };
                eleRight2.appendChild(buttonSubmitOrder);
}
function selectMusic(container){
    for(nP = 0, nPz = model.musicTrack.length; nP < nPz; nP++ ){
        var track = model.musicTrack[nP];
        if(!track.inCart){
            var opt = document.createElement("option");
            opt.id = "opt-"+track.id;
            opt.value = track.id;
            opt.innerHTML = track.title;
            container.appendChild(opt);
        }
    };
};
window.onload = function(){
    renderElements();
}
