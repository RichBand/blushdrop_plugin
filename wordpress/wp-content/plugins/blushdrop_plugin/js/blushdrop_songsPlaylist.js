// html5media enables <video> and <audio> tags in all major browsers
// External File: http://api.html5media.info/1.1.8/html5media.min.js
// Add user agent as an attribute on the <html> tag...
// Inspiration: http://css-tricks.com/ie-10-specific-styles/
var b = document.documentElement;
b.setAttribute('data-useragent', navigator.userAgent);
b.setAttribute('data-platform', navigator.platform);
// HTML5 audio player + playlist controls...
// Inspiration: http://jonhall.info/how_to/create_a_playlist_for_html5_audio
// Mythium Archive: https://archive.org/details/mythium/
var audioPlayer = null;
jQuery(function ($) {
    $(document).ready(function(){
        var ap = audioPlayer;
        $.when(ap.getPlaylist(1, true)).then(function(){
            var val = $('#eleSongCode').val();
            $('#audioWrapper').slideDown();
            if(val){
                ap.getPreselectedSong(val);
                ap.playlist.unshift(ap.preselectedSong);
                ap.currentTrackIndex = 0;
            }
            else{
                ap.loadSong(ap.playlist[0]);
                ap.currentTrackIndex = 0;
            }
            ap.player.onended = function(){
                ap.playing = false;
                $('#player__play').html('play');
            };
            ap.player.oncanplay = function(){
                if(!ap.playing){
                    ap.interfaceInfo.status.html('-');
                    $('#info__timeduration').html( ap.formatDurationTime( this.duration) );
                }
            };
            $('#' + ap.player.id ).on('timeupdate', function() {
                if(ap.playing){
                    $('#seekbar').attr("value", this.currentTime / this.duration);
                    $('#info__timeupdated').html(ap.formatDurationTime( this.duration - this.currentTime) );
                }
            });


        });
    });

    var supportsAudio = !!document.createElement('audio').canPlayType;
    if (supportsAudio) {
        audioPlayer = {
            currentTrackIndex: null,
            trackListIndex:1,
            interfaceInfo: {
                title: $('#info__title'),
                duration: $('#info__duration'),
                artist: $('#info__artist'),
                cover: $('#info__cover'),
                status: $('#info__status')
            },
            links:$('#playlist').data(),
            player: $('#player__audio').get(0),
            playing:false,
            playlist: [],
            preselectedSong:null,
            songLoaded: null,
            cleanInterfaceInfo:function(){
                this.interfaceInfo.title.html('');
                this.interfaceInfo.duration.html('00:00');
                this.interfaceInfo.artist.html('');
                this.interfaceInfo.status.html('Loading List...');
                this.interfaceInfo.cover.css('background-image', '');
            },
            checkboxSelectedStatus:function(){
                var $label = $("label[for='eleCheckboxPlayer']");
                var $checkbox = $('#eleCheckboxPlayer');

                if(this.songLoaded.id != $('#eleSongCode').val() ){
                    $label.removeClass('is-checked');
                    $checkbox.prop('checked', false );
                }
                else{
                    $label.addClass('is-checked');
                    $checkbox.prop('checked', true );
                }
            },
            disableNextButton:function(disabled){
                $('#player__next').prop('disabled', disabled);
            },
            disablePrevButton:function(disabled){
                $('#player__prev').prop('disabled', disabled);
            },
            waitUI: function (thoseFunctions) {
                $('#audioWrapper').css('opacity', 0.6); // change for opacitiy 0.6
                setTimeout(function () {
                    thoseFunctions();
                    $('#audioWrapper').css('opacity', 1); // change for opacitiy 1
                }, 1);
            },
            formatDurationTime: function(secs){
                var sec= new Number();
                var min= new Number();
                sec = Math.floor( secs );
                min = Math.floor( sec / 60 );
                min = min >= 10 ? min : '0' + min;
                sec = Math.floor( sec % 60 );
                sec = sec >= 10 ? sec : '0' + sec;
                return  min + ":"+ sec;
            },
            getPlaylist:function(trackListIndex, async){
                var response =false;
                var that = this;
                return $.ajax({
                    async:async,
                    url: bdp.model.ajaxUrl,
                    data: {
                        'action':'getTrackList',
                        'index': trackListIndex
                    },
                    dataType:'json',
                    success:function(data) {
                        if( typeof data.data != 'undefined' && data.data.length > 0) {
                            that.playlist = that.playlist.concat(data.data);
                            response = true;
                        }
                        else{
                            that.disableNextButton(true);
                            response = false;
                        }
                    },
                    error: function(){
                        response = false;
                        //something
                    }
                });
            },
            getPreselectedSong:function(trackID){
                var that = this;
                if(Boolean(trackID)){
                    $.ajax({
                        async: false,
                        url: bdp.model.ajaxUrl,
                        data: {
                            'action':'getSongData',
                            'index': trackID
                        },
                        dataType:'json',
                        success:function(data) {
                            if(typeof data != 'undefined' && !$.isEmptyObject(data) ){
                                that.preselectedSong = {
                                    id: trackID,
                                    attributes:{
                                        title:data.title,
                                        duration:data.duration
                                    },
                                    relationships:{
                                        artist:{
                                            data:{
                                                name:data.author,
                                                pic:{
                                                    url:data.image
                                                }
                                            }
                                        },
                                        audio_files:{
                                            data: [{
                                                audio_file: {
                                                    versions: {
                                                        mp3: {
                                                            url: data.src
                                                        }
                                                    }
                                                }
                                            }]
                                        }
                                    }
                                };
                                that.loadSong(that.preselectedSong);
                                that.checkboxSelectedStatus();
                            }
                        },
                        error: function(data, textStatus){
                            console.log(textStatus);
                        }
                    });
                }

            },
            loadSong: function (song) {
                this.songLoaded = song;
                var url = song.relationships.audio_files.data[0].audio_file.versions.mp3.url;
                $(this.player).attr('src', url);
                var inf = this.interfaceInfo;
                inf.title.html(song.attributes.title);
                inf.duration.html(song.attributes.duration);
                inf.artist.html(song.relationships.artist.data.name);
                inf.status.html('Loading 1....');
                inf.cover.css('background-image', 'url( ' + song.relationships.artist.data.pic.url + ')');
                inf.cover.height(inf.cover.width());
            },
            play: function () {
                this.playing = true;
                this.player.play();
                this.interfaceInfo.status.html('Now Playing...');
            },
            pause: function () {
                this.playing = false;
                this.player.pause();
                this.interfaceInfo.status.html('Paused...');
            },
            ended: function (data) {
                //if index = 10 then call ajax function, else find the next element and start again
            },
            next: function () {
                var that = this;
                that.currentTrackIndex ++;
                var deadEnd = false;
                if(  that.currentTrackIndex  >= that.playlist.length){
                   $.when(that.getPlaylist(++that.trackListIndex, false)).then(function(){
                       if(typeof that.playlist[that.currentTrackIndex] == 'undefined'){
                           that.trackListIndex--;
                           that.currentTrackIndex--;
                           deadEnd = true;
                        }
                   });
                }
                if(deadEnd){
                    audioPlayer.disableNextButton(true);
                }else{
                    if(that.preselectedSong != null &&  that.playlist[that.currentTrackIndex].id == that.preselectedSong.id){
                        return that.next();
                    }
                    that.loadSong(that.playlist[that.currentTrackIndex]);
                    that.checkboxSelectedStatus();
                    (that.playing)? that.play() : null;
                }

            },
            prev: function () {
                var that = this;
                that.currentTrackIndex --;
                if(  that.currentTrackIndex  < 0){
                    audioPlayer.disablePrevButton(true);
                    that.currentTrackIndex++;
                }
                if(that.preselectedSong != null && that.playlist[that.currentTrackIndex].id == that.preselectedSong.id && that.currentTrackIndex > 0){
                    return that.prev();
                }
                else{
                    that.loadSong(that.playlist[that.currentTrackIndex]);
                    (that.playing)? that.play() : null;
                    that.checkboxSelectedStatus();
                }
            }
        };
        $('#audioControllers').on('click', 'button', function(){
            var ap = audioPlayer;
            var changed = false;
            switch( $(this).attr('id') ){
               case 'player__next':
                   ap.waitUI(function(){
                       ap.next();
                       ap.disablePrevButton(false);
                   });
                   changed = true;
                   break;
               case 'player__prev':
                   ap.prev();
                   ap.disableNextButton(false);
                   changed = true;
                   break;
               case 'player__play':
                   if(ap.playing){
                       ap.pause();
                       $(this).html('play');
                   }
                   else{
                       ap.play();
                       $(this).html('pause');
                   }
                   break;
           }
        });
        $('#eleCheckboxPlayer').on('click', function(){
            var ap = audioPlayer;
            if(ap.preselectedSong == null || ap.songLoaded.id != ap.preselectedSong.id){
                if(this.checked){
                    $('#eleSongCode').val(ap.songLoaded.id).change();
                    $('#selectedSongName').html($('#info__title_artist').text().trim());
                }
                else{
                    if ( ap.preselectedSong != null && parseInt (ap.preselectedSong.id) ){
                        $('#eleSongCode').val(ap.preselectedSong.id).change();
                        $('#selectedSongName').html(ap.preselectedSong.attributes.title + ' (' + ap.preselectedSong.relationships.artist.data.name + ')');
                    }else{
                        $('#eleSongCode').val('').change();
                        $('#selectedSongName').html('');
                    }

                }
            }
        });
    }
});

