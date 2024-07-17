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
	 * Generate fluid font size preset and clump formula
	 *
	 * @return array
	 */
	function ukai_generate_fluid_font_preset() {
		/**
		 * [harmonic sequence samples]
		 * - Pythagorean Comma: 1.0136
		 * - Minor Second: 1.067
		 * - Major Second:  1.125
		 * - Napoleon Ratio: 1.155
		 * - Minor Third: 1.2
		 * - Major Third: 1.25
		 * - Perfect Fourth: 1.333
		 * - Augmented Fourth: 1.406
		 * - Augmented Fourth: 1.406
		 * - Perfect Fifth: 1.5
		 * - Minor Sixth: 1.6
		 * - Golden Ratio: 1.618
		 * - Major Sixth: 1.667
		 * - Minor Seventh: 1.778
		 * - Major Seventh: 1.875
		 */
		$fluid_typography_min_ratio          = apply_filters( 'ukai_fluid_typography_min_ratio', 1.155 );
		$fluid_typography_max_ratio          = apply_filters( 'ukai_fluid_typography_max_ratio', 1.2 );
		$fluid_typography_min_base           = apply_filters( 'ukai_fluid_typography_min_base', 1 );
		$fluid_typography_max_base           = apply_filters( 'ukai_fluid_typography_max_base', 1.25 );
		$fluid_typography_min_viewport_width = apply_filters( 'ukai_fluid_typography_min_viewport_width', UKAI_MIN_VIEWPORT_WIDTH );
		$fluid_typography_max_viewport_width = apply_filters( 'ukai_fluid_typography_max_viewport_width', UKAI_MAX_VIEWPORT_WIDTH );
		$fluid_typography_before_default     = min( apply_filters( 'ukai_fluid_typography_before_default', 3 ), 3 );
		$fluid_typography_after_default      = min( apply_filters( 'ukai_fluid_typography_after_default', 10 ), 10 );

		$min_font_size = [];
		$max_font_size = [];

		for ( $i = $fluid_typography_before_default; $i >= 1; $i -- ) {
			$min_font_size[] = $fluid_typography_min_base / pow( $fluid_typography_min_ratio, $i );
			$max_font_size[] = $fluid_typography_max_ratio / pow( $fluid_typography_max_ratio, $i );
		}

		$min_font_size[] = $fluid_typography_min_base;
		$max_font_size[] = $fluid_typography_max_base;

		for ( $i = 1; $i <= $fluid_typography_after_default; $i ++ ) {
			$min_font_size[] = $fluid_typography_min_base * pow( $fluid_typography_min_ratio, $i );
			$max_font_size[] = $fluid_typography_max_base * pow( $fluid_typography_max_ratio, $i );
		}

		$fluid_font_size_preset = [];
		$size_labels            = [ 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL', '8XL', '9XL', '10XL', '11XL' ];
		$size_labels            = array_slice( $size_labels, 0, $fluid_typography_before_default + 1 + $fluid_typography_after_default );

		foreach ( $min_font_size as $index => $min_size ) {
			if ( ! isset( $size_labels[ $index ] ) ) {
				continue;
			}
			$max_size = $max_font_size[ $index ];

			$slope = ( $max_size - $min_size ) / ( $fluid_typography_max_viewport_width - $fluid_typography_min_viewport_width );

			$y_intersection = ( - 1 * $fluid_typography_min_viewport_width ) * $slope + $min_size;

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
			'version'  => 3,
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
