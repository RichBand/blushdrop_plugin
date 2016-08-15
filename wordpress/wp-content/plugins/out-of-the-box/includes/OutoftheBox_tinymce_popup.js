jQuery(document).ready(function ($) {
  'use strict';

  $("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix").show();
  ;
  $("#tabs li").removeClass("ui-corner-top").addClass("ui-corner-left");
  $(".loadingshortcode").fadeOut(300, function () {
    $(this).remove();
  });

  /* Fix for not scrolling popup*/
  if (/Android|webOS|iPhone|iPod|iPad|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    var parent = $(tinyMCEPopup.getWin().document);

    if (parent.find('#safari_fix').length === 0) {
      parent.find('.mceWrapper iframe').wrap(function () {
        return $('<div id="safari_fix"/>').css({
          'width': "100%",
          'height': "100%",
          'overflow': 'auto',
          '-webkit-overflow-scrolling': 'touch'
        });
      });
    }
  }

  $('input:checkbox:not(.simple)').radiobutton({
    className: 'jquery-switch',
    checkedClass: 'jquery-switch-on'
  });

  $('input:radio').radiobutton();

  /* qTip help ballons */
  $('.OutoftheBox .help').qtip({
    content: {
      attr: 'title'
    },
    position: {
      my: 'bottom center',
      at: 'top center',
      viewport: $(window),
      adjust: {
        scroll: false
      }
    },
    style: {
      classes: 'OutoftheBox help qtip-light'
    },
    show: {
      solo: true
    }

  });

  $("input[name=mode]:radio").change(function () {

    $('.option').hide();
    $("#OutoftheBox_linkedfolders").trigger('change');

    switch ($(this).val()) {
      case 'files':
        $('.option.forfilebrowser').not('.hidden').show();
        $('#settings_userfolders_tab, #settings_upload_tab, #settings_advanced_tab, #settings_manipulation_tab, #settings_notifications_tab').show();
        $('#settings_mediafiles_tab').hide();
        break;

      case 'gallery':
        $('.option.forgallery').show();
        $('#settings_userfolders_tab, #settings_upload_tab, #settings_advanced_tab, #settings_manipulation_tab, #settings_notifications_tab').show();
        $('#settings_mediafiles_tab').hide();
        $('#OutoftheBox_upload_ext, #OutoftheBox_ext').val('gif|jpg|jpeg|png|bmp');
        break;

      case 'audio':
        $('.option.foraudio').show();
        $('.root-folder').show();
        $('.no-root-folder').hide();
        $('#settings_mediafiles_tab').show();
        $('#settings_userfolders_tab, #settings_upload_tab, #settings_advanced_tab, #settings_manipulation_tab, #settings_notifications_tab').hide();
        break;

      case 'video':
        $('.option.forvideo').show();
        $('.root-folder').show();
        $('.no-root-folder').hide();
        $('#settings_mediafiles_tab').show();
        $('#settings_userfolders_tab, #settings_upload_tab, #settings_advanced_tab, #settings_manipulation_tab, #settings_notifications_tab').hide();
        break;
    }

    $("#OutoftheBox_breadcrumb, #OutoftheBox_mediapurchase, #OutoftheBox_search, #OutoftheBox_slideshow, #OutoftheBox_upload, #OutoftheBox_rename, #OutoftheBox_delete, #OutoftheBox_addfolder").trigger('change');
    $('#OutoftheBox_linkedfolders').trigger('change');
  });

  $("input[name=OutoftheBox_file_layout]:radio").change(function () {
    switch ($(this).val()) {
      case 'grid':
        $('.columnnames-options, .option-filesize, .option-filedate').hide();
        break;
      case 'list':
        $('.columnnames-options, .option-filesize, .option-filedate').show();
        break;
    }
  });

  $("#OutoftheBox_breadcrumb, #OutoftheBox_mediapurchase, #OutoftheBox_search, #OutoftheBox_upload, #OutoftheBox_rename, #OutoftheBox_delete, #OutoftheBox_addfolder, #OutoftheBox_user_folders, #OutoftheBox_userfolders_template").change(function () {
    var toggleelement = '.' + $(this).attr('data-div-toggle');
    if ($(this).is(":checked")) {
      $(toggleelement).show().removeClass('hidden');
    } else {
      $(toggleelement).hide().addClass('hidden');
    }
  });

  $("#OutoftheBox_linkedfolders").change(function () {
    if ($(this).is(":checked")) {
      $(".option-userfolders ").show();
    } else {
      $(".option-userfolders").hide();
    }
    $('input[name=OutoftheBox_userfolders_method]:radio:checked').trigger('change').prop('checked', true)
  });

  $("input[name=OutoftheBox_userfolders_method]:radio").change(function () {
    switch ($(this).val()) {
      case 'manual':
        $('.root-folder').hide();
        $('.no-root-folder').show();
        $('.option-userfolders_auto').hide().addClass('hidden');
        break;
      case 'auto':
        $('.root-folder').show();
        $('.no-root-folder').hide();
        $('.option-userfolders_auto').show().removeClass('hidden');
        break;
    }
  });

  $("input[name=sort_field]:radio").change(function () {
    switch ($(this).val()) {
      case 'shuffle':
        $('.option-sort-field').hide();
        break;
      default:
        $('.option-sort-field').show();
        break;
    }
  });


  $(".OutoftheBox .insert_links").click(createDirectLinks);
  $(".OutoftheBox .insert_embedded").click(insertEmbedded);
  $('.OutoftheBox .insert_shortcode').click(insertOutoftheBoxShortCode);
  $('.OutoftheBox .insert_shortcode_gf').click(insertOutoftheBoxShortCodeGF);

  $(".OutoftheBox img.preloading").unveil(200, $(".OutoftheBox .ajax-filelist"), function () {
    $(this).load(function () {
      $(this).removeClass('preloading');
    });
  });

  /* Initialise from shortcode */
  $('input[name=mode]:radio:checked').trigger('change').prop('checked', true)

  function createShortcode() {

    var dir = $(".root-folder .current-folder-raw").text(),
            linkedfolders = $('#OutoftheBox_linkedfolders').prop("checked"),
            show_files = $('#OutoftheBox_showfiles').prop("checked"),
            show_folders = $('#OutoftheBox_showfolders').prop("checked"),
            ext = $('#OutoftheBox_ext').val(),
            show_filesize = $('#OutoftheBox_filesize').prop("checked"),
            show_filedate = $('#OutoftheBox_filedate').prop("checked"),
            show_ext = $('#OutoftheBox_showext').prop("checked"),
            show_columnnames = $('#OutoftheBox_showcolumnnames').prop("checked"),
            candownloadzip = $('#OutoftheBox_candownloadzip').prop("checked"),
            showsharelink = $('#OutoftheBox_showsharelink').prop("checked"),
            showrefreshbutton = $('#OutoftheBox_showrefreshbutton').prop("checked"),
            show_breadcrumb = $('#OutoftheBox_breadcrumb').prop("checked"),
            breadcrumb_roottext = $('#OutoftheBox_roottext').val(),
            show_root = $('#OutoftheBox_rootname').prop("checked"),
            search = $('#OutoftheBox_search').prop("checked"),
            search_from = $('#OutoftheBox_searchfrom').prop("checked"),
            previewinline = $('#OutoftheBox_previewinline').prop("checked"),
            force_download = $('#OutoftheBox_forcedownload').prop("checked"),
            include = $('#OutoftheBox_include').val(),
            exclude = $('#OutoftheBox_exclude').val(),
            sort_field = $("input[name=sort_field]:radio:checked").val(),
            sort_order = $("input[name=sort_order]:radio:checked").val(),
            crop = $('#OutoftheBox_crop').prop("checked"),
            slideshow = $('#OutoftheBox_slideshow').prop("checked"),
            pausetime = $('#OutoftheBox_pausetime').val(),
            maximages = $('#OutoftheBox_maximage').val(),
            target_height = $('#OutoftheBox_targetHeight').val(),
            max_width = $('#OutoftheBox_max_width').val(),
            max_height = $('#OutoftheBox_max_height').val(),
            upload = $('#OutoftheBox_upload').prop("checked"),
            overwrite = $('#OutoftheBox_overwrite').prop("checked"),
            upload_ext = $('#OutoftheBox_upload_ext').val(),
            maxfilesize = $('#OutoftheBox_maxfilesize').val(),
            rename = $('#OutoftheBox_rename').prop("checked"),
            move = $('#OutoftheBox_move').prop("checked"),
            can_delete = $('#OutoftheBox_delete').prop("checked"),
            can_addfolder = $('#OutoftheBox_addfolder').prop("checked"),
            notification_download = $('#OutoftheBox_notificationdownload').prop("checked"),
            notification_upload = $('#OutoftheBox_notificationupload').prop("checked"),
            notification_deletion = $('#OutoftheBox_notificationdeletion').prop("checked"),
            notification_emailaddress = $('#OutoftheBox_notification_email').val(),
            use_template_dir = $('#OutoftheBox_userfolders_template').prop("checked"),
            template_dir = $(".template-folder .OutoftheBox.files .current-folder-raw").text(),
            view_role = readCheckBoxes("input[name='OutoftheBox_view_role[]']"),
            download_role = readCheckBoxes("input[name='OutoftheBox_download_role[]']"),
            upload_role = readCheckBoxes("input[name='OutoftheBox_upload_role[]']"),
            renamefiles_role = readCheckBoxes("input[name='OutoftheBox_renamefiles_role[]']"),
            renamefolders_role = readCheckBoxes("input[name='OutoftheBox_renamefolders_role[]']"),
            move_role = readCheckBoxes("input[name='OutoftheBox_move_role[]']"),
            deletefiles_role = readCheckBoxes("input[name='OutoftheBox_deletefiles_role[]']"),
            deletefolders_role = readCheckBoxes("input[name='OutoftheBox_deletefolders_role[]']"),
            addfolder_role = readCheckBoxes("input[name='OutoftheBox_addfolder_role[]']"),
            view_user_folders_role = readCheckBoxes("input[name='OutoftheBox_view_user_folders_role[]']"),
            mediaextensions = readCheckBoxes("input[name='OutoftheBox_mediaextensions[]']"),
            autoplay = $('#OutoftheBox_autoplay').prop("checked"),
            hideplaylist = $('#OutoftheBox_hideplaylist').prop("checked"),
            linktomedia = $('#OutoftheBox_linktomedia').prop("checked"),
            mediapurchase = $('#OutoftheBox_mediapurchase').prop("checked"),
            linktoshop = $('#OutoftheBox_linktoshop').val();



    var data = '';

    if (OutoftheBox_vars.shortcodeRaw === '1') {
      data += '[raw]';
    }

    data += '[outofthebox ';


    if (dir !== '/' && dir !== '') {
      if (linkedfolders) {
        if ($("input[name=OutoftheBox_userfolders_method]:radio:checked").val() !== 'manual') {
          data += 'dir="' + dir + '" ';
        }
      } else {
        data += 'dir="' + dir + '" ';
      }
    }

    if (max_width !== '') {
      if (max_width.indexOf("px") !== -1 || max_width.indexOf("%") !== -1) {
        data += 'maxwidth="' + max_width + '" ';
      } else {
        data += 'maxwidth="' + parseInt(max_width) + '" ';
      }
    }

    if (max_height !== '') {
      if (max_height.indexOf("px") !== -1 || max_height.indexOf("%") !== -1) {
        data += 'maxheight="' + max_height + '" ';
      } else {
        data += 'maxheight="' + parseInt(max_height) + '" ';
      }
    }

    data += 'mode="' + $("input[name=mode]:radio:checked").val() + '" ';

    if (ext !== '') {
      data += 'ext="' + ext + '" ';
    }

    if (include !== '') {
      data += 'include="' + include + '" ';
    }
    if (exclude !== '') {
      data += 'exclude="' + exclude + '" ';
    }

    if (view_role !== 'administrator|editor|author|contributor|subscriber|pending|guest') {
      data += 'viewrole="' + view_role + '" ';
    }

    if (sort_field !== 'name') {
      data += 'sortfield="' + sort_field + '" ';
    }

    if (sort_field !== 'shuffle' && sort_order !== 'asc') {
      data += 'sortorder="' + sort_order + '" ';
    }

    var mode = $("input[name=mode]:radio:checked").val();
    switch (mode) {
      case 'audio':
      case 'video':

        if (mediaextensions === '') {
          $('#settings_mediafiles_tab a').trigger('click');
          $(".mediaextensions").css("color", "red");
          return false;
        }
        data += 'mediaextensions="' + mediaextensions + '" ';

        if (autoplay === true) {
          data += 'autoplay="1" ';
        }

        if (hideplaylist === true) {
          data += 'hideplaylist="1" ';
        }

        if (linktomedia === true) {
          data += 'linktomedia="1" ';
        }

        if (mediapurchase === true && linktoshop !== '') {
          data += 'linktoshop="' + linktoshop + '" ';
        }

        break;

      case 'files':
      case 'gallery':
        if (mode === 'gallery') {

          if (maximages !== '') {
            data += 'maximages="' + maximages + '" ';
          }

          if (crop === true) {
            data += 'crop="1" ';
          }

          if (target_height !== '') {
            data += 'targetheight="' + target_height + '" ';
          }

          if (slideshow === true) {
            data += 'slideshow="1" ';
            if (pausetime !== '') {
              data += 'pausetime="' + pausetime + '" ';
            }
          }
        }

        if (mode === 'files') {
          if (show_files === false) {
            data += 'showfiles="0" ';
          }
          if (show_folders === false) {
            data += 'showfolders="0" ';
          }
          if (show_filesize === false) {
            data += 'filesize="0" ';
          }

          if (show_filedate === false) {
            data += 'filedate="0" ';
          }

          if (show_ext === false) {
            data += 'showext="0" ';
          }

          if (force_download === true) {
            data += 'forcedownload="1" ';
          }

          if (show_columnnames === false) {
            data += 'showcolumnnames="0" ';
          }

        }

        if (download_role !== 'administrator|editor|author|contributor|subscriber|pending|guest') {
          data += 'downloadrole="' + download_role + '" ';
        }

        if (previewinline === false) {
          data += 'previewinline="0" ';
        }

        if (candownloadzip === true) {
          data += 'candownloadzip="1" ';
        }

        if (showsharelink === true) {
          data += 'showsharelink="1" ';
        }

        if (showrefreshbutton === false) {
          data += 'showrefreshbutton="0" ';
        }

        if (search === false) {
          data += 'search="0" ';
        } else {
          if (search_from === true) {
            data += 'searchfrom="selectedroot" ';
          }
        }

        if (show_breadcrumb === true) {
          if (show_root === true) {
            data += 'showroot="1" ';
          }
          if (breadcrumb_roottext !== '') {
            data += 'roottext="' + breadcrumb_roottext + '" ';
          }
        } else {
          data += 'showbreadcrumb="0" ';
        }

        if (notification_download === true || notification_upload === true || notification_deletion === true) {
          if (notification_emailaddress !== '') {
            data += 'notificationemail="' + notification_emailaddress + '" ';
          }
        }

        if (notification_download === true) {
          data += 'notificationdownload="1" ';
        }

        if (upload === true) {
          data += 'upload="1" ';

          if (upload_role !== 'administrator|editor|author|contributor|subscriber') {
            data += 'uploadrole="' + upload_role + '" ';
          }
          if (maxfilesize !== '') {
            data += 'maxfilesize="' + maxfilesize + '" ';
          }

          if (overwrite === true) {
            data += 'overwrite="1" ';
          }

          if (upload_ext !== '') {
            data += 'uploadext="' + upload_ext + '" ';
          }

          if (notification_upload === true) {
            data += 'notificationupload="1" ';
          }

        }

        if (rename === true) {
          data += 'rename="1" ';

          if (renamefiles_role !== 'administrator|editor') {
            data += 'renamefilesrole="' + renamefiles_role + '" ';
          }
          if (renamefolders_role !== 'administrator|editor') {
            data += 'renamefoldersrole="' + renamefolders_role + '" ';
          }
        }

        if (move === true) {
          data += 'move="1" ';

          if (move_role !== 'administrator|editor') {
            data += 'moverole="' + move_role + '" ';
          }
        }

        if (can_delete === true) {
          data += 'delete="1" ';

          if (deletefiles_role !== 'administrator|editor') {
            data += 'deletefilesrole="' + deletefiles_role + '" ';
          }
          if (deletefolders_role !== 'administrator|editor') {
            data += 'deletefoldersrole="' + deletefolders_role + '" ';
          }

          if (notification_deletion === true) {
            data += 'notificationdeletion="1" ';
          }
        }

        if (can_addfolder === true) {
          data += 'addfolder="1" ';

          if (addfolder_role !== 'administrator|editor') {
            data += 'addfolderrole="' + addfolder_role + '" ';
          }
        }

        if (linkedfolders === true) {
          var method = $("input[name=OutoftheBox_userfolders_method]:radio:checked").val();
          data += 'userfolders="' + method + '" ';

          if (method === 'auto' && use_template_dir === true && template_dir !== '') {
            data += 'usertemplatedir="' + template_dir + '" ';
          }

          if (view_user_folders_role !== 'administrator') {
            data += 'viewuserfoldersrole="' + view_user_folders_role + '" ';
          }
        }

        break;
    }

    data += ']';

    if (OutoftheBox_vars.shortcodeRaw === '1') {
      data += '[/raw]';
    }

    return data;

  }

  function insertOutoftheBoxShortCode() {
    var data = createShortcode();

    tinyMCEPopup.execCommand('mceInsertContent', false, data);
    // Refocus in window
    if (tinyMCEPopup.isWindow)
      window.focus();
    tinyMCEPopup.editor.focus();
    tinyMCEPopup.close();
  }

  function insertOutoftheBoxShortCodeGF() {
    var data = createShortcode();
    $('#field_outofthebox', window.parent.document).val(data);
    window.parent.SetFieldProperty('OutoftheBoxShortcode', data);
    window.parent.tb_remove();
  }

  function createDirectLinks() {
    var listtoken = $(".OutoftheBox.files").attr('data-token'),
            lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path'),
            entries = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");

    if (entries.length === 0) {
      if (tinyMCEPopup.isWindow)
        window.focus();
      tinyMCEPopup.editor.focus();
      tinyMCEPopup.close();
    }

    $.ajax({
      type: "POST",
      url: OutoftheBox_vars.ajax_url,
      data: {
        action: 'outofthebox-create-link',
        listtoken: listtoken,
        lastpath: lastpath,
        entries: entries,
        _ajax_nonce: OutoftheBox_vars.createlink_nonce
      },
      beforeSend: function () {
        $(".OutoftheBox .loading").height($(".OutoftheBox .ajax-filelist").height());
        $(".OutoftheBox .loading").fadeTo(400, 0.8);
        $(".OutoftheBox .insert_links").attr('disabled', 'disabled');
      },
      complete: function () {
        $(".OutoftheBox .loading").fadeOut(400);
        $(".OutoftheBox .insert_links").removeAttr('disabled');
      },
      success: function (response) {
        if (response !== null) {
          if (response.links !== null && response.links.length > 0) {

            var data = '<table>';

            $.each(response.links, function (key, linkresult) {
              data += '<tr><td><a href="' + linkresult.link.replace('?dl=1', '') + '">' + linkresult.name + '</a></td><td>&nbsp;</td><td>' + linkresult.size + '</td></tr>';
            });

            data += '</table>';

            tinyMCEPopup.execCommand('mceInsertContent', false, data);
            // Refocus in window
            if (tinyMCEPopup.isWindow)
              window.focus();
            tinyMCEPopup.editor.focus();
            tinyMCEPopup.close();
          } else {
          }
        }
      },
      dataType: 'json'
    });
    return false;
  }

  function insertEmbedded() {
    var listtoken = $(".OutoftheBox.files").attr('data-token'),
            lastpath = $(".OutoftheBox[data-token='" + listtoken + "']").attr('data-path'),
            entries = readArrCheckBoxes(".OutoftheBox[data-token='" + listtoken + "'] input[name='selected-files[]']");

    if (entries.length === 0) {
      if (tinyMCEPopup.isWindow)
        window.focus();
      tinyMCEPopup.editor.focus();
      tinyMCEPopup.close();
    }

    $.ajax({
      type: "POST",
      url: OutoftheBox_vars.ajax_url,
      data: {
        action: 'outofthebox-embedded',
        listtoken: listtoken,
        lastpath: lastpath,
        entries: entries,
        _ajax_nonce: OutoftheBox_vars.createlink_nonce
      },
      beforeSend: function () {
        $(".OutoftheBox .loading").height($(".OutoftheBox .ajax-filelist").height());
        $(".OutoftheBox .loading").fadeTo(400, 0.8);
        $(".OutoftheBox .insert_links").attr('disabled', 'disabled');
      },
      complete: function () {
        $(".OutoftheBox .loading").fadeOut(400);
        $(".OutoftheBox .insert_links").removeAttr('disabled');
      },
      success: function (response) {
        if (response !== null) {
          if (response.links !== null && response.links.length > 0) {

            var data = '';

            $.each(response.links, function (key, linkresult) {
              data += '<iframe src="' + linkresult.embeddedlink + '" height="480" style="width:100%;" frameborder="0" scrolling="no" class="oftb-embedded"></iframe>';
            });



            tinyMCEPopup.execCommand('mceInsertContent', false, data);
            // Refocus in window
            if (tinyMCEPopup.isWindow)
              window.focus();
            tinyMCEPopup.editor.focus();
            tinyMCEPopup.close();
          } else {
          }
        }
      },
      dataType: 'json'
    });
    return false;
  }

  function readCheckBoxes(element) {
    var values = $(element + ":checked").map(function () {
      return this.value;
    }).get();


    if (values.length === 0) {
      return "none";
    }

    return values.join('|');
  }
});

(function ($) {
  $.fn.disableTab = function (tabIndex, hide) {

    // Get the array of disabled tabs, if any
    var disabledTabs = this.tabs("option", "disabled");

    if ($.isArray(disabledTabs)) {
      var pos = $.inArray(tabIndex, disabledTabs);

      if (pos < 0) {
        disabledTabs.push(tabIndex);
      }
    }
    else {
      disabledTabs = [tabIndex];
    }

    this.tabs("option", "disabled", disabledTabs);

    if (hide === true) {
      $(this).find('li:eq(' + tabIndex + ')').addClass('ui-state-hidden');
    }

    // Enable chaining
    return this;
  };

  $.fn.enableTab = function (tabIndex) {

    // Remove the ui-state-hidden class if it exists
    $(this).find('li:eq(' + tabIndex + ')').removeClass('ui-state-hidden');

    // Use the built-in enable function
    this.tabs("enable", tabIndex);

    // Enable chaining
    return this;

  };
})(jQuery);