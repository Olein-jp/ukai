<?php
/**
 * functions.php
 *
 * @package ukai
 * @author Olein-jp
 * @since 1.0.0
 */

if ( ! function_exists( 'ukai_enqueue_styles' ) ) {
	/**
	 * Enqueue global styles
	 *
	 * @return void
	 */
	function ukai_enqueue_styles() {
		wp_enqueue_style(
			'ukai-style',
			get_template_directory_uri() . '/build/css/style.css',
			array(),
			filemtime( get_theme_file_path( 'style.css' ) )
		);
	}

	add_action( 'wp_enqueue_scripts', 'ukai_enqueue_styles' );
}

if ( ! function_exists( 'ukai_enqueue_block_style' ) ) {
	/**
	 * Enqueue block styles
	 *
	 * @return void
	 */
	function ukai_enqueue_block_style() {
		$files = glob( get_template_directory() . '/build/css/block/*.css' );

		foreach ( $files as $file ) {

			$filename   = basename( $file, '.css' );
			$block_name = str_replace( 'core-', 'core/', $filename );

			wp_enqueue_block_style(
				$block_name,
				array(
					'handle' => "ukai-{$filename}",
					'src'    => get_theme_file_uri( "/build/css/block/{$filename}.css" ),
					'path'   => get_theme_file_path( "/build/css/block/{$filename}.css" ),
				)
			);
		}
	}

	add_action( 'init', 'ukai_enqueue_block_style' );
}

/**
 * Required generate fluid typography font size preset and update theme.json
 */
require get_template_directory() . '/inc/fluid-typography.php';
