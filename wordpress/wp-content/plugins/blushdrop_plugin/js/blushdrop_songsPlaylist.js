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
        audioPlayer.getPlaylist(1);

    });
    var supportsAudio = !!document.createElement('audio').canPlayType;
    if (supportsAudio) {
        audioPlayer = {
            index:1,
            links:$('#playlist').data(),
            currentTrackIndex: null,
            player: $('#player__audio').get(0),
            playing:false,
            playlist: null,
            interfaceInfo: {
                title: $('#info__title'),
                duration: $('#info__duration'),
                artist: $('#info__artist'),
                cover: $('#info__cover'),
                status: $('#info__status'),
            },
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
                            that.loadTrack(data.data[0]);
                            that.currentTrackIndex = 0;
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
            loadTrack: function (song) {
                /*
                * 'title'=>$contents->data->attributes->title,
                 'author'=>$contents->data->relationships->artist->data->name,
                 'image'=>$contents->data->relationships->artist->data->pic->url,*/
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
            play: function (data) {
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
                if(this.playlist.length < this.currentTrackIndex){
                    this.loadTrack(++this.currentTrackIndex);
                    (this.playing)? this.play(next.data()) : null;
                }
                else{
                    that.getTracklist(++this.index);
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