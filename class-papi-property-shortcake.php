<?php

/**
 * Property that integrates Shortcake with Papi.
 */
class Papi_Property_Shortcake extends Papi_Property {

	/**
	 * Output CSS in admin head.
	 */
	public function css() {
		?>
		<style type="text/css">
			.papi-shortcake button.edit-media > span,
			.papi-shortcake button.remove-media > span {
				margin-top: -2px;
			}

			.papi-shortcake-container {
				float: left;
				margin-top: 10px;
				overflow-x: hidden;
				width: 100%;
			}

			.papi-shortcake-container > h3 {
				padding: 8px 0;
			}

			.papi-shortcake-container-inner {
				margin-bottom: 20px;
			}

			.papi-shortcake-container-inner iframe,
			.papi-shortcake-container-inner object,
			.papi-shortcake-container-inner embed {
				border: 1px #eaeaea solid;
				max-width: 100%;
				width: 100%;
			}
		</style>
		<?php
	}

	/**
	 * Format the value of the property before it's returned
	 * to WordPress admin or the site.
	 *
	 * @param  mixed  $value
	 * @param  string $slug
	 * @param  int    $post_id
	 *
	 * @return bool
	 */
	public function format_value( $value, $slug, $post_id ) {
		return is_admin() ? $value : do_shortcode( $value );
	}

	/**
	 * Get shortcode title.
	 *
	 * @param  string $shortcode
	 *
	 * @return string
	 */
	protected function get_title( $shortcode ) {
		$shortcodes = ShortCode_UI::get_instance()->get_shortcodes();
		$shortcode = shortcode_parse_atts( $shortcode );
		$shortcode = is_array( $shortcode ) && isset( $shortcode[0] ) ? $shortcode[0] : '';
		$shortcode = substr( $shortcode, 1 );

		if ( isset( $shortcodes[$shortcode] ) ) {
			return $shortcodes[$shortcode]['label'];
		}
	}

	/**
	 * Render property html.
	 */
	public function html() {
		$value = $this->get_value();
		$title = $this->get_title( $value );
		?>

		<div class="papi-shortcake">
			<div id="wp-content-media-buttons" class="wp-media-buttons">
				<button type="button" class="button insert-media add_media">
					<span class="wp-media-buttons-icon"></span>
					<?php esc_html_e( 'Add' ); ?>
				</button>

				<button type="button" class="button edit-media edit_media insert-media <?php echo $value ? '' : 'papi-hide'; ?>">
					<span class="wp-media-buttons-icon">
						<i class="mce-i-dashicon dashicons-edit"></i>
					</span>
					<?php esc_html_e( 'Edit' ); ?>
				</button>

				<button type="button" class="button remove-media remove_media <?php echo $value ? '' : 'papi-hide'; ?>">
					<span class="wp-media-buttons-icon">
						<i class="mce-i-dashicon dashicons-no"></i>
					</span>
					<?php esc_html_e( 'Remove' ); ?>
				</button>
			</div>

			<input type="hidden" name="<?php esc_attr_e( $this->html_name() ); ?>" value="<?php esc_attr_e( $value ); ?>">

			<div class="papi-shortcake-container">
				<?php if ( $title ): ?>
					<h3><?php esc_html_e( $title ); ?></h3>
				<?php endif; ?>

				<div class="papi-shortcake-container-inner <?php echo $value ? '' : 'papi-hide'; ?>">
					<?php echo do_shortcode( $value ); // WPCS: xss ok ?>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Output JavaScript in admin footer.
	 */
	public function js() {
		?>
		<script>
			/**
			 * Get shortcode with shortcake attributes.
			 *
			 * @param  {string} shortcode
			 *
			 * @return {object}
			 */
			function getShortcode(shortcode) {
				shortcode = parseShortcode(shortcode);

				for (var i = 0, l = sui.shortcodes.models.length; i < l; i++) {
					if (sui.shortcodes.models[i].attributes.shortcode_tag === shortcode.tag) {
						shortcode.attributes = sui.shortcodes.models[i].attributes;
						return shortcode;
					}
				}
			}

			/**
			 * Parse shortcode.
			 *
			 * @param  {string} shortcode
			 *
			 * @return {object}
			 */
			function parseShortcode(shortcode) {
				var shortcodeTags = _.map( sui.shortcodes.pluck( 'shortcode_tag' ), pregQuote ).join( '|' );
				var regexp = wp.shortcode.regexp( shortcodeTags );
				regexp.lastIndex = 0;
				return wp.shortcode.fromMatch( regexp.exec( shortcode ) );
			}

			/**
			 * Escape any special characters in a string to be used as a regular expression.
			 *
			 * JavaScript version of PHP's `preg_quote`.
			 *
			 * @see http://phpjs.org/functions/preg_quote/
			 *
			 * @param  {string} str
			 * @param  {string} delimiter
			 * @return {string}
			 */
			function pregQuote(str, delimiter) {
				return String(str).replace(
					new RegExp( '[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + ( delimiter || '' ) + '-]', 'g' ),
					'\\$&'
				);
			}

			// Insert media element event.
			jQuery(document).on('click', '.papi-shortcake button.insert-media', function(e) {
				e.preventDefault();

				var $this = jQuery(e.currentTarget);
				var $target = $this.closest('.papi-shortcake').find('.papi-shortcake-container > div');
				var $hidden = $this.closest('.papi-shortcake').find('input[type="hidden"]');

				// Store old version of `send_to_editor`.
				window.send_to_editor_old = window.send_to_editor;

				// Remove media frame menu and modify css.
				jQuery('.media-modal-content .media-menu .separator:first').next().trigger('click').closest('.media-frame-menu').remove();
				jQuery('.media-modal-content .media-frame-title, .media-frame-content').css('left', '0px');
				jQuery('.media-modal-content .media-toolbar-primary.search-form').css('width', '12.7%');

				// In Edit mode.
				if ($this.hasClass('edit_media')) {
					var shortcode = getShortcode($hidden.val());

					jQuery('.media-modal-content .add-shortcode-list li[data-shortcode="' + shortcode.attributes.shortcode_tag + '"]').trigger('click');
					jQuery('.media-modal-content .edit-shortcode-form-cancel').remove();
					jQuery('.media-modal-content .media-frame-title h1').text(shortcode.attributes.label + ' <?php esc_html_e( 'Details' ); ?>');

					for (var key in shortcode.attrs.named) {
						if (shortcode.attrs.named.hasOwnProperty(key)) {
							jQuery('.media-modal-content .edit-shortcode-form [name="' + key + '"]').val(shortcode.attrs.named[key]);
						}
					}
				}

				// Media frame close event.
				wp.media.frame.on('close', function() {
					// Restore old `send_to_editor` if it exists.
					if (typeof window.send_to_editor_old !== 'undefined') {
						window.send_to_editor = window.send_to_editor_old;
						delete window.send_to_editor_old;
					}
				});

				// Override `send_to_editor`.
				window.send_to_editor = function(value) {
					jQuery.getJSON(papi.ajaxUrl + '?action=get_shortcode&shortcode=' + encodeURIComponent(value), function(res) {
						$target.removeClass('papi-hide').html(res.html);
						$hidden.val(value);

						if (shortcode = getShortcode(value)) {
							$target.siblings().remove();
							jQuery('<h3>' + shortcode.attributes.label + '</h3>').insertBefore($target);
						}

						// Show edit and remove buttons.
						jQuery('.papi-shortcake button.edit-media').removeClass('papi-hide');
						jQuery('.papi-shortcake button.remove-media').removeClass('papi-hide');
					});

					// Restore old `send_to_editor`.
					window.send_to_editor = window.send_to_editor_old;
					delete window.send_to_editor_old;
				};
			});

			// Edit media element event.
			jQuery(document).on('click', '.papi-shortcake button.remove-media', function(e) {
				e.preventDefault();

				var $this = jQuery(e.currentTarget);
				var $hidden = $this.closest('.papi-shortcake').find('input[type="hidden"]');
			});

			// Remove media element event.
			jQuery(document).on('click', '.papi-shortcake button.remove-media', function(e) {
				e.preventDefault();

				var $this = jQuery(e.currentTarget);
				var $prop = $this.closest('.papi-shortcake');

				// Clear container div.
				$prop.find('.papi-shortcake-container > h3').remove();
				$prop.find('.papi-shortcake-container > div').addClass('papi-hide').html('');
				$prop.find('input[type="hidden"]').val('');

				// Hide edit and remove buttons
				$prop.find('button.edit-media').addClass('papi-hide');
				$this.addClass('papi-hide');
			});
		</script>
		<?php
	}

	/**
	 * Setup actions.
	 */
	protected function setup_actions() {
		add_action( 'admin_head', [$this, 'css'] );
		add_action( 'admin_footer', [$this, 'js'] );
	}
}
