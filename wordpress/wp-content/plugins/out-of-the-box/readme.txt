=== Out-of-the-Box ===
Requires at least: 3.8
Tested up to: 4.5

This plugin will help you to easily integrate Dropbox into your WordPress website or blog. Out-of-the-Box allows you to view, download, delete, rename files & folders directly from a WordPress page. You can use Out-of-the-Box as a filebrowser, gallery, audio- or video-player!

== Description ==

This plugin will help you to easily integrate Dropbox into your WordPress website or blog. Out-of-the-Box allows you to view, download, delete, rename files & folders directly from a WordPress page. You can use Out-of-the-Box as a filebrowser, gallery, audio- or video-player!

== Changelog ==
= 1.7.4 (26 April 2016) =
* Bug Fix for stalling upload function
* Bug Fix opening and closing multiple Lightboxes
* Bug Fix notifications for downloads
* Bug Fix menu items in case inline preview is disabled
* Bug Fix hidding empty subfolders in Gallery
* Bug Fix manually linking users to folders on a WordPress Multisite
* Bug Fix recognizing extensions in File Browser and Media Player
* Bug Fix User Folder feature
* Bug Fix in the phpThumb library breaking thumbnails in the Gallery 
* Added hook: outofthebox_download, outofthebox_upload
* Added %ip% and %location% variable for the notifications template
* Updated Dropbox SDK to version 1.1.6 with new certificate
* Updated several jQuery libraries
* Replaced Auto Updater as previous solution isn't available anymore.

= 1.7.3 (9 July 15) =
* Added support for folder upload (Currently only supported by Chrome)
* Added preview support for: 'doc', 'docx', 'docm', 'ppt', 'pps', 'ppsx', 'ppsm', 'pptx', 'pptm', 'xls', 'xlsx', 'xlsm', 'rtf' and * 'pdf'
* Added download notification for zip downloads
* Improved notifications, added hook (outofthebox_notification)
* Improved Upload Feature (removed simpleupload attribute)
* Improved layout
* Bug fix deleting multiple files
* Bug fix max upload size

= 1.7.2 (16 June 15) =
* Added Inline Preview setting
* Bug fix opening PDF in Lightbox in IE
* Bug fix browsing in File Browser
* Bug fix Gravity Forms integration

= 1.7.1 (25 May 15) =
* Bug Fix blank settings page
* Bug fix images in File Browser

= 1.7 (22 May 15) =
* Added Gravity Forms integration (Missing features? Let me know!)
* Added iLightBox as Lightbox
* Added %user_firstname% and %user_lastname% attributes to UserFolder template
* Added Max Height setting
* Bug Fix sorting folders
* Bug Fix template folders
* Bug Fix notification emails
* Bug Fix Video Player IE8
* Bug Fix for weird video dimensions in Video Player
* Improved performance in general
* Updated layout File Browser and Gallery
* Updated Blueimp jQuery File Upload to version 9.9.3
* Updated qTip2 to version 2.2.1
* Update Zip Function to 2.0.3
* Updated Jplayer to 2.9.2

= 1.6.5 (17 January 15) =
* Bug fix overlay Front-End
* Bug fix sorting files and folders
* Bug fix Zip archive
* Bug fix search function
* Updated jPlayer

= 1.6.4 (6 November 14) =
* Updated Dropbox API SDK (security reasons)
* Improved security functions
* Bug fix in-case sensitive folders
* Bug fix Shortcode Generator

= 1.6.3 (28 August 14) =
* Bug fix gallery due to updated Dropbox API
* Bug fix Zip function

= 1.6.2 (7 August 14) =
* Added editable shortcodes
* Added new search option
* Added purchase button to playlist Media Player
* Redesign Plugin settings page
* Redesign ZIP function
* Updated notification email
* Bug fix User Roles
* Bug fix Audio Player playing mp3 files in Chrome
* Bug fix creating shared links
* Bug fix User folders
* Bug fix Skins Audio players
* Bug fix editable shortcodes

= 1.6.1 (30 June 14) =
* Redesign of default jPlayer skin and added three new skins
* Added support for custom jPlayer skins
* Bug fix Dropbox API - PDFs can again viewed inline.
* Bug fix creating user folders on user registration
* Bug fix search function
* Bug fix Google Analytics integration
* Bug fix uploading mov files from iOS
* Updated jQuery.Jplayer to 2.6.0 

= 1.6 (16 June 14) =
* Added: You can now direct link users to folders
* Added: Select optional template folder for fresh created user folders
* Added deletion of multiple files at once
* Added download button for audio/video files
* Added auto-updater for WP Multisite
* Added Google Analytics integration for statistics
* Added notification for deleting files
* Bug fix removal of start & cancel buttons 
* Updated Shortcode Generator

= 1.5.2 (16 April 14) =
* Added autoplay option for media player
* Bug fix folder/file exclusion
* Bug fix Dropbox case-insensitive folder structure
* Bug fix qTip popups
* Bug fix for download function in WP Multisite setup
* Bug fix gallery for mobile browsers

= 1.5.1 (20 March 14) =
* Improved layout uploadform
* Added show more button for image
* Added support for themes which includes Isotope
* Bug fix creating zip files in gallery
* Bug fix Colorbox image grouping
* Bug fix Cache lock
* Disabled right mouse click in Out-of-the-Box container

= 1.5 (18 Februari 14) =
* Added inline embedded preview (Uses Google Doc viewer)
* Added 'show more' button for Gallery
* Added simple browser cache
* Improved layout
* Bug fix upload function
* Bug fix CSS dropdown menu

= 1.4.3 (11 Februari 14) =
* Bug fix foldernames
* Bug fix authorization

= 1.4.2 (13 Januari 14) =
* Bug fix Gallery thumbs

= 1.4.1 (8 Januari 14) =
* Bug fix encryption SSL connection Dropbox API
* Bug fix Insert Links MCE editor

= 1.4 (7 Januari 14) =
* Added email notification on download/upload
* Added mp4 support mediaplayer
* Added include attribute in Shortcode Generator
* Added direct links in MCE editor
* Added Auto-Updater
* Improved cache/thumbnails for large galleries
* Bug fix fullscreen mediaplayer
* Bug fix Lightbox gallery
* Bug fix upload buttons
* Several small bug fixes

= 1.3.2 (29 November 13) =
* Don't need to create your own Dropbox App anymore
* Added Multi-Site support
* Added new permissions:
renamerole=> renamefoldersrole, renamefilesrole
deleterole=> deletefoldersrole, deletefilesrole
* Bug fix upload function
* Bug fix zipping multiple files
* Bug fix CSS

= 1.3.1 (22 November 13) =
* Added overlay for images or PDF files
* Added some extra options to the Shortcode Generator
* Bug fixes Gallery
* Bug fix renaming folders

= 1.3 (2 November 13) =
* Added zip functionality, choose which files you would like to download or download all files at once
* Added shortlinks functionality
* Improved user folders functionality
* Improved thumbnail function
* Improved plugin settings page
* Bug fix shortcode generator
* Bug fix filenames file browser
* Bug fix downloading files

= 1.2.2 (14 Oktober 13) =
* Critical security bug fixed
* Replaced WordPress Capabilities with Roles. Replaced the following shortcode attributes and their possible values:
viewcapability => viewrole
downloadcapability => downloadrole
uploadcapability => uploadrole
renamecapability => renamerole
deletecapability => deleterole
addfoldercapability => addfolderrole

= 1.2.1 (11 Oktober 13) =
* Improved Gallery with nice grid. Resizing is done with WordPress own image editor. If WordPress can't resize the image, Dropbox own thumbnail will be used.
* Added image shuffle

= 1.2 (9 Oktober 13) =
* Added sort function and sortable columns to the file browser
* Added search function
* Improved breadcrumb
* Reworked video and audio player skin and added responsiveness.
* Updated shortcode generator

= 1.1.4 (7 Oktober 13) =
* Critical bug fix uploading files to Dropbox
* Multiple fixes involving adding/renaming/deleting of files and folders
* Improved file browsing
* Removed sessions for Out-of-the-Box information
* Added some CSS

= 1.1.3 (25 September 13) =
* Bug fix 'addfolder' parameter in shortcode gallery

= 1.1.2 (21 September 13) =
* Bug fix location cache files

= 1.1.1 (19 September 13) =
* Improved admin interface
* Replaced file upload temp directory with WordPress own
* You can use Out-of-the-Box without SSL-certificate now

= 1.1 (17 September 13) =
* Added a HTML5 audio/video player with flash-fallback
* Added gallery

= 1.0 (10 September 13) =
* Initial release version