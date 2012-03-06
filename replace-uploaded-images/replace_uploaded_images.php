<?php
/*
Plugin Name: Replace Uploaded Images
Plugin URI: http://mikekelly.myblog.arts.ac.uk/software
Description: Replaces the uploaded image with the Wordpress-generated 'Large' image, as specified in Settings -> Media
			 This was created in response to users uploading very large images directly from their digital cameras.
			 Can be optionally disabled in Settings -> Media.
Version: 0.1
Author: Mike Kelly, Serge Rauber
Author URI: http://mikekelly.myblog.arts.ac.uk

Filter function by Serge Rauber http://wp.kalyx.fr/05-02-2010-redimensionner-image-uploadee

This script is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function rui_replace_uploaded_image($image_data) {
	
	$rui_enabled = get_option( 'rui_enable_disable' );
	// Disabled? Return original image data
	if ( $rui_enabled == 0 ) {
		return $image_data;
	}
	
	// if there is no large image, return original image data
	if (!isset($image_data['sizes']['large'])) {
		return $image_data;
	}

	// paths to the uploaded image and the large image
	$upload_dir = wp_upload_dir();
	$uploaded_image_location = $upload_dir['basedir'] . '/' .$image_data['file'];
	$large_image_location = $upload_dir['path'] . '/'.$image_data['sizes']['large']['file'];

	// delete the uploaded image
	unlink($uploaded_image_location);

	// rename the large image
	rename($large_image_location,$uploaded_image_location);

	// update image metadata and return them
	$image_data['width'] = $image_data['sizes']['large']['width'];
	$image_data['height'] = $image_data['sizes']['large']['height'];
	unset($image_data['sizes']['large']);

	return $image_data;
}
add_filter('wp_generate_attachment_metadata','rui_replace_uploaded_image');

function rui_section_title_and_description() {
	?><p><?php _e('Set whether to keep the original uploaded image, or replace it with the Large size image, if defined above. This is enabled by default for better performance and to save disk space. Disable this option if, for example, you want to share images for print, and need the original high resolution versions.', 'rui_replace_uploaded_images') ?></p><?php
}

function rui_enable_disable() {
	?><fieldset><legend class="hidden"><?php _e('Replace uploaded images', 'rui_replace_uploaded_images') ?></legend>
<input id="rui_enable_disable" type="checkbox" value="1" name="rui_enable_disable" <?php checked('1',  get_option('rui_enable_disable') ); ?>/>
<label for="rui_enable_disable"><?php _e('enabled', 'rui_replace_uploaded_images'); ?></label>
</fieldset><?php
}

function rui_settings_activation() {
	add_option('rui_enable_disable', '1');
}

function rui_add_settings() {
	register_setting('media', 'rui_enable_disable', 'intval');
	add_settings_section('replaceuploadedimages', __('Replace Uploaded Images', 'rui_replace_uploaded_images'), 'rui_section_title_and_description', 'media');
	add_settings_field('rui_enable_disable', __('Replace uploaded images', 'rui_replace_uploaded_images'), 'rui_enable_disable', 'media', 'replaceuploadedimages');
}

add_action('admin_init', 'rui_add_settings');
register_activation_hook(__FILE__, 'rui_settings_activation');
?>