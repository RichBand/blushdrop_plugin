<?php
/*Important note about getMetadataWithChildren(): you need to go to the
Client class in Client.php and set the parameter include_media_info to true
$this->_getMetadata($path, array("include_media_info" => "true",
in order to get the media info, Dropbox doesnt get it activated by defualt
https://www.dropbox.com/developers-v1/core/docs#metadata
 * */
function getFolderMetadata($path){
    $folderMetadata = null;
    $GLOBALS["dbxClient"] = connectDropbox();
    $folderMetadata = $GLOBALS["dbxClient"]->getMetadataWithChildren($path);
    return $folderMetadata;
}

function getFolderMetadata_ajax() {
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
        $path = $_REQUEST['path'];
        // Let's take the data that was sent and do something with it
        if ( $path != '' || $path != null ){
            $folderMetadata = getFolderMetadata($path);
        }
        // Now we'll return it to the javascript function
        // Anything outputted will be returned in the response
        echo $folderMetadata;
        // If you're debugging, it might be useful to see what was sent in the $_REQUEST
        // print_r($_REQUEST);
    }
    // Always die in functions echoing ajax content
    die();
}
add_action( 'wp_ajax_example_ajax_request', 'getFolderMetadata_ajax' );

