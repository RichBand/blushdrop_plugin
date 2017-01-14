var _active = false,
        _refreshtimer,
        _updatetimer,
        _resizeTimer = null,
        _thumbTimer = null,
        readArrCheckBoxes,
        oftb_playlists = {},
        _DBcache = {},
        _DBuploads = {},
        mobile = false,
        windowwidth;

var DB_iLightbox = {};

jQuery(document).ready(function ($) {
  $(window).load(function () {
    'use strict';

    if (/Android|webOS|iPhone|iPod|iPad|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      var userAgent = navigator.userAgent.toLowerCase();
      if ((userAgent.search("android") > -1) && (userAgent.search("mobile") > -1)) {
        mobile = true;
      } else if ((userAgent.search("android") > -1) && !(userAgent.search("mobile") > -1)) {
        mobile = false;
      } else {
        mobile = true;
      }
    }

    /* Simple check if browser is Chrome > allow folder uploads */
    var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;

    /* Check if user is using a mobile device (including tables) detected by WordPress, alters css*/
    if (OutoftheBox_vars.is_mobile === '1') {
      $('html').addClass('oftb-mobile');
    }

    $(".OutoftheBox img.preloading").not('.hidden').unveil(200, null, function () {
      $(this).load(function () {
        $(this).removeClass('preloading');
      });
    });

    refreshLists();
    //Refresh lists every 60 minutes
    _refreshtimer = setInterval(refreshLists, 1000 * 60 * 60);

    //Remove no JS message
    $(".OutoftheBox.jsdisabled").removeClass('jsdisabled');

    //Add return to home event to nav-home
    $('.OutoftheBox .nav-home').click(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token'),
              orgpath = $(this).closest(".OutoftheBox").attr('data-org-path'),
              data = {
                listtoken: listtoken
              };
      $(".OutoftheBox[data-qtip-id='search-" + listtoken + "'] .search-input").val('');
      $(this).closest(".OutoftheBox").attr('data-path', orgpath);
      getFileList(data);
    });

    //Add refresh event to nav-refresh
    $('.OutoftheBox .nav-refresh').click(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token'),
              data = {
                listtoken: listtoken
              };
      $(".OutoftheBox[data-qtip-id='search-" + listtoken + "'] .search-input").val('');
      getFileList(data, 'hardrefresh');
    });

    //Add scroll event to nav-upload
    $('.OutoftheBox .nav-upload').click(function () {
      $('.qtip.OutoftheBox').qtip('hide');
      var listtoken = $(this).closest(".gear-menu").attr('data-token'),
              uploadcontainer = $(".OutoftheBox[data-token='" + listtoken + "']").find('.fileupload-container');
      $('html, body').animate({
        scrollTop: uploadcontainer.offset().top
      }, 1500);
      for (var i = 0; i < 3; i++) {
        uploadcontainer.find('.fileupload-buttonbar').fadeTo('slow', 0.5).fadeTo('slow', 1.0);
      }
    });

    /* Add Link to event*/
    $('#OutoftheBox-UserToFolder .oftb-linkbutton').click(function () {
      $('#OutoftheBox-UserToFolder .thickbox_opener').removeClass("thickbox_opener");
      $(this).parent().addClass("thickbox_opener");
      $(this).parent().find('.oftb-unlinkbutton').removeClass("disabled");
      tb_show("(Re) link to folder", '#TB_inline?height=450&amp;width=800&amp;inlineId=oftb-embedded');
    });

    $('#OutoftheBox-UserToFolder .oftb-unlinkbutton').click(function () {
      var curbutton = $(this),
              user_id = curbutton.parent().attr('data-userid');

      $.ajax({type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: {
          action: 'outofthebox-unlinkusertofolder',
          userid: user_id,
          _ajax_nonce: OutoftheBox_vars.createlink_nonce
        },
        beforeSend: function () {
          curbutton.addClass('disabled');
        },
        success: function (response) {
          if (response === '1') {
            curbutton.parent().find('.oftb-linkedto').html(OutoftheBox_vars.str_nolink);
          } else {
            curbutton.removeClass("disabled");
          }
        },
        dataType: 'text'
      });

    });

    /* Delete files event */
    $(".OutoftheBox .selected-files-delete").click(function () {
      var listtoken = $(this).closest(".gear-menu").attr('data-token');
      $('.qtip.OutoftheBox').qtip('hide');

      var entries = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");

      if (entries.length > 0) {
        var dialog_html = $("<div class='dialog' title='" + OutoftheBox_vars.str_delete_title + "'><p>" + OutoftheBox_vars.str_delete_multiple + "</p></div>");
        var l18nButtons = {};
        l18nButtons[OutoftheBox_vars.str_delete_title] = function () {
          var data = {
            action: 'outofthebox-delete-entries',
            entries: entries,
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.delete_nonce
          };
          changeEntry(data);
          $(this).dialog("destroy");
        };
        l18nButtons[OutoftheBox_vars.str_cancel_title] = function () {
          $(this).dialog("destroy");
        };
        dialog_html.dialog({
          dialogClass: 'OutoftheBox',
          resizable: false,
          height: 200,
          width: 400,
          modal: true,
          buttons: l18nButtons
        });
      }
      return false;
    });

    /* Settings menu */
    $('.OutoftheBox .nav-gear').each(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token');

      $(this).qtip({
        prerender: true,
        id: 'nav-' + listtoken,
        content: {
          text: $(this).next('.gear-menu')
        },
        position: {
          my: 'top right',
          at: 'bottom center',
          target: $(this).find('i'),
          viewport: $(window),
          adjust: {
            scroll: false
          }
        },
        style: {
          classes: 'OutoftheBox qtip-light'
        },
        show: {
          event: 'click, mouseenter',
          solo: true
        },
        hide: {
          event: 'mouseleave unfocus',
          fixed: true,
          delay: 200
        },
        events: {
          show: function (event, api) {
            var selectedboxes = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");
            if (($(".OutoftheBox[data-qtip-id='search-" + listtoken + "'] .search-input").length > 0) && $(".OutoftheBox[data-qtip-id='search-" + listtoken + "'] .search-input").val() !== '') {
              api.elements.content.find(".all-files-to-zip").parent().hide();
            } else {
              api.elements.content.find(".all-files-to-zip").parent().show();
            }

            if (selectedboxes.length === 0) {
              api.elements.content.find(".selected-files-to-zip").parent().hide();
              api.elements.content.find(".selected-files-delete").parent().hide();
            } else {
              api.elements.content.find(".selected-files-to-zip").parent().show();
              api.elements.content.find(".selected-files-delete").parent().show();
            }

            var visibleelements = api.elements.content.find('ul > li').not('.gear-menu-no-options').filter(function () {
              return $(this).css('display') !== 'none';
            });

            if (visibleelements.length > 0) {
              api.elements.content.find('.gear-menu-no-options').hide();
            } else {
              api.elements.content.find('.gear-menu-no-options').show();
            }

          }
        }
      });
    });

    // Searchbox
    $('.OutoftheBox .nav-search').each(function () {
      var listtoken = $(this).closest(".OutoftheBox").attr('data-token');

      $(this).qtip({
        prerender: true,
        id: 'search-' + listtoken,
        content: {
          text: $(this).next('.search-div'),
          button: $(this).next('.search-div').find('.search-remove')
        },
        position: {
          my: 'top right',
          at: 'bottom center',
          target: $(this).find('i'),
          viewport: $(window),
          adjust: {
            scroll: false
          }
        },
        style: {
          classes: 'OutoftheBox search qtip-light'
        },
        show: {
          effect: function () {
            $(this).fadeTo(90, 1, function () {
              $('input', this).focus();
            });
          }
        },
        hide: {
          fixed: true,
          delay: 1500
        }
      });
    });

    $('.OutoftheBox .search-input').each(function () {
      $(this).on("keyup", function (event) {
        var listtoken = $(this).closest(".OutoftheBox").attr('data-qtip-id').replace('search-', '');
        clearTimeout(_updatetimer);
        var data = {
          listtoken: listtoken
        };
        _updatetimer = setTimeout(function () {
          getFileList(data);
        }, 1000);
        if ($(this).val().length > 0) {
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").addClass('search');
          $(".OutoftheBox[data-token='" + listtoken + "'] .nav-search").addClass('inuse');
        } else {
          $(".OutoftheBox[data-token='" + listtoken + "'] .nav-search").removeClass('inuse');
        }
      });
    });
    $('.OutoftheBox .search-remove').click(function () {
      if ($(this).parent().find('.search-input').val() !== '') {
        $(this).parent().find('.search-input').val('');
        $(this).parent().find('.search-input').trigger('keyup');
      }
    });

    //Sortable column Names
    $(".OutoftheBox .sortable").click(function () {

      var listtoken = $(this).closest(".OutoftheBox").attr('data-token');

      var newclass = 'asc';
      if ($(this).hasClass('asc')) {
        newclass = 'desc';
      }

      $(".OutoftheBox[data-token='" + listtoken + "'] .sortable").removeClass('asc').removeClass('desc');
      $(this).addClass(newclass);
      var sortstr = $(this).attr('data-sortname') + ':' + newclass;
      $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort', sortstr);

      var data = {
        listtoken: listtoken
      };

      clearTimeout(_updatetimer);
      _updatetimer = setTimeout(function () {
        getFileList(data);
      }, 300);
    });


    //To ZIP
    $('.select-all-files').click(function () {
      $(this).closest(".OutoftheBox").find(".selected-files:checkbox").prop("checked", $(this).prop("checked"));
      if ($(this).prop("checked") === true) {
        $(this).closest(".OutoftheBox").find(".selected-files:checkbox").show();
      } else {
        $(this).closest(".OutoftheBox").find(".selected-files:checkbox").hide();
      }
    });

    $(".OutoftheBox .all-files-to-zip, .OutoftheBox .selected-files-to-zip").click(function (event) {
      var location = OutoftheBox_vars.ajax_url;

      var listtoken = $(this).closest(".gear-menu").attr('data-token'),
              lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path');

      var data = {
        action: 'outofthebox-create-zip',
        listtoken: listtoken,
        lastpath: lastpath,
        _ajax_nonce: OutoftheBox_vars.createzip_nonce
      };
      if ($(event.target).hasClass('selected-files-to-zip')) {
        data.files = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");
      }

      $('.qtip.OutoftheBox').qtip('hide');
      $(this).attr('href', location + "?" + $.param(data));

      return;
    });

    function isCached(identifyer, listtoken) {
      if (typeof _DBcache[listtoken] === 'undefined') {
        _DBcache[listtoken] = {};
      }

      if (typeof _DBcache[listtoken][identifyer] === 'undefined' || $.isEmptyObject(_DBcache[listtoken][identifyer])) {
        return false;
      } else {

        var unixtime = Math.round((new Date()).getTime() / 1000);
        if (_DBcache[listtoken][identifyer].expires < unixtime) {
          _DBcache[listtoken][identifyer] = {};
          return false;
        }
        return _DBcache[listtoken][identifyer];
      }
    }

    function updateDiv(response, identifyer, listtoken) {
      $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 1);

      if (typeof _DBcache[listtoken] === 'undefined') {
        _DBcache[listtoken] = {};
      }

      _DBcache[listtoken][identifyer] = response;

      $(".OutoftheBox[data-token='" + listtoken + "'] .ajax-filelist").html(response.html);
      $(".OutoftheBox[data-token='" + listtoken + "'] .nav-title").html(response.breadcrumb);
      $(".OutoftheBox[data-token='" + listtoken + "'] .current-folder-raw").text(response.rawpath);

      if (response.lastpath !== null) {
        $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path', response.lastpath);
      }

      $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeOut(400);

      updateActions(listtoken);
    }

    function getFileList(data, hardrefresh) {
      if (_refreshtimer) {
        clearInterval(_refreshtimer);
      }

      _refreshtimer = setInterval(refreshLists, 1000 * 60 * 10);

      var listtoken = data.listtoken,
              list = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-list'),
              lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path'),
              sort = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-sort'),
              query = $(".OutoftheBox[data-qtip-id='search-" + listtoken + "'] .search-input").val(),
              ajax_action = 'outofthebox-get-filelist',
              deeplink = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-deeplink'),
              nonce = OutoftheBox_vars.refresh_nonce;

      if (list === 'gallery') {
        ajax_action = 'outofthebox-get-gallery';
        nonce = OutoftheBox_vars.gallery_nonce;
      }

      if (typeof query !== 'undefined' && query.length > 2 && query !== 'Search filenames') {
        data.query = query;
      }

      if (typeof hardrefresh !== 'undefined') {
        _DBcache = [];
      }

      data.deeplink = deeplink;
      data.sort = sort;
      data.action = ajax_action;
      data.mobile = mobile;
      data._ajax_nonce = nonce;

      /* Identifyer for cache */
      var str = JSON.stringify(data);
      var identifyer = str.hashCode();
      var request = false;

      request = isCached(identifyer, listtoken);

      if (request !== false) {
        return updateDiv(request, identifyer, listtoken);
      }

      /* Don't add in the identifyer */
      data.lastpath = lastpath;

      $.ajax({
        type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: data,
        beforeSend: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .no_results").remove();
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").removeClass('initialize upload error');
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 1);
        },
        complete: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").removeClass('search');
        },
        success: function (response) {
          if (response !== null && response !== 0) {
            updateDiv(response, identifyer, listtoken);
          } else {
            $(".OutoftheBox[data-token='" + listtoken + "'] .nav-title").html(OutoftheBox_vars.str_no_filelist);
            $(".OutoftheBox[data-token='" + listtoken + "'] .loading").addClass('error');
            updateActions(listtoken);
          }
        },
        error: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .nav-title").html(OutoftheBox_vars.str_no_filelist);
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").addClass('error');
          updateActions(listtoken);
        },
        dataType: 'json'
      });
    }

    function changeEntry(data) {
      var listtoken = data.listtoken,
              lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path');
      data.lastpath = lastpath;
      $.ajax({type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: data,
        beforeSend: function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 0.8);
        },
        complete: function () {
          var data = {
            listtoken: listtoken
          };
          getFileList(data, 'hardrefresh');
        }, success: function (response) {
          if (typeof response !== 'undefined') {
            if (typeof response.result !== 'undefined' && response.result !== '1') {

              var dialog_html = $("<div class='dialog' title='" + OutoftheBox_vars.str_error_title + "'><p>" + response.msg + "</em></p></div>");
              var l18nButtons = {};
              l18nButtons[OutoftheBox_vars.str_close_title] = function () {
                $(this).dialog("close");
              };
              dialog_html.dialog({
                dialogClass: 'OutoftheBox',
                resizable: false, height: 200,
                width: 400,
                modal: true,
                buttons: l18nButtons
              });
            } else {
              if (response.lastpath !== null) {
                $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path', response.lastpath);
              }
            }
          }
        },
        dataType: 'json'
      });
    }

    function refreshLists() {
      var selector = $('.OutoftheBox.files, .OutoftheBox.gridgallery');
      if (_active) {
        var selector = $('.OutoftheBox.files');
      }

      //Create file lists
      selector.each(function () {

        var data = {
          OutoftheBoxpath: $(this).attr('data-path'),
          listtoken: $(this).attr('data-token')
        };
        getFileList(data);
      });
      _active = true;
    }

    window.updateCollage = function updateCollage(listtoken) {
      var selector = $(".OutoftheBox.gridgallery[data-token='" + listtoken + "']");

      //Set Image container explicit
      var padding = parseInt($(selector).find(".image-collage").css('padding-left')) + parseInt($(selector).find(".image-collage").css('padding-right'));
      var containerwidth = $(selector).width() - padding - 1;
      $(selector).find(".image-collage").outerWidth(containerwidth);

      var targetheight = $(selector).attr('data-targetheight');
      var allowpartiallastrow = ($(selector).attr('data-lastrow') === 'true');

      $(selector).find('.image-collage').removeWhitespace().collagePlus({
        'targetHeight': targetheight,
        'fadeSpeed': "slow",
        'allowPartialLastRow': true
      });

      $(selector).find(".image-container.hidden").fadeOut(0);
      $(selector).find(".image-collage").fadeTo(1500, 1);

      $(selector).find(".image-container").each(function () {
        $(this).find(".folder-thumb").width($(this).width()).height($(this).height());
      });

      $(selector).find('.image-folder-img').delay(1000).animate({opacity: 0}, 1500);
      if (_thumbTimer) {
        clearInterval(_thumbTimer);
      }

      updateImageFolders();
      _thumbTimer = setInterval(updateImageFolders, 15000);

    };

    function updateImageFolders() {
      $(".OutoftheBox.gridgallery .image-folder").each(function () {
        $(this).find('.folder-thumb').fadeIn(1500);
        var delay = Math.floor(Math.random() * 3000) + 1500;
        $(this).find(".thumb3").delay(delay).fadeOut(1500);
        $(this).find(".thumb2").delay(delay + 1500).delay(delay).fadeOut(1500);
        $(this).find(".thumb3").delay(2 * (delay + 1500)).delay(delay).fadeIn(1500);
      });
    }
    function updateActions(listtoken) {

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").unbind('hover');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").hover(
              function () {
                $(this).addClass('hasfocus');
              },
              function () {
                $(this).removeClass('hasfocus');
              }
      );

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").on('mouseover', function () {
        $(this).addClass('hasfocus');
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry").click(function () {
        $(this).find('.entry_checkbox input[type="checkbox"]').trigger('click');
      });


      /* Edit menu popup */
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry .entry_edit_menu").each(function () {
        $(this).click(function (e) {
          e.stopPropagation();
        });

        $(this).qtip({
          content: {
            text: $(this).next('.oftb-dropdown-menu')
          },
          position: {
            my: 'top center',
            at: 'bottom center',
            target: $(this),
            scroll: false,
            viewport: $(".OutoftheBox[data-token='" + listtoken + "']")
          },
          show: {
            event: 'click',
            solo: true
          },
          hide: {
            event: 'mouseleave unfocus',
            delay: 200,
            fixed: true
          },
          events: {
            show: function (event, api) {
              api.elements.target.closest('.entry').addClass('hasfocus').addClass('popupopen');
            },
            hide: function (event, api) {
              api.elements.target.closest('.entry').removeClass('hasfocus').removeClass('popupopen');
            }
          },
          style: {
            classes: 'OutoftheBox qtip-light'
          }
        });
      });


      /* Load more images */
      var loadmoreimages = function () {
        // the element should probably be expected to be off-screen (beneath the visible viewport) when domready fires, but this can be tested using similar logic
        var element = $(".OutoftheBox[data-token='" + listtoken + "'] .image-container.entry:not(.hidden):last()");
        // is the element at least 10% visible along both axes?
        var visible = element.isOnScreen(0.1, 0.1);
        if (visible) {
          var loadimages = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-loadimages'),
                  images = $(".OutoftheBox[data-token='" + listtoken + "'] .image-container:hidden:lt(" + loadimages + ")");

          if (images.length > 0) {
            images.each(function () {
              $(this).fadeIn(500);
              $(this).removeClass('hidden');
              $(this).find('img').removeClass('hidden');
            });

            $(".OutoftheBox[data-token='" + listtoken + "'] img.preloading").not('.hidden').unveil(200, null, function () {
              $(this).load(function () {
                $(this).removeClass('preloading');
              });
            });
          } else {
            // tidy up
            $(window).off('scroll', debounced);
          }

        }
      };
      /* wrap it in the functor so that it's only called every 50 ms */
      var debounced = $.noop;
      debounced = loadmoreimages.debounce(50);
      $(window).on('scroll', debounced);
      $(window).trigger('scroll');

      /* Drag and Drop folders and files */
      if ($('#OutoftheBox .entry.moveable').length > 0) {
        $('#OutoftheBox .entry').not('.parentfolder').draggable({
          revert: "invalid",
          stack: "#OutoftheBox .entry",
          cursor: 'move',
          containment: 'parent',
          distance: 50,
          delay: 50,
          start: function (event, ui) {
            $(this).addClass('isdragged');
            $(this).css('transform', 'scale(0.8)');
          },
          stop: function (event, ui) {
            setTimeout(function () {
              $(this).removeClass('isdragged');
            }, 300);
            $(this).css('transform', 'scale(1)');
          }
        });

        $('#OutoftheBox .entry.folder').droppable({
          accept: $('#OutoftheBox .entry'),
          activeClass: "ui-state-hover",
          hoverClass: "ui-state-active",
          drop: function (event, ui) {
            var listtoken = ui.draggable.closest('.OutoftheBox').attr('data-token');
            $(ui.draggable).fadeOut(500);

            var data = {
              action: 'outofthebox-move-entry',
              OutoftheBoxpath: ui.draggable.attr('data-url'),
              copy: false,
              target: $(this).attr('data-url'),
              listtoken: listtoken,
              _ajax_nonce: OutoftheBox_vars.move_nonce
            };
            changeEntry(data);
          }
        });
      }

      $(".OutoftheBox[data-token='" + listtoken + "'] .folder, .OutoftheBox[data-token='" + listtoken + "'] .image-folder").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .folder, .OutoftheBox[data-token='" + listtoken + "'] .image-folder").click(function (e) {

        if ($(this).hasClass('isdragged')) {
          return false;
        }
        $(".OutoftheBox[data-qtip-id='search-" + listtoken + "'] .search-input").val('');
        var data = {
          OutoftheBoxpath: $(this).closest('.folder, .image-folder').attr('data-url'),
          listtoken: listtoken
        };
        getFileList(data);
        e.stopPropagation();
      });

      /* Use timeout to load images in viewport correctly */
      setTimeout(function () {

        $(".OutoftheBox[data-token='" + listtoken + "'] img.preloading").not('.hidden').unveil(200, $(window), function () {
          $(this).load(function () {
            $(this).removeClass('preloading');
          });
        });

        $(".OutoftheBox[data-token='" + listtoken + "'] img.preloading").not('.hidden').unveil(200, $(".OutoftheBox .ajax-filelist"), function () {
          $(this).load(function () {
            $(this).removeClass('preloading');
          });
        });

        setTimeout(function () {
          $(".OutoftheBox[data-token='" + listtoken + "'] .image-collage").fadeTo(0, 0);
          updateCollage(listtoken);
        }, 200);
      }, 500);

      $(".OutoftheBox[data-token='" + listtoken + "'] .image-container .image-rollover").css("opacity", "0");
      $(".OutoftheBox[data-token='" + listtoken + "'] .image-container").hover(
              function () {
                $(this).find('.image-rollover, .image-folder-img').stop().animate({opacity: 1}, 400);
              },
              function () {
                $(this).find('.image-rollover, .image-folder-img').stop().animate({opacity: 0}, 400);
              });

      var groupsArr = [];

      if (typeof DB_iLightbox[listtoken] === 'undefined') {
        DB_iLightbox[listtoken] = {};
      } else if (!$.isEmptyObject(DB_iLightbox[listtoken])) {
        DB_iLightbox[listtoken].destroy();
      }

      $('.OutoftheBox[data-token="' + listtoken + '"] .ilightbox-group[rel^="ilightbox["]').each(function () {
        var group = this.getAttribute("rel");
        $.inArray(group, groupsArr) === -1 && groupsArr.push(group);
      });
      $.each(groupsArr, function (i, groupName) {
        var selector = $('.OutoftheBox[data-token="' + listtoken + '"]');

        DB_iLightbox[listtoken] = $('.OutoftheBox[data-token="' + listtoken + '"] .ilightbox-group[rel="' + groupName + '"]').iLightBox({
          skin: OutoftheBox_vars.lightbox_skin,
          path: OutoftheBox_vars.lightbox_path,
          maxScale: 1,
          slideshow: {
            pauseOnHover: true,
            pauseTime: selector.attr('data-pausetime'),
            startPaused: ((selector.attr('data-list') === 'gallery') && (selector.attr('data-slideshow') === '1')) ? false : true
          },
          controls: {
            slideshow: (selector.attr('data-list') === 'gallery') ? true : false,
            arrows: (selector.attr('data-list') === 'gallery') ? false : true
          },
          keepAspectRatio: true,
          callback: {
            onBeforeLoad: function (api, position) {
              $('.ilightbox-holder').addClass('OutoftheBox');
              $('.ilightbox-holder').find('iframe').addClass('oftb-embedded');
              iframeFix();
            },
            onShow: function (api) {
              if (api.currentElement.find('.empty_iframe').length === 0) {
                api.currentElement.find('.oftb-embedded').after(OutoftheBox_vars.str_iframe_loggedin);
              }

              /* Bugfix for PDF files that open very narrow */
              if (api.currentElement.find('iframe').length > 0) {
                setTimeout(function () {
                  api.currentElement.find('.oftb-embedded').width(api.currentElement.find('.ilightbox-container').width() - 1);
                }, 500);
              }

              api.currentElement.find('.empty_iframe').hide();
              if (api.currentElement.find('img').length !== 0) {
                setTimeout(function () {
                  api.currentElement.find('.empty_iframe').fadeIn();
                }, 5000);
              }
              $('.OutoftheBox .ilightbox-container img').on("contextmenu", function (e) {
                return false;
              });
            }
          },
          errors: {
            loadImage: OutoftheBox_vars.str_imgError_title,
            loadContents: OutoftheBox_vars.str_xhrError_title
          },
          text: {
            next: OutoftheBox_vars.str_next_title,
            previous: OutoftheBox_vars.str_previous_title,
            slideShow: OutoftheBox_vars.str_startslideshow
          }
        });
      });

      /* Disable right clicks */
      $('#OutoftheBox .entry').on("contextmenu", function (e) {
        return false;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_checkbox").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_checkbox").click(function (e) {
        e.stopPropagation();
        return true;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_checkbox :checkbox").click(function (e) {
        if ($(this).prop('checked')) {
          $(this).closest('.entry').addClass('isselected');
        } else {
          $(this).closest('.entry').removeClass('isselected');
        }
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_linkto").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_linkto").click(function (e) {

        var folder_path = $(this).parent().attr('data-url'),
                user_id = $('#OutoftheBox-UserToFolder .thickbox_opener').attr('data-userid');

        $.ajax({type: "POST",
          url: OutoftheBox_vars.ajax_url,
          data: {
            action: 'outofthebox-linkusertofolder',
            id: folder_path,
            userid: user_id,
            _ajax_nonce: OutoftheBox_vars.createlink_nonce
          },
          beforeSend: function () {
            $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 1);
          },
          complete: function () {
            $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeOut(400);
            tb_remove();
          },
          success: function (response) {
            if (response === '1') {
              $('#OutoftheBox-UserToFolder .thickbox_opener .oftb-linkedto').html(decodeURIComponent(folder_path));
              $('#OutoftheBox-UserToFolder .thickbox_opener').removeClass("thickbox_opener");
              $('#OutoftheBox-UserToFolder .thickbox_opener .oftb-unlinkbutton').removeClass("disabled");
            }
          },
          dataType: 'text'
        });

        e.stopPropagation();
        return true;
      });


      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_view").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_view").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');
        var datapath = $(this).closest("ul").attr('data-path');
        var link = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").find(".entry_link")[0].click();
      });


      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_download").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_download").click(function (e) {
        e.stopPropagation();

        var href = $(this).attr('href'),
                dataname = $(this).attr('data-filename');

        sendGooglePageView('Download', dataname);

        // Delay a few milliseconds for Tracking event
        setTimeout(function () {
          window.location = href;
        }, 300);

        return false;

      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_shortlink").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_shortlink").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');

        var datapath = $(this).closest("ul").attr('data-path');
        var dataurl = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-url');
        var dialog_html = $("<div class='dialog' title='" + OutoftheBox_vars.str_share_link + "'><input type='text' class='shared-link-url' value='" + OutoftheBox_vars.str_create_shared_link + "' style='width: 98%;' readonly/></div>");

        var l18nButtons = {};
        l18nButtons[OutoftheBox_vars.str_close_title] = function () {
          $(this).dialog("destroy");
        };

        dialog_html.dialog({
          dialogClass: 'OutoftheBox',
          resizable: false,
          height: 150,
          width: 400,
          modal: true,
          buttons: l18nButtons,
          open: function (event, ui) {

            $.ajax({
              type: "POST", url: OutoftheBox_vars.ajax_url,
              data: {
                action: 'outofthebox-create-link',
                listtoken: listtoken,
                OutoftheBoxpath: dataurl,
                _ajax_nonce: OutoftheBox_vars.createlink_nonce
              },
              beforeSend: function () {
                $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeTo(400, 0.8);
              },
              complete: function () {
                $(".OutoftheBox[data-token='" + listtoken + "'] .loading").fadeOut(400);
              },
              success: function (response) {
                if (response !== null) {
                  if (response.link !== null) {
                    $(dialog_html).find('.shared-link-url').val(response.link);
                    sendGooglePageView('Create shared link');
                  } else {
                    $(dialog_html).find('.shared-link-url').val(response.error);
                  }
                }
              },
              dataType: 'json'
            });
          }
        });
        return false;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_delete").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_delete").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');

        var datapath = $(this).closest("ul").attr('data-path');
        var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-name');
        var dataurl = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-url');
        var dialog_html = $("<div class='dialog' title='" + OutoftheBox_vars.str_delete_title + "'><p>" + OutoftheBox_vars.str_delete + ' <em>' + dataname + "</em></p></div>");
        var l18nButtons = {};
        l18nButtons[OutoftheBox_vars.str_delete_title] = function () {
          var data = {
            action: 'outofthebox-delete-entry',
            OutoftheBoxpath: dataurl,
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.delete_nonce
          };
          changeEntry(data);
          $(this).dialog("destroy");
        };
        l18nButtons[OutoftheBox_vars.str_cancel_title] = function () {
          $(this).dialog("destroy");
        };
        dialog_html.dialog({
          dialogClass: 'OutoftheBox',
          resizable: false,
          height: 200,
          width: 400,
          modal: true,
          buttons: l18nButtons
        });
        return false;
      });

      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_rename").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .entry_action_rename").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');

        var datapath = $(this).closest("ul").attr('data-path');
        var dataname = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-name');
        var dataurl = $(".OutoftheBox[data-token='" + listtoken + "'] .entry[data-url='" + datapath + "']").attr('data-url');
        var dialog_html = $("<div class='dialog' title='" + OutoftheBox_vars.str_rename_title + "'><p>" + OutoftheBox_vars.str_rename +
                '<input type="text" name="newname" id="newname" value="' + dataname + '" class="text ui-widget-content ui-corner-all" style=" width: 98%; "/></p></div>');
        var l18nButtons = {};
        l18nButtons[OutoftheBox_vars.str_rename_title] = function () {
          var data = {
            action: 'outofthebox-rename-entry',
            OutoftheBoxpath: dataurl,
            newname: encodeURIComponent($('#newname').val()),
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.rename_nonce
          };
          changeEntry(data);
          $(this).dialog("destroy");
        };
        l18nButtons[OutoftheBox_vars.str_cancel_title] = function () {
          $(this).dialog("destroy");
        };
        dialog_html.dialog({
          dialogClass: 'OutoftheBox',
          resizable: false,
          height: 200,
          width: 400,
          modal: true,
          buttons: l18nButtons});
        return false;
      });
      $(".OutoftheBox[data-token='" + listtoken + "'] .newfolder").unbind('click');
      $(".OutoftheBox[data-token='" + listtoken + "'] .newfolder").click(function () {
        $('.qtip.OutoftheBox').qtip('hide');
        var dialog_html = $("<div class='dialog' title='" + OutoftheBox_vars.str_addfolder_title + "'><p>" +
                '<input type="text" name="newfolder" id="newfolder" value="' + OutoftheBox_vars.str_addfolder + '" class="text ui-widget-content ui-corner-all" style=" width: 90%; "/></p></div>');
        var l18nButtons = {};
        l18nButtons[OutoftheBox_vars.str_addfolder_title] = function () {
          var data = {
            action: 'outofthebox-add-folder',
            newfolder: encodeURIComponent($('#newfolder').val()),
            listtoken: listtoken,
            _ajax_nonce: OutoftheBox_vars.addfolder_nonce
          };
          changeEntry(data);
          $(this).dialog("destroy");
        };
        l18nButtons[OutoftheBox_vars.str_cancel_title] = function () {
          $(this).dialog("destroy");
        };
        dialog_html.dialog({
          dialogClass: 'OutoftheBox',
          resizable: false,
          height: 200,
          width: 400,
          modal: true,
          buttons: l18nButtons
        });
        return false;
      });
    }

    /* Remove Folder upload button if isn't supported by browser */
    if (!is_chrome) {
      $('.upload-multiple-files').parent().remove();
    }

    // Initialize the jQuery File Upload widget:
    $('.OutoftheBox .fileuploadform').each(function () {
      $(this).fileupload({
        url: OutoftheBox_vars.ajax_url,
        type: 'POST',
        dataType: 'json',
        autoUpload: true,
        maxFileSize: OutoftheBox_vars.post_max_size,
        acceptFileTypes: new RegExp($(this).find('input[name="acceptfiletypes"]').val(), "i"),
        dropZone: $(this).closest('.OutoftheBox'),
        messages: {
          maxNumberOfFiles: OutoftheBox_vars.maxNumberOfFiles,
          acceptFileTypes: OutoftheBox_vars.acceptFileTypes,
          maxFileSize: OutoftheBox_vars.maxFileSize,
          minFileSize: OutoftheBox_vars.minFileSize
        },
        limitConcurrentUploads: 3,
        disableImageLoad: true,
        disableImageResize: true,
        disableImagePreview: true,
        disableAudioPreview: true,
        disableVideoPreview: true,
        uploadTemplateId: null,
        downloadTemplateId: null, add: function (e, data) {
          var listtoken = $(this).attr('data-token');

          $.each(data.files, function (index, file) {
            file.hash = file.name.hashCode() + '_' + Math.floor(Math.random() * 1000000);
            file.listtoken = listtoken;
            file = validateFile(file);
            var row = renderFileUploadRow(file);

            if (file.error !== false) {
              data.files.splice(index, 1);
            }
          });

          if (data.autoUpload || (data.autoUpload !== false &&
                  $(this).fileupload('option', 'autoUpload'))) {
            if (data.files.length > 0) {
              data.process().done(function () {
                data.submit();
              });
            }
          }
        }
      }).on('fileuploadsubmit', function (e, data) {
        var datatoken = $(this).attr('data-token');
        $(".OutoftheBox[data-token='" + datatoken + "'] .loading").addClass('upload');
        $(".OutoftheBox[data-token='" + datatoken + "'] .loading").fadeTo(400, 1);

        var filehash;
        $.each(data.files, function (index, file) {
          uploadStart(file);
          filehash = file.hash;
        });

        $('.gform_button:submit').prop("disabled", false).fadeTo(400, 0.3);

        data.formData = {
          action: 'outofthebox-upload-file',
          type: 'do-upload',
          hash: filehash,
          lastpath: $(".OutoftheBox[data-token='" + datatoken + "']").attr('data-path'),
          listtoken: datatoken,
          _ajax_nonce: OutoftheBox_vars.upload_nonce
        };

      }).on('fileuploadprogress', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10) / 2;

        $.each(data.files, function (index, file) {
          uploadProgress(file, {percentage: progress, progress: 'uploading_to_server'});

          if (progress >= 50) {
            uploadProgress(file, {percentage: 50, progress: 'uploading_to_cloud'});

            setTimeout(function () {
              getProgress(file);
            }, 2000);
          }
        });

      }).on('fileuploadstopped', function () {
        $('.gform_button:submit').prop("disabled", false).fadeTo(400, 1);
      }).on('fileuploaddone', function (e, data) {
        sendGooglePageView('Upload file');
      }).on('fileuploadalways', function (e, data) {

        if (data.result !== null) {
          if (typeof data.result.status !== 'undefined') {
            if (data.result.status.progress === 'finished' || data.result.status.progress === 'failed') {
              uploadFinished(data.result.file);
            }
          } else {
            data.result.file.error = OutoftheBox_vars.str_error;
            uploadFinished(data.result.file);
          }
        } else {
          $.each(data.files, function (index, file) {
            file.error = OutoftheBox_vars.str_error;
            uploadFinished(file);
          });
        }
      }).on('fileuploaddrop', function (e, data) {
        var uploadcontainer = $(this);
        $('html, body').animate({
          scrollTop: uploadcontainer.offset().top
        }, 1500);
      });
    });

    /* ***** Helper functions for File Upload ***** */
    /* Validate File for Upload */
    function validateFile(file) {

      var maxFileSize = $(".OutoftheBox[data-token='" + file.listtoken + "']").find('input[name="maxfilesize"]').val(),
              acceptFileType = new RegExp($(".OutoftheBox[data-token='" + file.listtoken + "']").find('input[name="acceptfiletypes"]').val(), "i");

      file.error = false;
      if (file.name.length && !acceptFileType.test(file.name)) {
        file.error = OutoftheBox_vars.acceptFileTypes;
      }
      if (maxFileSize !== '' && file.size > 0 && file.size > maxFileSize) {
        file.error = OutoftheBox_vars.maxFileSize;
      }
      return file;
    }

    /* Get Progress for uploading files to cloud*/
    function getProgress(file) {

      $.ajax({type: "POST",
        url: OutoftheBox_vars.ajax_url,
        data: {
          action: 'outofthebox-upload-file',
          type: 'get-status',
          listtoken: file.listtoken,
          hash: file.hash,
          _ajax_nonce: OutoftheBox_vars.upload_nonce
        },
        success: function (response) {
          if (response !== null) {
            if (typeof response.status !== 'undefined') {
              if (response.status.progress === 'starting' || response.status.progress === 'uploading') {
                setTimeout(function () {
                  getProgress(response.file);
                }, 1500);
              }
              uploadProgress(response.file, {percentage: 50 + (response.status.percentage / 2), progress: response.status.progress});
            } else {
              file.error = OutoftheBox_vars.str_error;
              uploadFinished(file);
            }
          }
        },
        error: function (response) {
          file.error = OutoftheBox_vars.str_error;
          uploadFinished(file);
        },
        complete: function (response) {

        },
        dataType: 'json'
      });
    }

    /* Render file in upload list */
    function renderFileUploadRow(file) {
      var row = ($(".OutoftheBox[data-token='" + file.listtoken + "']").find('.template-row').clone().removeClass('template-row'));

      row.attr('data-file', file.name).attr('data-id', file.hash);
      row.find('.file-name').text(file.name);
      if (file.size !== 'undefined' && file.size > 0) {
        row.find('.file-size').text(humanFileSize(file.size, true));
      }
      row.find('.upload-thumbnail img').attr('src', getThumbnail(file));

      row.addClass('template-upload');
      row.find('.upload-status').removeClass().addClass('upload-status queue').text(OutoftheBox_vars.str_inqueue);
      row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-circle');

      $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list .files").append(row);

      $('.OutoftheBox .fileuploadform[data-token="' + file.listtoken + '"] div.fileupload-drag-drop').fadeOut();

      if (typeof file.error !== 'undefined' && file.error !== false) {
        uploadFinished(file);
      }

      return row;
    }

    function uploadStart(file) {
      var row = $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list [data-id='" + file.hash + "']");
      row.find('.upload-status').removeClass().addClass('upload-status succes').text(OutoftheBox_vars.str_uploading_local);
      row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-circle-o-notch fa-spin');
      row.find('.upload-progress').slideDown();
      $('input[type="submit"]').prop('disabled', true);
    }

    /* Render the progress of uploading cloud files */
    function uploadProgress(file, status) {
      var row = $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list [data-id='" + file.hash + "']");

      row.find('.progress')
              .attr('aria-valuenow', status.percentage)
              .children().first().fadeIn()
              .animate({
                width: status.percentage + '%'
              }, 'slow', function () {
                if (status.progress === 'uploading_to_cloud') {
                  row.find('.upload-status').text(OutoftheBox_vars.str_uploading_cloud);
                }
              });

      if (status.progress === 'finished' || status.progress === 'failed') {
        uploadFinished(file);
      }
    }

    function uploadFinished(file) {
      var row = $(".OutoftheBox[data-token='" + file.listtoken + "'] .fileupload-list [data-id='" + file.hash + "']");

      row.addClass('template-download').removeClass('template-upload');
      row.find('.file-name').text(file.name);
      row.find('.upload-thumbnail img').attr('src', getThumbnail(file));
      row.find('.upload-progress').slideUp();

      if (typeof file.error !== 'undefined' && file.error !== false) {
        row.find('.upload-status').removeClass().addClass('upload-status error').text(OutoftheBox_vars.str_error);
        row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-exclamation-circle');
        row.find('.upload-error').text(file.error).slideUp().delay(500).slideDown();
      } else {
        row.find('.upload-status').removeClass().addClass('upload-status succes').text(OutoftheBox_vars.str_success);
        row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-check-circle');

        if (typeof _DBuploads[file.listtoken] === 'undefined') {
          _DBuploads[file.listtoken] = {};
        }

        _DBuploads[file.listtoken][file.fileid] = {
          "name": file.name,
          "path": file.completepath,
          "size": file.filesize,
          "link": file.link
        };

        $('.OutoftheBox .fileuploadform[data-token="' + file.listtoken + '"] .fileupload-filelist').val(JSON.stringify(_DBuploads[file.listtoken]));
      }

      row.delay(5000).animate({"opacity": "0"}, "slow", function () {
        if ($(this).parent().find('.template-upload, .template-download').length <= 1) {
          $(this).closest('.fileuploadform').find('div.fileupload-drag-drop').fadeIn();

          /* Update Filelist */
          clearTimeout(_refreshtimer);
          var formData = {
            listtoken: file.listtoken
          };

          _DBcache = [];
          getFileList(formData);

        }

        $(this).remove();
      });

      $('input[type="submit"]').prop('disabled', false);
    }

    /* Get thumbnail for local and cloud files */
    function getThumbnail(file) {

      var thumbnailUrl = OutoftheBox_vars.plugin_url + '/css/icons/128x128/';
      if (typeof file.thumbnail === 'undefined' || file.thumbnail === null || file.thumbnail === '') {
        var icon;

        if (typeof file.type === 'undefined' || file.type === null) {
          icon = 'unknown';
        } else if (file.type.indexOf("word") >= 0) {
          icon = 'application-msword';
        } else if (file.type.indexOf("excel") >= 0 || file.type.indexOf("spreadsheet") >= 0) {
          icon = 'application-vnd.ms-excel';
        } else if (file.type.indexOf("powerpoint") >= 0 || file.type.indexOf("presentation") >= 0) {
          icon = 'application-vnd.ms-powerpoint';
        } else if (file.type.indexOf("access") >= 0 || file.type.indexOf("mdb") >= 0) {
          icon = 'application-vnd.ms-access';
        } else if (file.type.indexOf("image") >= 0) {
          icon = 'image-x-generic';
        } else if (file.type.indexOf("audio") >= 0) {
          icon = 'audio-x-generic';
        } else if (file.type.indexOf("video") >= 0) {
          icon = 'video-x-generic';
        } else if (file.type.indexOf("pdf") >= 0) {
          icon = 'application-pdf';
        } else if (file.type.indexOf("zip") >= 0 ||
                file.type.indexOf("archive") >= 0 ||
                file.type.indexOf("tar") >= 0 ||
                file.type.indexOf("compressed") >= 0
                ) {
          icon = 'application-zip';
        } else if (file.type.indexOf("html") >= 0) {
          icon = 'text-xml';
        } else if (file.type.indexOf("application/exe") >= 0 ||
                file.type.indexOf("application/x-msdownload") >= 0 ||
                file.type.indexOf("application/x-exe") >= 0 ||
                file.type.indexOf("application/x-winexe") >= 0 ||
                file.type.indexOf("application/msdos-windows") >= 0 ||
                file.type.indexOf("application/x-executable") >= 0
                ) {
          icon = 'application-x-executable';
        } else if (file.type.indexOf("text") >= 0) {
          icon = 'text-x-generic';
        } else {
          icon = 'unknown';
        }
        return thumbnailUrl + icon + '.png';
      } else {
        return file.thumbnail;
      }

    }

    /* drag and drop functionality*/
    $(document).bind('dragover', function (e) {
      var dropZone = $('.OutoftheBox .fileuploadform').closest('.OutoftheBox'),
              timeout = window.dropZoneTimeout;
      if (!timeout) {
        dropZone.addClass('in');
      } else {
        clearTimeout(timeout);
      }
      var found = false, node = e.target;
      do {
        if ($(node).is(dropZone)) {
          found = true;
          break;
        }
        node = node.parentNode;
      } while (node !== null);
      if (found) {
        $(node).addClass('hover');
      } else {
        dropZone.removeClass('hover');
      }
      window.dropZoneTimeout = setTimeout(function () {
        window.dropZoneTimeout = null;
        dropZone.removeClass('in hover');
      }, 100);
    });
    $(document).bind('drop dragover', function (e) {
      e.preventDefault();
    });

    // Resize handlers
    windowwidth = $(window).width();
    $(window).resize(function () {

      if (windowwidth === $(window).width()) {
        windowwidth = $(window).width();
        return;
      }
      windowwidth = $(window).width();

      $('.OutoftheBox.media.video .jp-jplayer').each(function () {
        var status = ($(this).data().jPlayer.status);
        if (status.videoHeight !== 0 && status.videoWidth !== 0) {
          var ratio = status.videoWidth / status.videoHeight;
          var jpvideo = $(this);
          if ($(this).find('object').length > 0) {
            var jpobject = $(this).find('object');
          } else {
            var jpobject = $(this).find('video');
          }

          if (jpvideo.height() !== jpvideo.width() / ratio) {
            if ((screen.height >= (jpvideo.width() / ratio)) || (status.cssClass !== "jp-video-full")) {
              jpobject.height(jpobject.width() / ratio);
              jpvideo.height(jpobject.width() / ratio);
            } else {
              jpobject.width(screen.height * ratio);
              jpvideo.width(screen.height * ratio);
            }
          }
          $(this).parent().find(".jp-video-play").height(jpvideo.height());

        }

      });

      // set a timer to re-apply the plugin
      if (_resizeTimer) {
        clearTimeout(_resizeTimer);
      }

      $(".OutoftheBox .image-collage").fadeTo(100, 0);

      _resizeTimer = setTimeout(function () {
        $(".OutoftheBox .image-collage").each(function () {
          var listtoken = $(this).closest('.OutoftheBox').attr('data-token');
          updateCollage(listtoken);
        });
      }, 500);
    });

    var downloadURL = function downloadURL(url) {
      var hiddenIFrameID = 'hiddenDownloader',
              iframe = document.getElementById(hiddenIFrameID);
      if (iframe === null) {
        iframe = document.createElement('iframe');
        iframe.id = hiddenIFrameID;
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
      }
      iframe.src = url;
    };

    readArrCheckBoxes = function (element) {
      var values = $(element + ":checked").map(function () {
        return this.value;
      }).get();

      return values;
    };

    iframeFix();

  });

  function iframeFix() {
    /* Safari bug fix for embedded iframes*/
    if (/iPhone|iPod|iPad/.test(navigator.userAgent)) {
      $('iframe.oftb-embedded').each(function () {
        if ($(this).closest('#safari_fix').length === 0) {
          $(this).wrap(function () {
            return $('<div id="safari_fix"/>').css({
              'width': "100%",
              'height': "100%",
              'overflow': 'auto',
              'z-index': '2',
              '-webkit-overflow-scrolling': 'touch'
            });
          });
        }
      });
    }
  }

  $.fn.isOnScreen = function (x, y) {

    if (x == null || typeof x == 'undefined')
      x = 1;
    if (y == null || typeof y == 'undefined')
      y = 1;

    var win = $(window);

    var viewport = {
      top: win.scrollTop(),
      left: win.scrollLeft()
    };
    viewport.right = viewport.left + win.width();
    viewport.bottom = viewport.top + win.height();

    var height = this.outerHeight();
    var width = this.outerWidth();

    if (!width || !height) {
      return false;
    }

    var bounds = this.offset();
    bounds.right = bounds.left + width;
    bounds.bottom = bounds.top + height;

    var visible = (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));

    if (!visible) {
      return false;
    }

    var deltas = {
      top: Math.min(1, (bounds.bottom - viewport.top) / height),
      bottom: Math.min(1, (viewport.bottom - bounds.top) / height),
      left: Math.min(1, (bounds.right - viewport.left) / width),
      right: Math.min(1, (viewport.right - bounds.left) / width)
    };

    return (deltas.left * deltas.right) >= x && (deltas.top * deltas.bottom) >= y;

  };

});


function sendGooglePageView(action, value) {
  if (OutoftheBox_vars.google_analytics === "1") {
    if (typeof ga !== "undefined" && ga !== null) {
      ga('send', 'event', 'Out-of-the-Box', action, value);
    } else if (typeof _gaq !== "undefined" && _gaq !== null) {
      _gaq.push(['_trackEvent', 'Out-of-the-Box', action, value]);
    }
  }
}
/* Helper functions */
function humanFileSize(bytes, si) {
  var thresh = si ? 1000 : 1024;
  if (Math.abs(bytes) < thresh) {
    return bytes + ' B';
  }
  var units = si
          ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
          : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
  var u = -1;
  do {
    bytes /= thresh;
    ++u;
  } while (Math.abs(bytes) >= thresh && u < units.length - 1);
  return bytes.toFixed(1) + ' ' + units[u];
}

String.prototype.hashCode = function () {
  var hash = 0, i, char;
  if (this.length === 0)
    return hash;
  for (i = 0, l = this.length; i < l; i++) {
    char = this.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash |= 0; // Convert to 32bit integer
  }
  return Math.abs(hash);
};

Function.prototype.debounce = function (threshold) {
  var callback = this;
  var timeout;
  return function () {
    var context = this, params = arguments;
    window.clearTimeout(timeout);
    timeout = window.setTimeout(function () {
      callback.apply(context, params);
    }, threshold);
  };
};