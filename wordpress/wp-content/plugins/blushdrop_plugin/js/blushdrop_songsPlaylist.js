// html5media enables <video> and <audio> tags in all major browsers
// External File: http://api.html5media.info/1.1.8/html5media.min.js
/*
* <div id="interface--playlist" class="<?= mdlGrid()?>">
 <ul id="playlist" class="<?= mdlGrid()?>"></ul>
 </div>
 */

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
        $.when(audioPlayer.getPlaylist(1)).then(function(){
            var val = $('#eleSongCode').val();
            if(val){
                audioPlayer.loadPreselectedSong(val);
                audioPlayer.currentTrackIndex = -1;
            }
            else{
                audioPlayer.loadTrack(audioPlayer.playlist[0]);
                audioPlayer.currentTrackIndex = 0;
            }
            audioPlayer.player.onended = function(){
                audioPlayer.playing = false;
                $('#player__play').html('play');
            }
        });
    });
    var supportsAudio = !!document.createElement('audio').canPlayType;
    if (supportsAudio) {
        audioPlayer = {
            currentTrackIndex: null,
            index:1,
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
            playlist: null,
            preselectedSong:null,
            songLoaded: null,
            cleanInterfaceInfo:function(){
                this.interfaceInfo.title.html('');
                this.interfaceInfo.duration.html('00:00');
                this.interfaceInfo.artist.html('');
                this.interfaceInfo.status.html('Loading List...');
                this.interfaceInfo.cover.css('background-image', '');
            },
            getPlaylist:function(index){
                var response ='';
                var that = this;
                $.ajax({
                    async: false,
                    url: bdp.model.ajaxUrl,
                    data: {
                        'action':'getTrackList',
                        'index': index
                    },
                    dataType:'json',
                    success:function(data, textStatus, request) {
                        if(Boolean(data.data != 'undefined' && data.data.length > 0) ){
                            that.playlist = data.data;
                        }
                    },
                    error: function(data, textStatus, request){
                        //something
                    }
                });
            },
//            buildDOM:function(data){
//                 var lis = '';
//                 $.each(data.data, function(index, obj){
//                     var pic = Boolean(obj.relationships.artist.data.pic.url)? obj.relationships.artist.data.pic.url  : 'alternate default url';
//                     var url = Boolean(obj.relationships.audio_files.data[0].audio_file.versions.mp3.url)? obj.relationships.audio_files.data[0].audio_file.versions.mp3.url  : '-';
//                     var title = Boolean(obj.attributes.title)? obj.attributes.title : 'no title';
//                     var artistName = Boolean(obj.relationships.artist.data.name)? obj.relationships.artist.data.name : 'no artist name';
//                     var duration =  Boolean(obj.attributes.duration)? obj.attributes.duration : '-';
//                     var vocals = Boolean(obj.attributes.has_file_with_vocals);
//                     var instrumentals = Boolean(obj.attributes.has_instrumental_file);
//
//                     lis += '<li data-id="' + obj.id + '" data-index="' + index + '" data-title="' + title + '" data-artist="' + artistName + '" data-duration="' + duration + '" data-url="' + url + '" data-cover="' + pic + '"><ul class="mdl-grid mdl-grid--nesting">'
//                            + '<li class="backgroundCover mdl-cell mdl-cell--2-col mdl-cell--2-col-tablet mdl-cell--1-col-phone" style="background-image: url('+ pic +');"></li>'
//                            + '<li class="title_name mdl-cell mdl-cell--5-col mdl-cell--3-col-tablet mdl-cell--3-col-phone"><p class="title">' + title + '</p><p class="artist">' + artistName + '</p></li>'
//                            + '<li class="voc_inst   mdl-cell mdl-cell--3-col mdl-cell--3-col-tablet mdl-cell--1-col-phone">' + (vocals? 'vocals ': '') + (instrumentals? 'instrumentals ': '') + '</li>'
//                            + '<li class="duration   mdl-cell mdl-cell--1-col mdl-cell--4-col-tablet mdl-cell--2-col-phone">' + duration + '</li>'
//                            + '<li class="checkbox   mdl-cell mdl-cell--1-col mdl-cell--4-col-tablet mdl-cell--1-col-phone"><input type="checkbox" id="song_'+ obj.id +'" class="MDLCLASS" data-title="'+ title +'" data-artist="'+ artistName +'" data-id="'+ obj.id +'" ></li>'
//                          + '</ul></li>';
//                 })
//                $('#playlist').data(data.links).html(lis);
//            },
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
                this.currentTrackIndex +=1;
                if(this.currentTrackIndex < this.playlist.length){
                    if(this.preselectedSong != null && this.playlist[this.currentTrackIndex].id == this.preselectedSong.id ){
                        return audioPlayer.next();
                    }
                    this.loadTrack(this.playlist[this.currentTrackIndex]);
                    (this.playing)? this.play() : null;
                }
                else{
                    $.when(this.getPlaylist(++this.index)).then(function(){
                        that.currentTrackIndex = 0;
                        if(this.preselectedSong != null && that.playlist[0].id == this.preselectedSong.id ){
                            return audioPlayer.next();
                        }
                        //TODO check if the conditions on load a new playlist, if the first element is equal to the pre selected song, then, load the next one
                    });
                }
            },
            prev: function () {
                if(this.index == 1){
                    //disable button
                    return
                }
                var prev = $(this.currentTrackIndex).prev();
                if(prev.is('li')){
                    this.loadTrack(prev.get(0));
                    (this.playing)? this.play(prev.data()) : null;
                }
                else{
                    this.cleanInterfaceInfo();
                    this.pause();
                    bdp.musicWidget.start(--this.index);
                }
            },
        }

            //extension = audio.canPlayType('audio/mpeg') ? '.mp3' : audio.canPlayType('audio/ogg') ? '.ogg' : '';
        //loadTrack(index);
        $('#player__next').click(function(){
            audioPlayer.next();
        });
        $('#player__prev').click(function(){
            audioPlayer.prev();
        });
        $('#player__play').click(function(){
            if(audioPlayer.playing){
                audioPlayer.pause();
                $(this).html('play');
            }
            else{
                audioPlayer.play();
                $(this).html('pause');
            }
        });
//        audioPlayer.playlist.on('click', 'li', function (e) {
//
//            var data = $(this).data();
//            if (data.id !== audioPlayer.index && !$(e.target).is(':checkbox')) {
//                audioPlayer.loadTrack(this);
//                audioPlayer.play(data);
//            }
//        });
//        audioPlayer.playlist.on('change', 'input:checkbox', function (e) {
//            audioPlayer.playlist.find('input:checkbox').not(this).prop('checked', false);
//            var $selectedSongName = $('#selectedSongName');
//            var $eleSongCode = $('#eleSongCode');
//            if(this.checked){
//                $selectedSongName.html($(this).data('title'))
//                var data = $(this).data();
//                $eleSongCode.data(data).val($(this).data('id'));
//            }
//            else{
//                var songincart =  $selectedSongName.data('songincart');
//                $selectedSongName.html(Boolean(songincart)? songincart : 'please select a song from the playlist');
//                $('#eleSongCode').val('');
//            }
//        });
    }
});

