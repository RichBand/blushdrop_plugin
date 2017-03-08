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
    var supportsAudio = !!document.createElement('audio').canPlayType;
    if (supportsAudio) {
        audioPlayer = {
            index:1,
            links:$('#playlist').data(),
            loaded: null,
            player: $('#player__audio').get(0),
            playing:false,
            playlist: $('#playlist'),
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
            loadTrack: function (list) {
                this.loaded = list;
                var data = $(list).data();
                $(this.player).attr('src',data.url);
                var inf = this.interfaceInfo;
                inf.title.html(data.title);
                inf.duration.html(data.duration);
                inf.artist.html(data.artist);
                inf.status.html('Loading 1....');
                inf.cover.css('background-image', 'url( ' + data.cover + ')');
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
                var next = $(this.loaded).next();
                if(next.is('li')){
                    this.loadTrack(next.get(0));
                    (this.playing)? this.play(next.data()) : null;
                }
                else{
                    this.cleanInterfaceInfo();
                    this.pause();
                    bdp.musicWidget.start(++this.index);
                }
            },
            prev: function () {
                if(this.index == 1){
                    //disable button
                    return
                }
                var prev = $(this.loaded).prev();
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
        audioPlayer.playlist.on('click', 'li', function (e) {

            var data = $(this).data();
            if (data.id !== audioPlayer.index && !$(e.target).is(':checkbox')) {
                audioPlayer.loadTrack(this);
                audioPlayer.play(data);
            }
        });
        audioPlayer.playlist.on('change', 'input:checkbox', function (e) {
            audioPlayer.playlist.find('input:checkbox').not(this).prop('checked', false);
            var title = '';
            if(this.checked){
                $('#selectedSongName').html($(this).data('title'))
                var data = $(this).data();
                $('#eleSongCode').data(data).val($(this).data('id'));
            }
            else{
                $('#selectedSongName').html('please select a song from the playlist')
                $('#eleSongCode').val('');
            }
        });
    }
});