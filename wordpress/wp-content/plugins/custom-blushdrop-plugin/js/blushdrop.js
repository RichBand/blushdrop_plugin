/**
 * Created by ricardobandala on 2016-08-15.
 */
var model = {
    currentTotalTime:0,
    mainProduct:0,
    musicTrack:[],
    baseProducts: [],
    abspath:""
};
var view = {
    checkout: [],
    setCheckout: function(){
        //get the values of the three elements
        this.getElement("eleSelectMusic")
    },
    getMusic: function(){},
    //onclick
    getDVD: function(){},
    //onclick
    getRAW: function(){},
    getElement:function(ele){


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
function selectMusic(container){
    var parentSelect = document.getElementById(container);

    //var parentExcertp = parentSelect.nextSibling;
    for(nP = 0, nPz = model.musicTrack.length; nP < nPz; nP++ ){
        var track = model.musicTrack[nP];
        if(!track.inCart){
            var opt = document.createElement("option");
            opt.id = "opt-"+track.id;
            opt.value = track.id;
            opt.innerHTML = track.title;
            parentSelect.appendChild(opt);
            /*var excertp = document.createElement("div");
             excertp.id = "exrpt-"+track.id;
             excertp.innerHTML = track.excerpt;
             parentExcertp.appendChild(excertp);*/
        }
    };
};
function extraMinutes(container){
    var parent = document.getElementById(container);
    var y = parent.getElementsByClassName("woocommerce-Price-currencySymbol")[0].nextSibling
    var price = parseFloat(y.textContent);
    var minutes = model.currentTotalTime;
    var amount = 0;
    y.textContent = "Total Minutes: "+minutes;
    if(model.mainProduct){
        minutes -= 10;
        amount = price*minutes
        y.textContent += " - Minutes included: 10 ="+minutes
        +"Total Amount: "+amount;
    }
    else{
        y.textContent += "Total Amount: "+(price * minutes);
    }
}
window.onload = function(){
    dropboxUIContainer.container = document.getElementById("OutoftheBox");
    dropboxUIContainer.onClick();
    selectMusic("bdpCustomerMusicOptions");
    extraMinutes("bdpCustomerMinutes");
    //populateBaseProducts("bdpBaseProducts");
}