<?php

function ukai_enqueue_block_style() {
	$files = glob( get_template_directory_uri() . '/build/css/block/*.css' );

	foreach ( $files as $file ) {

		$filename   = basename( $file, '.css' );
		$block_name = str_replace( 'core-', 'core/', $filename );

		wp_enqueue_block_style(
			$block_name,
			array(
				'handle' => "cormorant-block-{$filename}",
				'src'    => get_theme_file_uri( "/assets/css/block/{$filename}.css" ),
				'path'   => get_theme_file_path( "/assets/css/block/{$filename}.css" ),
			)
		);
	}
}
add_action( 'init', 'ukai_enqueue_block_style' );