<?php
/**
 * inc/fluid-typography.php
 *
 * @package ukai
 * @author Olein-jp
 * @since 1.0.0
 */

if ( ! function_exists( 'ukai_generate_fluid_font_preset' ) ) {
	/**
	 * Generate fluid font size preset
	 *
	 * @return array
	 */
	function ukai_generate_fluid_font_preset() {
		$min_font_size_ratio = apply_filters( 'ukai_fluid_font_min_font_size_ratio', 1.1 );
		$max_font_size_ratio = apply_filters( 'ukai_fluid_font_max_font_size_ratio', 1.2 );
		$min_base_font_size  = apply_filters( 'ukai_fluid_font_min_base_size', 1 );
		$max_base_font_size  = apply_filters( 'ukai_fluid_font_max_base_size', 1.25 );
		$min_viewport_width  = apply_filters( 'ukai_fluid_font_min_viewport_width', 25 ); // 25
		$max_viewport_width  = apply_filters( 'ukai_fluid_font_max_viewport_width', 98.75 ); // 98.75
		$font_step_below     = min( apply_filters( 'ukai_fluid_font_step_below', 2 ), 3 );
		$font_step_above     = max( apply_filters( 'ukai_fluid_font_step_above', 8 ), 10 );

		$min_font_size = [];
		$max_font_size = [];

		for ( $i = $font_step_below; $i >= 1; $i-- ) {
			$min_font_size[] = $min_base_font_size / pow( $min_font_size_ratio, $i );
			$max_font_size[] = $max_base_font_size / pow( $max_font_size_ratio, $i );
		}

		$min_font_size[] = $min_base_font_size;
		$max_font_size[] = $max_base_font_size;

		for ( $i = 1; $i <= $font_step_above; $i++ ) {
			$min_font_size[] = $min_base_font_size * pow( $min_font_size_ratio, $i );
			$max_font_size[] = $max_base_font_size * pow( $max_font_size_ratio, $i );
		}

		$fluid_font_size_preset = [];
		$size_labels_below      = array( 's', 'xs', '2xZs' );
		$size_labels_above      = array( 'l', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', '8xl', '9xl' );
		$size_labels_below      = array_reverse( array_slice( $size_labels_below, 0, $font_step_below ) );
		$size_labels_above      = array_slice( $size_labels_above, 0, $font_step_above );
		$size_labels            = array_merge( $size_labels_below, array( 'm' ), $size_labels_above );

		foreach ( $min_font_size as $index => $min_size ) {
			if ( ! isset( $size_labels[ $index ] ) ) {
				continue;
			}
			$max_size = $max_font_size[ $index ];

			$slope = ( $max_size - $min_size ) / ( $max_viewport_width - $min_viewport_width );

			$y_intersection = ( -1 * $min_viewport_width ) * $slope + $min_size;

			$fluid_font_size_preset[ $size_labels[ $index ] ] = 'clamp( ' . round( $min_size, 4 ) . 'rem, ' . round( $y_intersection, 4 ) . 'rem + ' . ( round( $slope, 6 ) * 100 ) . 'vw, ' . round( $max_size, 4 ) . 'rem )';
		}

		return $fluid_font_size_preset;
	}
}

if ( ! function_exists( 'ukai_add_font_size_preset' ) ) {
	/**
	 * Add font size preset to theme.json
	 *
	 * @param WP_Theme_JSON $theme_json Theme JSON object.
	 * @return WP_Theme_JSON
	 */
	function ukai_add_font_size_preset( $theme_json ) {
		$font_presets = ukai_generate_fluid_font_preset();

		$new_font_sizes = [];

		foreach ( $font_presets as $key => $value ) {
			$label            = strval( ( $key >= 0 ) ? $key : '-' . abs( $key ) );
			$new_font_sizes[] = array(
				'size' => $value,
				'name' => strtoupper( $label ),
				'slug' => $label,
			);
		}

		$new_data = array(
			'version'  => 2,
			'settings' => array(
				'typography' => array(
					'fontSizes' => $new_font_sizes,
				),
			),
		);

		$theme_json->update_with( $new_data );

		return $theme_json;
	}

	add_filter( 'wp_theme_json_data_theme', 'ukai_add_font_size_preset' );
}
