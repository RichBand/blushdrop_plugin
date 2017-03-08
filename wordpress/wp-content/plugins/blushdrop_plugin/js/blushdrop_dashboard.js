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
        bdp.musicWidget.start(1);
    });
    bdp = {
        model:{},
        musicWidget:{
            start:function(index){
                var response ='';
                $.ajax({
                    async: false,
                    url: bdp.model.ajaxUrl,
                    data: {
                        'action':'getTrackList',
                        'index': index
                    },
                    dataType:'json',
                    success:function(data, textStatus, request) {
                        Boolean(data.data != 'undefined' && data.data.length > 0)? bdp.musicWidget.buildDOM(data):null;
                    },
                    error: function(data, textStatus, request){
                        //something
                    }
                });
            },
            buildDOM:function(data){
                 var lis = '';
                 $.each(data.data, function(index, obj){
                     var pic = Boolean(obj.relationships.artist.data.pic.url)? obj.relationships.artist.data.pic.url  : 'alternate default url';
                     var url = Boolean(obj.relationships.audio_files.data[0].audio_file.versions.mp3.url)? obj.relationships.audio_files.data[0].audio_file.versions.mp3.url  : '-';
                     var title = Boolean(obj.attributes.title)? obj.attributes.title : 'no title';
                     var artistName = Boolean(obj.relationships.artist.data.name)? obj.relationships.artist.data.name : 'no artist name';
                     var duration =  Boolean(obj.attributes.duration)? obj.attributes.duration : '-';
                     var vocals = Boolean(obj.attributes.has_file_with_vocals);
                     var instrumentals = Boolean(obj.attributes.has_instrumental_file);

                     lis += '<li data-id="' + obj.id + '" data-index="' + index + '" data-title="' + title + '" data-artist="' + artistName + '" data-duration="' + duration + '" data-url="' + url + '" data-cover="' + pic + '"><ul class="mdl-grid mdl-grid--nesting">'
                            + '<li class="backgroundCover mdl-cell mdl-cell--2-col mdl-cell--2-col-tablet mdl-cell--1-col-phone" style="background-image: url('+ pic +');"></li>'
                            + '<li class="title_name mdl-cell mdl-cell--5-col mdl-cell--3-col-tablet mdl-cell--3-col-phone"><p class="title">' + title + '</p><p class="artist">' + artistName + '</p></li>'
                            + '<li class="voc_inst   mdl-cell mdl-cell--3-col mdl-cell--3-col-tablet mdl-cell--1-col-phone">' + (vocals? 'vocals ': '') + (instrumentals? 'instrumentals ': '') + '</li>'
                            + '<li class="duration   mdl-cell mdl-cell--1-col mdl-cell--4-col-tablet mdl-cell--2-col-phone">' + duration + '</li>'
                            + '<li class="checkbox   mdl-cell mdl-cell--1-col mdl-cell--4-col-tablet mdl-cell--1-col-phone"><input type="checkbox" id="song_'+ obj.id +'" class="MDLCLASS" data-title="'+ title +'" data-artist="'+ artistName +'" data-id="'+ obj.id +'" ></li>'
                          + '</ul></li>';
                 })
                $('#playlist').data(data.links).html(lis);
            },
        },
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
