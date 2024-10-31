/*!
* File type: JavaScript Document
* Plugin: Geo2 Maps Add-on for NextGEN Gallery
* Description: Code uploading content from WordPress library
* Author: Pawel Block
* Version: 2.0.1
*/	

jQuery( document ).ready( function( $ ) 
{
  // Uploading files
  var file_frame;
  var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

  jQuery( '#upload_path_button' ).on( 'click', function( event3 ) {
    event3.preventDefault();
    // If the media frame already exists, reopen it.
    if ( file_frame ) {
      // Set the post ID to what we want
      // Open frame
      file_frame.open();
      return;
    } 
    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media( {
      title: 'Select one XML, KML, KMZ, GeoRSS, GML or GPX file to upload',
      button: {
        text: 'Use this file',
      },
      multiple: false // Set to true to allow multiple files to be selected
    } );
    // When an image is selected, run a callback.
    file_frame.on( 'select', function() {
      // We set multiple to false so only get one image from the uploader
      attachment = file_frame.state().get( 'selection' ).first().toJSON();
      // Do something with attachment.url here
      $( '#xmlurl' ).val( attachment.url );
      // Restore the main post ID
      wp.media.model.settings.post.id = wp_media_post_id;
    } );
      // Finally, open the modal
      file_frame.open();
  } );

  // Restore the main ID when the add media button is pressed
  jQuery( 'a.add_media' ).on( 'click', function() {
    wp.media.model.settings.post.id = wp_media_post_id;
  } );
} );
