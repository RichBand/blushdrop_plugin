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
        $.when(ap.getPlaylist(1)).then(function(){
            var val = $('#eleSongCode').val();
            if(val){
                ap.loadPreselectedSong(val);
                ap.playlist.unshift(ap.preselectedSong);
                ap.currentTrackIndex = 0;
            }
            else{
                ap.loadTrack(ap.playlist[0]);
                ap.currentTrackIndex = 0;
            }
            ap.player.onended = function(){
                ap.playing = false;
                $('#player__play').html('play');
            }
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
                status: $('#info__status'),
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
            getPlaylist:function(trackListIndex){
                var response =false;
                var that = this;
                $.ajax({
                    async: false,
                    url: bdp.model.ajaxUrl,
                    data: {
                        'action':'getTrackList',
                        'index': trackListIndex
                    },
                    dataType:'json',
                    success:function(data, textStatus, request) {
                        if( typeof data.data != 'undefined' && data.data.length > 0) {
                            that.playlist = that.playlist.concat(data.data);
                            response = true;
                        }
                        else{
                            that.disableNextButton(true);
                            response = false;
                        }
                    },
                    error: function(data, textStatus, request){
                        response = false;
                        //something
                    }
                });
                return response;
            },
            loadPreselectedSong:function(trackID){
                var response ='';
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
                        success:function(data, textStatus, request) {
                            if(typeof data != 'undefined' && !$.isEmptyObject(data) ){
                                that.preselectedSong = {
                                    id: trackID,
                                    attributes:{
                                        title:data.title,
                                        duration:data.duration
                                    },
                                    relationships:{
                                        artist:{
                                            name:data.author,
                                            data:{
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
                                }
                                that.loadTrack(that.preselectedSong);
                            }
                        },
                        error: function(data, textStatus, request){
                            console.log(textStatus);
                        }
                    });
                }

            },loadTrack: function (song) {
                this.songLoaded = song;
                var url = song.relationships.audio_files.data[0].audio_file.versions.mp3.url;
                $(this.player).attr('src', url);
                var inf = this.interfaceInfo;
                inf.title.html(song.attributes.title);
                inf.duration.html(song.attributes.duration);
                inf.artist.html(song.relationships.artist.name);
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
                if(  that.currentTrackIndex  >= that.playlist.length){
                   $.when(that.getPlaylist(++that.trackListIndex)).then(function(){
                       if(typeof that.playlist[that.currentTrackIndex] == 'undefined'){
                           audioPlayer.disableNextButton(true);
                           that.trackListIndex--;
                           that.currentTrackIndex--;
                        }
                   });
                }
                if(that.playlist[that.currentTrackIndex].id == that.preselectedSong.id){
                    return that.next();
                }
                else{
                    that.loadTrack(that.playlist[that.currentTrackIndex]);
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
                if(that.playlist[that.currentTrackIndex].id == that.preselectedSong.id && that.currentTrackIndex > 0){
                    return that.prev();
                }
                else{
                    that.loadTrack(that.playlist[that.currentTrackIndex]);
                    (that.playing)? that.play() : null;
                }
            },
        };
        $('#audioControllers').on('click', 'button', function(){
           switch( $(this).attr('id') ){
               case 'player__next':
                   audioPlayer.waitUI(function(){
                       audioPlayer.next();
                       audioPlayer.disablePrevButton(false);
                   });
                   break;
               case 'player__prev':
                   audioPlayer.prev();
                   audioPlayer.disableNextButton(false);
                   break;
               case 'player__play':
                   if(audioPlayer.playing){
                       audioPlayer.pause();
                       $(this).html('play');
                   }
                   else{
                       audioPlayer.play();
                       $(this).html('pause');
                   }
                   break;
           }
        });
    }
});

