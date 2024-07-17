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
		$fluid_spacing_ratio = apply_filters( 'ukai_fluid_spacing_ratio', 1.25 );
		/**
		 * [base size] Baseline spacing value
		 * Default : 0.625 (10px)(float)
		 */
		$fluid_spacing_base_size = apply_filters( 'ukai_fluid_spacing_base_size', 0.625 );
		/**
		 * [viewport width] Minimum and maximum viewport width. To make changes from the base, please use hooks to the constants.
		 */
		$fluid_spacing_min_viewport_width = apply_filters( 'ukai_fluid_spacing_min_viewport_width', UKAI_MIN_VIEWPORT_WIDTH );
		$fluid_spacing_max_viewport_width = apply_filters( 'ukai_fluid_spacing_max_viewport_width', UKAI_MAX_VIEWPORT_WIDTH );
		/**
		 * [steps] Number of steps to generate. The default is 6, with a maximum of 10.
		 */
		$fluid_spacing_steps = min( apply_filters( 'ukai_fluid_spacing_after_default', 8 ), 10 );
		/**
		 * [multiple samples] Multiple values to generate the next value.
		 * Default : 'double_repeatedly'(string))
		 * - double_repeatedly: 1, 2, 4, 8, 16, 32, 64, 128, 256, 512
		 * You can also use a numeric value to multiply the previous value.
		 * - 1.25: 1, 1.25, 1.5625, 1.953125, 2.44140625
		 */
		$fluid_spacing_multiple = apply_filters( 'ukai_fluid_spacing_multiple', 'double_repeatedly' );

		$fluid_spacing_min           = [];
		$fluid_spacing_max           = [];
		$fluid_spacing_preset        = [];
		$fluid_spacing_preset_labels = [ 's', 'm', 'l', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl' ];

		for ( $i = 1; $i <= $fluid_spacing_steps; $i ++ ) {
			$label = $fluid_spacing_preset_labels[ $i - 1 ];
			if ( 'double_repeatedly' === $fluid_spacing_multiple ) {
				$fluid_spacing_min[ $label ] = $fluid_spacing_base_size * $i;
				$fluid_spacing_max[ $label ] = ( $fluid_spacing_base_size * $i ) * $fluid_spacing_ratio;
			} elseif ( is_numeric( $fluid_spacing_multiple ) ) {
				if ( $i > 1 ) {
					$previous_label              = $fluid_spacing_preset_labels[ $i - 2 ];
					$fluid_spacing_min[ $label ] = $fluid_spacing_min[ $previous_label ] * $fluid_spacing_multiple;
					$fluid_spacing_max[ $label ] = $fluid_spacing_min[ $label ] * $fluid_spacing_ratio;
				} else {
					$fluid_spacing_min[ $label ] = $fluid_spacing_base_size;
					$fluid_spacing_max[ $label ] = $fluid_spacing_base_size * $fluid_spacing_ratio;
				}
			}

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
	 *
	 * @return WP_Theme_JSON
	 */
	function ukai_add_spacing_preset( $theme_json ) {
		$font_presets = ukai_generate_fluid_spacing();

		$new_spacings = [];

		foreach ( $font_presets as $key => $value ) {
			$label          = $key;
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
