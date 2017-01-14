jQuery(document).ready(function ($) {
  $(window).load(function () {
    'use strict';

    /* Audio Players*/
    $('.OutoftheBox.media.audio').each(function () {
      var listtoken = $(this).attr('data-token'),
              extensions = $(this).attr('data-extensions'),
              autoplay = $(this).attr('data-autoplay'),
              jPlayerSelector = '#' + $(this).find('.jp-jplayer').attr('id'),
              cssSelector = '#' + $(this).find('.jp-video').attr('id');
      oftb_playlists[listtoken] = new jPlayerPlaylist({
        jPlayer: jPlayerSelector,
        cssSelectorAncestor: cssSelector
      }, [], {
        playlistOptions: {
          autoPlay: (autoplay === '1' ? true : false)
        },
        swfPath: OutoftheBox_vars.js_url,
        supplied: extensions,
        solution: "html,flash",
        wmode: "window",
        ready: function () {
          var data = {
            action: 'outofthebox-get-playlist',
            lastFolder: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-id'),
            sort: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort'),
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.getplaylist_nonce
          };
          $.ajax({
            type: "POST",
            url: OutoftheBox_vars.ajax_url,
            data: data,
            success: function (result) {
              if (result !== '-1') {
                oftb_playlists[listtoken].setPlaylist(result);
                if (!$(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").hasClass('hideonstart')) {
                  $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").slideDown("slow");
                }

                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").unbind('click');
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").click(function (e) {
                  e.stopPropagation();
                  var href = $(this).attr('href') + '&dl=1',
                          dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html();

                  sendGooglePageView('Download', dataname);

                  // Delay a few milliseconds for Tracking event
                  setTimeout(function () {
                    window.location = href;
                  }, 300);

                  return false;

                });

                switchSong(listtoken);
              }
            },
            dataType: 'json'
          });
        },
        play: function (e) {
          switchSong(listtoken);
          var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html();
          sendGooglePageView('Play Music', dataname);
        }
      });
    });


    /* Video Players*/
    $('.OutoftheBox.media.video').each(function () {
      var listtoken = $(this).attr('data-token'),
              extensions = $(this).attr('data-extensions'),
              autoplay = $(this).attr('data-autoplay'),
              jPlayerSelector = '#' + $(this).find('.jp-jplayer').attr('id'),
              cssSelector = '#' + $(this).find('.jp-video').attr('id');
      oftb_playlists[listtoken] = new jPlayerPlaylist({
        jPlayer: jPlayerSelector,
        cssSelectorAncestor: cssSelector
      }, [], {
        playlistOptions: {
          autoPlay: (autoplay === '1' ? true : false)
        },
        swfPath: OutoftheBox_vars.js_url,
        supplied: extensions,
        solution: "html,flash",
        audioFullScreen: true,
        errorAlerts: false,
        warningAlerts: false,
        size: {
          width: "100%",
          height: "100%"
        },
        sizeFull: {
          width: "90%",
          height: "100%"
        },
        ready: function (e) {
          var data = {
            action: 'outofthebox-get-playlist',
            lastFolder: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-id'),
            sort: $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort'),
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.getplaylist_nonce
          };
          $.ajax({
            type: "POST",
            url: OutoftheBox_vars.ajax_url,
            data: data,
            success: function (result) {
              if (result !== '-1') {
                oftb_playlists[listtoken].setPlaylist(result);

                if (!$(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").hasClass('hideonstart')) {
                  $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist").slideDown("slow");
                }
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").unbind('click');
                $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item-dl").click(function (e) {
                  e.stopPropagation();
                  var href = $(this).attr('href') + '&dl=1',
                          dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html();
                  sendGooglePageView('Download', dataname);

                  // Delay a few milliseconds for Tracking event
                  setTimeout(function () {
                    window.location = href;
                  }, 300);

                  return false;

                });
              }
              switchSong(listtoken);
            },
            dataType: 'json'
          });
          $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").height($(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").width() / 1.6);
          $(".OutoftheBox[data-token='" + listtoken + "'] object").width('100%');
          $(".OutoftheBox[data-token='" + listtoken + "'] object").height($(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").height());
        },
        ended: function (e) {

        },
        pause: function (e) {
          $(".OutoftheBox[data-token='" + listtoken + "'] .jp-video-play").height($(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").height());
        },
        loadedmetadata: function (e) {

          if (e.jPlayer.status.videoHeight !== 0 && e.jPlayer.status.videoWidth !== 0) {
            var ratio = e.jPlayer.status.videoWidth / e.jPlayer.status.videoHeight;
            var videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] object");
            if (e.jPlayer.html.active === true) {
              videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] video");
              videoselector.bind('contextmenu', function () {
                return false;
              });
            }
            if (videoselector.height() === 0 || videoselector.height() !== videoselector.parent().width() / ratio) {
              videoselector.width(videoselector.parent().width());
              videoselector.height(videoselector.width() / ratio);
              $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer").height(videoselector.width() / ratio);
            }
            $(".OutoftheBox[data-token='" + listtoken + "'] .jp-jplayer img").hide();
          }
        },
        waiting: function (e) {
          var videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] object");
          if (e.jPlayer.html.active === true) {
            videoselector = $(".OutoftheBox[data-token='" + listtoken + "'] video");
            videoselector.bind('contextmenu', function () {
              return false;
            });
          }
        },
        resize: function (e) {

        },
        play: function (e) {
          var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .jp-playlist-item.jp-playlist-current  .jp-playlist-item-song-title").html();
          sendGooglePageView('Play Video', dataname);
          switchSong(listtoken);

          $('html, body').animate({
            scrollTop: $(".OutoftheBox[data-token='" + listtoken + "'].media").offset().top
          }, 1500);

        }
      });
    });

    function switchSong(listtoken) {
      var $this = $(".OutoftheBox[data-token='" + listtoken + "'].media");

      $this.find(".jp-previous").removeClass('disabled');
      $this.find(".jp-next").removeClass('disabled');

      if (($this.find('.jp-playlist ul li:last-child')).hasClass('jp-playlist-current')) {
        $this.find(".jp-next").addClass('disabled');
      }

      if (($this.find('.jp-playlist ul li:first-child')).hasClass('jp-playlist-current')) {
        $this.find(".jp-previous").addClass('disabled');
      }

      var $song_title = $this.find('.jp-playlist ul li.jp-playlist-current .jp-playlist-current').html();
      $this.find('.jp-song-title').html($song_title);
    }

    $(".OutoftheBox .jp-playlist-toggle").click(function () {
      var $this = $(this).closest('.media');
      $this.find(".jp-playlist").slideToggle("slow");
    });


  });
});