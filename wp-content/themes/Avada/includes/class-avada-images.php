<?php

class Avada_Images {

    public function __construct() {
    	global $smof_data;
    	
        if ( ! $smof_data['status_lightbox'] ) {
        	add_filter( 'wp_get_attachment_link', array( $this, 'prepare_lightbox_links' ) );
        }
        
        add_filter( 'jpeg_quality', array( $this, 'image_full_quality' ) );
        add_filter( 'wp_editor_set_quality', array( $this, 'image_full_quality' ) );
    }

    /**
     * Adds lightbox attributes to links
     */
    public function prepare_lightbox_links( $content ) {
    
		preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/i', $content, $matches );
        $attachment_id = self::get_attachment_id_from_url( $matches[2][0] );
        $title = get_post_field( 'post_title', $attachment_id ); 
        $caption = get_post_field('post_excerpt', $attachment_id );

        $content = preg_replace( "/<a/", '<a data-rel="iLightbox[postimages]" data-title="' . $title . '" data-caption="' . $caption . '"' , $content, 1 );
        
        return $content;
    }

    /**
     * Modify the image quality and set it to 100
     */
    public function image_full_quality( $quality ) {
    	return 100;
    }
    
    /**
     * Gets the attachment ID from the url
     *
     * @param string $attachment_url The url of the attachment
     *
     * @return string The attachment ID
     */    
	public function get_attachment_id_from_url( $attachment_url = '' ) {
		global $wpdb;
		$attachment_id = false;

		if ( $attachment_url == '' ) {
			return;
		}

		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

			// Run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
		}
		
		return $attachment_id;
	}    

}

// Omit closing PHP tag to avoid "Headers already sent" issues.
