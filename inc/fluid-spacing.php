<?php
/**
 * inc/fluid-spacing.php
 *
 * @package ukai
 * @author Olein-jp
 * @since 1.0.0
 */

if ( ! function_exists( 'ukai_generate_fluid_spacing' ) ) {
	/**
	 * Generate fluid spacing preset and clamp formula
	 *
	 * @return array
	 */
	function ukai_generate_fluid_spacing() {
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
		$fluid_spacing_ratio              = apply_filters( 'ukai_fluid_spacing_ratio', 1.25 );
		$fluid_spacing_base_size          = apply_filters( 'ukai_fluid_spacing_base_size', 0.625 );
		$fluid_spacing_min_viewport_width = apply_filters( 'ukai_fluid_spacing_min_viewport_width', UKAI_MIN_VIEWPORT_WIDTH );
		$fluid_spacing_max_viewport_width = apply_filters( 'ukai_fluid_spacing_max_viewport_width', UKAI_MAX_VIEWPORT_WIDTH );
		$fluid_spacing_steps              = min( apply_filters( 'ukai_fluid_spacing_after_default', 6 ), 10 );

		$fluid_spacing_min           = [];
		$fluid_spacing_max           = [];
		$fluid_spacing_preset        = [];
		$fluid_spacing_preset_labels = [ 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL' ];

		for ( $i = 1; $i <= $fluid_spacing_steps; $i++ ) {
			$label                       = $fluid_spacing_preset_labels[ $i - 1 ];
			$fluid_spacing_min[ $label ] = $fluid_spacing_base_size * $i;
			$fluid_spacing_max[ $label ] = ( $fluid_spacing_base_size * $i ) * $fluid_spacing_ratio;

			$slope = ( $fluid_spacing_max[ $label ] - $fluid_spacing_min[ $label ] ) / ( $fluid_spacing_max_viewport_width - $fluid_spacing_min_viewport_width );

			$y_intersection = ( - 1 * $fluid_spacing_min_viewport_width ) * $slope + $fluid_spacing_min[ $label ];

			$fluid_spacing_clamp_formula = 'clamp( ' . round( $fluid_spacing_min[ $label ], 4 ) . 'rem, ' . round( $y_intersection, 4 ) . 'rem + ' . ( round( $slope, 6 ) * 100 ) . 'vw, ' . round( $fluid_spacing_max[ $label ], 4 ) . 'rem )';

			$fluid_spacing_preset[ $label ] = $fluid_spacing_clamp_formula;

		}

		return $fluid_spacing_preset;

	}
}

if ( ! function_exists( 'ukai_add_spacing_preset' ) ) {
	/**
	 * Add spacing preset to theme.json
	 *
	 * @param WP_Theme_JSON $theme_json Theme JSON object.
	 * @return WP_Theme_JSON
	 */
	function ukai_add_spacing_preset( $theme_json ) {
		$font_presets = ukai_generate_fluid_spacing();

		$new_spacings = [];

		foreach ( $font_presets as $key => $value ) {
			$label          = strval( ( $key >= 0 ) ? $key : '-' . abs( $key ) );
			$new_spacings[] = array(
				'size' => $value,
				'name' => strtoupper( $label ),
				'slug' => $label,
			);
		}

		$new_data = array(
			'version'  => 3,
			'settings' => array(
				'spacing' => array(
					'spacingSizes' => $new_spacings,
				),
			),
		);

		$theme_json->update_with( $new_data );

		return $theme_json;
	}

	add_filter( 'wp_theme_json_data_theme', 'ukai_add_spacing_preset' );
}
