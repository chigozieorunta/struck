<?php
/**
 * Plugin Name: Struck
 * Description: Convert images to WebP
 * Version: 1.0.0
 * Author: Chigozie Orunta
 * Author URI: https://github.com/chigozieorunta/struck
 * Text Domain: struck
 *
 * @package Struck
 */

require 'vendor/autoload.php';

use WebPConvert\WebPConvert;

//add_action( 'init', 'convert_all_images_to_webp' );
//add_filter( 'wp_get_attachment_image_src', 'convert_images_to_webp', 10, 4 );

function convert_images_to_webp( $image, $attachment_id, $size, $icon ) {
	$source      = get_system_path_for_image( $attachment_id );
	$destination = $source . '.webp';
	$options     = array(
		'fail'                 => 'original',
		'fail-when-fail-fails' => 'throw',
		'reconvert'            => false,
		'serve-original'       => false,
		'show-report'          => false,
		'suppress-warnings'    => true,

		'redirect-to-self-instead-of-serving' => false,

		'serve-image' => [
			'headers' => [
				'cache-control'  => false,
				'content-length' => true,
				'content-type'   => true,
				'expires'        => false,
				'last-modified'  => true,
				'vary-accept'    => false
			],
			'cache-control-header' => 'public, max-age=31536000',
		],
		'convert' => [
			'quality' => 'auto',
		]
	);

	WebPConvert::serveConverted($source, $destination, $options);
}

function get_all_images() {
	$query_images_args = array(
        'post_parent'    => get_the_ID(),
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
	);

	$query_images = new WP_Query( $query_images_args );

	$images = array();
	foreach ( $query_images->posts as $image ) {
		$images[] = $image;
	}

	return $images;
}

function convert_all_images_to_webp() {
	$images = get_all_images();
	foreach( $images as $image ) {
		$source      = get_system_path_for_image( $image->ID );
		$destination = $source . '.webp';
		$options     = array(
			'fail'                 => 'original',
			'fail-when-fail-fails' => 'throw',
			'reconvert' => false,
			'serve-original' => false,
			'show-report' => false,
			'suppress-warnings' => true,
			'redirect-to-self-instead-of-serving' => false,
			'serve-image' => [
				'headers' => [
					'cache-control' => false,
					'content-length' => true,
					'content-type' => true,
					'expires' => false,
					'last-modified' => true,
					'vary-accept' => false
				],
				'cache-control-header' => 'public, max-age=31536000',
			],
			'convert' => [
				'quality' => 'auto',
			]
		);

		WebPConvert::serveConverted($source, $destination, $options);
	}
}

function get_system_path_for_image( $attachment_id ) {
	$url       = wp_get_attachment_url( $attachment_id );
	$uploads   = wp_upload_dir();
	$file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $url );

	return $file_path;
}