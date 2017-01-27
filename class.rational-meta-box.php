<?php
/**
 * Rational Meta Box Class
 *
 * Class for generating WordPress meta boxes
 * using multidimensional, associative arrays.
 */
class RationalMetaBoxes {
	/**
	 * Sample fields in one meta box
	 *
	 * More meta boxes can be added by passing more arrays at the same level
	 * as 'sample-box-one' below.
	 */
	private $boxes = array(
		'sample-box-one'	=> array(
			'title'			=> 'Sample Box One',
			'screen'		=> 'post',
			'description'	=> 'A description of what sample box one is all about.',
			'fields'		=> array(
				'sample-checkbox'	=> array(
					'type'			=> 'checkbox',
					'label'			=> 'Sample Checkbox',
					'description'	=> 'Description is used as the label for checkboxes',
					'other'			=> array(
						'checked'	=> true
					),
				),
				'sample-color'		=> array(
					'type'			=> 'color',
					'label'			=> 'Sample Color',
					'value'			=> '#006699',
				),
				'sample-date'		=> array(
					'type'			=> 'date',
					'label'			=> 'Sample Date',
					'value'			=> '11/5/1605',
				),
				'sample-datetime'	=> array(
					'type'			=> 'datetime-local',
					'label'			=> 'Sample Datetime',
					'description'	=> 'Descriptive text.',
					'value'			=> 'Nov 5, 1605 2:05AM',
				),
				'sample-email'		=> array(
					'type'			=> 'email',
					'label'			=> 'Sample Email',
					'other'			=> array(
						'placeholder'	=> 'sample@email.com',
					),
				),
				'sample-file'		=> array(
					'type'			=> 'file',
					'label'			=> 'Sample File',
					'description'	=> 'Uses WordPress\' media uploader to handle files.',
				),
				'sample-number'		=> array(
					'type'			=> 'number',
					'label'			=> 'Sample Number',
					'other'			=> array(
						'placeholder'	=> 'Eg. 12345',
					),
				),
				'sample-password'	=> array(
					'type'			=> 'password',
					'label'			=> 'Sample Password Field',
				),
				'sample-radio'		=> array(
					'type'			=> 'radio',
					'label'			=> 'Sample Radio',
					'value'			=> 'option-two',
					'options'		=> array(
						'Option One <code>\'Option One\'</code>',
						'option-two' => 'Option Two <code>\'option-two\' => \'Option Two\'</code>',
					),
				),
				'sample-range'		=> array(
					'type'			=> 'range',
					'label'			=> 'Sample Range',
					'value'			=> 20,
					'other'			=> array(
						'min'	=> 10,
						'max'	=> 100,
						'step'	=> 10,
					),
				),
				'sample-search'		=> array(
					'type'			=> 'search',
					'label'			=> 'Sample Search',
				),
				'sample-select'		=> array(
					'type'			=> 'select',
					'label'			=> 'Sample Select',
					'options'		=> array(
						'Choose One&hellip;',
						'Option One',
						'option-two' => 'Option Two',
					),
				),
				'sample-tel'		=> array(
					'type'			=> 'tel',
					'label'			=> 'Sample Telephone',
				),
				'sample-textarea'	=> array(
					'type'			=> 'textarea',
					'label'			=> 'Sample Textarea',
					'other'			=> array(
						'char_count'	=> 155,
						'rows'			=> 5,
					),
				),
				'sample-text'		=> array(
					'type'			=> 'text',
					'label'			=> 'Sample Text Field',
				),
				'sample-time'		=> array(
					'type'			=> 'time',
					'label'			=> 'Sample Time',
					'value'			=> '3:50PM',
				),
				'sample-url'		=> array(
					'type'			=> 'url',
					'label'			=> 'Sample URL',
					'other'			=> array(
						'placeholder'	=> 'http://jeremyhixon.com',
					),
				),
			),
		),
	);
	/**
	 * Text domain
	 */
	private $domain = 'rationalmeta';
	
	/**
	 * Class construct method
	 *
	 * @param	array	$boxes	Multidimensional, associative array of meta box parameters, sections and fields
	 * @param	string	$domain	Text domain
	 */
	public function __construct( $boxes = false, $domain = false ) {
		// replacing default meta box with custom array
		if ( $boxes && count( $boxes ) > 0 ) {
			$this->boxes = $boxes;
		}
		// replacing default textdomain with custom one
		if ( $domain && !empty( $domain ) ) {
			$this->domain = $domain;
		}
		
		// If the current meta boxes make use of the "file" type then we need to make
		// sure that the proper scripts and styles are queued to make use of the media
		// uploader
		if ( count( $this->search( $this->boxes, 'type', 'file' ) ) > 0 ) {
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_register_script( 'meta-box-upload', 'js/meta-box-upload.js', array( 'jquery', 'media-upload', 'thickbox' ) );
			wp_enqueue_script( 'meta-box-upload' );
		}
		
		// If there are any moxes that call for the character count
		if ( count( $this-> search( $this->boxes, 'char_count' ) ) > 0 ) {
			wp_register_style( 'char-count-css', 'css/char-count.css' );
			wp_enqueue_style( 'char-count-css' );
			wp_enqueue_script( 'jquery' );
			wp_register_script( 'char-count-js', 'js/char-count.js', array( 'jquery' ) );
			wp_enqueue_script( 'char-count-js' );
		}
		
		add_action( 'save_post', array( $this, 'save' ) );
	}
	
	/**
	 * Class call method. Intercepts class requests to handle callbacks dynamically
	 *
	 * @param	string	$func	Requested method name
	 * @param	array	$params	Array of paramters passed to the requested method
	 */
	public function __call( $func, $params ) {
		// Break apart request at '_' because the assembled callbacks are simply the meta box
		// id with '_callback' appended to it. We're left with the id to use as a selector in
		// the 'meta_box_callback' method.
		$req_parts = explode( '_', $func );
		if ( count( $req_parts ) > 2 ) {
			$type = array_shift( $req_parts );
			$id = implode('_', $req_parts);
		} else {
			$type = $req_parts[0];
			$id = $req_parts[1];
		}
		switch ( $type ) {
			case 'box':
				$this->meta_box_callback( $id, $params[0] );
				break;
		}
	}
	
	/**
	 * Save function for sanitizing and storing fields
	 *
	 * @param string $post_id	The current post's ID
	 */
	public function save( $post_id ) {
		if ( isset( $_POST['rational_meta_id'] ) ) {
			$ids = $_POST['rational_meta_id'];
			foreach ( $ids as $id ) {
				// validating
				if ( ! isset( $_POST[$id . '_nonce'] ) )
					return $post_id;
				if ( ! wp_verify_nonce( $_POST[$id . '_nonce'], $id ) )
					return $post_id;
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
					return $post_id;
				if ( $_POST['post_type'] === 'page'  ) {
					if ( ! current_user_can( 'edit_page', $post_id ) )
						return $post_id;
				} else {
					if ( ! current_user_can( 'edit_post', $post_id ) )
						return $post_id;
				}
				// processing
				if ( isset( $this->boxes[$id]['fields'] ) ) {
					foreach ( $this->boxes[$id]['fields'] as $field_id => $field_atts ) {
						if ( isset( $_POST[$field_id] ) && !empty( $_POST[$field_id] ) ) {
							switch ( $field_atts['type'] ) {
								case 'email':
									$value = sanitize_email( $_POST[$field_id] );
									break;
								case 'text':
									$value = sanitize_text_field( $_POST[$field_id] );
									break;
								case 'url':
									$value = esc_url_raw( $_POST[$field_id] );
									break;
								default:
									$value = $_POST[$field_id];
							}
							update_post_meta( $post_id, $field_id, $value );
						} else if ( empty( $_POST[$field_id] && $field_atts['type'] === 'checkbox' ) ) {
							// unchecked boxes don't send anything in $_POST so I provide the 'null' value manually
							update_post_meta( $post_id, $field_id, null );
						}
					}
				}
			}
		}
	}
	
	/**
	 * Manages the cosntruction of the form elements for the given meta box
	 *
	 * @param	string	$id		The id of the current meta box callback
	 * @param	object	$post	WordPress post object
	 */
	private function meta_box_callback( $id, $post ) {
		wp_nonce_field( $id, $id . '_nonce' );
		$atts = $this->boxes[$id];
		
		if ( isset( $atts['description'] ) ) {
			echo $atts['description'];
		}
		if ( isset( $atts['fields'] ) ) {
			echo '<input type="hidden" name="rational_meta_id[]" value="' . $id . '">';
			echo '<table class="form-table"><tbody>';
			foreach ( $atts['fields'] as $field_id => $atts ) {
				echo $this->generate_field( $field_id, $atts );
			}
			echo '</tbody></table>';
		}
	}
	
	/**
	 * Searches the given array for a key/value pair
	 * 
	 * @param	array	$array	The array to be searched
	 * @param	string	$key	The key to search for
	 * @param	string	$value	The value to check for on the given key
	 *
	 * @return	array	The found key/value pair (if any), empty array if nothing is found
	 */
	private function search( $array, $key, $value = false ) {
		$results = array();
		
		if ( is_array( $array ) ) {
			if (
				( isset( $array[$key] ) && $array[$key] === $value ) ||
				( !$value && isset( $array[$key] ) )
			) {
				$results[] = $array;
			}
		
			foreach ( $array as $subarray ) {
				$results = array_merge( $results, $this->search( $subarray, $key, $value ) );
			}
		}
		
		return $results;
	}
	
	/**
	 * Runs through the 'boxes' array assigning meta boxes to the given screen(s)
	 */
	public function generate_boxes() {
		foreach ( $this->boxes as $id => $atts ) {
			$context = isset( $atts['context'] ) ? $atts['context'] : 'advanced';
			$priority = isset( $atts['priority'] ) ? $atts['priority'] : 'default';
			if ( is_array( $atts['screen'] ) ) {
				foreach ( $atts['screen'] as $screen ) {
					add_meta_box(
						$id,
						__( $atts['title'], $this->domain ),
						array( $this, 'box_' . $id ),
						$screen,
						$context,
						$priority
					);
				}
			} else {
				$screen = isset( $atts['screen'] ) ? $atts['screen'] : 'post';
				add_meta_box(
					$id,
					__( $atts['title'], $this->domain ),
					array( $this, 'box_' . $id ),
					$screen,
					$context,
					$priority
				);
			}
		}
	}
	
	/**
	 * Builds the table row and inputs for the given field
	 *
	 * @param	string	$id		The 'id' of the field
	 * @param	array	$atts	The properties of the field
	 *
	 * @return	string	HTML for the table row
	 */
	private function generate_field( $id, $atts ) {
		global $post;
		if ( !isset( $atts['type'] ) ) {
			$atts['type'] = 'text';
		}
		$text_style = array( 'date', 'datetime', 'datetime-local', 'email', 'number', 'password', 'search', 'tel', 'text', 'time', 'url' );
		$stored_value = get_post_meta( $post->ID, $id, true );
		$html  = '<tr>';
		$html .= '<th>';
		$html .= $this->generate_label( $id, $atts );
		$html .= '</th>';
		$html .= '<td>';
		// compile
		$autocomplete = '';
		if  ( isset( $atts['other']['autocomplete'] ) ) {
			$autocomplete = 'autocomplete="' . $atts['other']['autocomplete'] . '"';
		}
		$autofocus = '';
		if ( isset( $atts['other']['autofocus'] ) && $atts['other']['autofocus'] ) {
			$autofocus = 'autofocus';
		}
		$char_count = '';
		if ( isset( $atts['other']['char_count'] ) && !empty( $atts['other']['char_count'] ) && $atts['other']['char_count'] ) {
			$char_count = 'data-char-count="' . intval( $atts['other']['char_count'] ) . '"';
		}
		$class = '';
		if ( isset( $atts['other']['class'] ) ) {
			$class = $atts['other']['class'];
		} else if ( in_array( $atts['type'], $text_style) ) {
			$class = 'regular-text';
		} else if ( $atts['type'] === 'textarea' ) {
			$class = 'large-text';
		}
		$cols = '';
		if ( isset( $atts['other']['cols'] ) ) {
			$cols = 'cols="' . $atts['other']['cols'] . '"';
		}
		$description = '';
		if ( isset( $atts['description'] ) ) {
			if ( $atts['type'] !== 'checkbox' ) {
				$description = '<p class="description">' . $atts['description'] . '</p>';
			} else {
				$description = $atts['description'];
			}
		}
		$disabled = '';
		if ( isset( $atts['other']['disabled'] ) && $atts['other']['disabled'] ) {
			$disabled = 'disabled';
		}
		$max = '';
		if ( isset( $atts['other']['max'] ) ) {
			switch ( $atts['type'] ) {
				case 'date':
					$max = 'max="' . date( 'Y-m-d', strtotime( $atts['other']['max'] ) ) . '"';
					break;
				case 'datetime':
				case 'datetime-local':
					$max = 'max="' . date( 'Y-m-d\TH:i:s', strtotime( $atts['other']['max'] ) ) . '"';
					break;
				case 'month':
					$max = 'max="' . date( 'Y-m', strtotime( $atts['other']['max'] ) ) . '"';
					break;
				case 'time':
					$max = 'max="' . date( 'H:i:s', strtotime( $atts['other']['max'] ) ) . '"';
					break;
				case 'week':
					$max = 'max="' . date( 'Y-\WW', strtotime( $atts['other']['max'] ) ) . '"';
					break;
				default:
					$max = 'max="' . $atts['other']['max'] . '"';
			}
		}
		$maxlength = ( isset( $atts['other']['maxlength'] ) ) ? 'maxlength="' . $atts['other']['maxlength'] . '"' : '';
		$min = '';
		if ( isset( $atts['other']['min'] ) ) {
			switch ( $atts['type'] ) {
				case 'date':
					$min = 'min="' . date( 'Y-m-d', strtotime( $atts['other']['min'] ) ) . '"';
					break;
				case 'datetime':
				case 'datetime-local':
					$min = 'min="' . date( 'Y-m-d\TH:i:s', strtotime( $atts['other']['min'] ) ) . '"';
					break;
				case 'month':
					$min = 'min="' . date( 'Y-m', strtotime( $atts['other']['min'] ) ) . '"';
					break;
				case 'time':
					$min = 'min="' . date( 'H:i:s', strtotime( $atts['other']['min'] ) ) . '"';
					break;
				case 'week':
					$min = 'min="' . date( 'Y-\WW', strtotime( $atts['other']['min'] ) ) . '"';
					break;
				default:
					$min = 'min="' . $atts['other']['min'] . '"';
			}
		}
		$pattern = ( isset( $atts['other']['pattern'] ) ) ? 'pattern="' . $atts['other']['pattern'] . '"' : '';
		$placeholder = '';
		if ( isset( $atts['other']['placeholder'] ) ) {
			$placeholder = 'placeholder="' . $atts['other']['placeholder'] . '"';
		}
		$readonly = '';
		if ( isset( $atts['other']['readonly'] ) && $atts['other']['readonly'] ) {
			$readonly = 'readonly';
		}
		$required = '';
		if ( isset( $atts['other']['required'] ) && $atts['other']['required'] ) {
			$required = 'required';
		}
		$rows = '';
		if ( isset( $atts['other']['rows'] ) ) {
			$rows = 'rows="' . $atts['other']['rows'] . '"';
		}
		$step = ( isset( $atts['other']['step'] ) ) ? 'step="' . $atts['other']['step'] . '"' : '';
		$value = '';
		if ( $stored_value ) {
			if ( isset( $atts['value'] ) && $atts['type'] === 'checkbox' ) {
				$value = $atts['value'];
			} else if ( !isset( $atts['value'] ) && $atts['type'] === 'checkbox' ) {
				$value = 'yes';
			} else {
				$value = $this->get_special_format( $atts['type'], $stored_value );
			}
		} else if ( isset( $atts['value'] ) ) {
			$value = $this->get_special_format( $atts['type'], $atts['value'] );
		} else if ( $atts['type'] === 'checkbox' ) {
			$value = 'yes';
		}
		$checked = '';
		if ( $stored_value && $stored_value === $value && ( ( isset( $atts['value'] ) && $stored_value === $atts['value'] ) || ( !isset( $atts['value'] ) && $stored_value === 'yes' ) ) ) {
			$checked = 'checked';
		} else if ( isset( $atts['other']['checked'] ) && $atts['other']['checked'] ) {
			$checked = 'checked';
		}
		// assign
		switch ( $atts['type'] ) {
			case 'file':
				$html .= sprintf(
					'<input class="%s rational-upload-field" type="text" name="%s" id="%s" value="%s" %s> <input class="button rational-upload-button" type="button" name="%s" id="%s" value="Add or Upload" %s>%s',
					$class,
					$id,
					$id,
					$value,
					$required,
					$id . '-button',
					$id . '-button',
					$autofocus,
					$description
				);
				break;
			case 'checkbox':
				$html .= sprintf(
					'<label><input class="%s" type="checkbox" name="%s" id="%s" value="%s" %s %s %s %s> %s</label>',
					$class,
					$id,
					$id,
					$value,
					$autofocus,
					$checked,
					$disabled,
					$required,
					$description
				);
				break;
			case 'color':
				$html .= sprintf(
					'<input class="%s" type="color" name="%s" id="%s" value="%s" %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$autofocus,
					$disabled,
					$readonly,
					$required,
					$description
				);
				break;
			case 'date':
			case 'month':
			case 'week':
				$html .= sprintf(
					'<input class="%s" type="%s" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s>%s',
					$class,
					$atts['type'],
					$id,
					$id,
					$value,
					$min,
					$max,
					$autocomplete,
					$autofocus,
					$disabled,
					$readonly,
					$required,
					$description
				);
				break;
			case 'datetime':
			case 'datetime-local':
				$html .= sprintf(
					'<input class="%s" type="%s" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s>%s',
					$class,
					$atts['type'],
					$id,
					$id,
					$value,
					$min,
					$max,
					$autocomplete,
					$autofocus,
					$disabled,
					$readonly,
					$required,
					$description
				);
				break;
			case 'email':
				$html .= sprintf(
					'<input class="%s" type="email" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$placeholder,
					$autocomplete,
					$autofocus,
					$disabled,
					$maxlength,
					$pattern,
					$readonly,
					$required,
					$char_count,
					$description
				);
				break;
			case 'number':
				$html .= sprintf(
					'<input class="%s" type="number" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$min,
					$max,
					$step,
					$placeholder,
					$autocomplete,
					$autofocus,
					$disabled,
					$maxlength,
					$pattern,
					$readonly,
					$required,
					$char_count,
					$description
				);
				break;
			case 'password':
				$html .= sprintf(
					'<input class="%s" type="password" name="%s" id="%s" value="%s" %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$autofocus,
					$disabled,
					$maxlength,
					$placeholder,
					$required,
					$char_count,
					$description
				);
				break;
			case 'radio':
				$html .= '<br>';
				$html .= '<fieldset>';
				$html .= '<legend class="screen-reader-text">' . $atts['label'] . '</legend>';
				$i = 1;
				foreach ( $atts['options'] as $key => $value ) {
					if ( count(array_filter(array_keys($atts['options']), 'is_string')) > 0 ) {
						$input_value = $key;
					} else {
						$input_value = $this->slugify( $value );
					}
					$checked = '';
					if ( $stored_value && $stored_value === $input_value ) {
						$checked = 'checked';
					} else if ( isset( $atts['value'] ) && strval( $atts['value'] ) === $input_value ) {
						$checked = 'checked';
					}
					$html .= sprintf(
						'<label><input class="%s" type="radio" name="%s" id="%s" value="%s" %s %s %s %s> %s</label>',
						$class,
						$id,
							$id . '-' . $i,
						$input_value,
						$autofocus,
						$disabled,
						$required,
						$checked,
						$value
					);
					if ( $i < count( $atts['options'] ) ) {
						$html .= '<br>';
					}
					$i++;
				}
				$html .= '</fieldset>';
				break;
			case 'range':
				$html .= sprintf(
					'<input class="%s" type="range" name="%s" id="%s" value="%s" %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$min,
					$max,
					$step,
					$autofocus,
					$disabled,
					$description
				);
				break;
			case 'search':
				$html .= sprintf(
					'<input class="%s" type="search" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$placeholder,
					$autocomplete,
					$autofocus,
					$disabled,
					$maxlength,
					$pattern,
					$readonly,
					$required,
					$char_count,
					$description
				);
				break;
			case 'select':
				$html .= sprintf(
					'<select class="%s" name="%s" id="%s" %s %s %s>',
					$class,
					$id,
					$id,
					$autofocus,
					$disabled,
					$required
				);
				$j = 1;
				foreach ( $atts['options'] as $key => $value ) {
					if ( count(array_filter(array_keys($atts['options']), 'is_string')) > 0 ) {
						$option_value = $key;
					} else {
						$option_value = $this->slugify( $value );
					}
					$selected = '';
					if ( $stored_value && $stored_value === $option_value ) {
						$selected = 'selected';
					} else if ( isset( $atts['value'] ) && strval( $atts['value'] ) === $option_value ) {
						$selected = 'selected';
					}
					$html .= sprintf(
						'<option value="%s" %s>%s</option>',
						$option_value,
						$selected,
						$value
					);
					$j++;
				}
				$html .= '</select>' . $description;
				break;
			case 'tel':
				$html .= sprintf(
					'<input class="%s" type="tel" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$placeholder,
					$autocomplete,
					$autofocus,
					$disabled,
					$maxlength,
					$pattern,
					$readonly,
					$required,
					$char_count,
					$description
				);
				break;
			case 'text':
				$html .= sprintf(
					'<input class="%s" type="text" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$placeholder,
					$autocomplete,
					$autofocus,
					$disabled,
					$maxlength,
					$pattern,
					$readonly,
					$required,
					$char_count,
					$description
				);
				break;
			case 'time':
				$html .= sprintf(
					'<input class="%s" type="time" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$min,
					$max,
					$autocomplete,
					$autofocus,
					$disabled,
					$readonly,
					$required,
					$description
				);
				break;
			case 'textarea':
				$html .= sprintf(
					'<textarea class="%s" name="%s" id="%s" %s %s %s %s %s %s %s %s %s>%s</textarea>%s',
					$class,
					$id,
					$id,
					$placeholder,
					$autofocus,
					$cols,
					$disabled,
					$maxlength,
					$readonly,
					$required,
					$rows,
					$char_count,
					$value,
					$description
				);
				break;
			case 'url':
				$html .= sprintf(
					'<input class="%s" type="text" name="%s" id="%s" value="%s" %s %s %s %s %s %s %s %s %s>%s',
					$class,
					$id,
					$id,
					$value,
					$placeholder,
					$autocomplete,
					$autofocus,
					$disabled,
					$maxlength,
					$pattern,
					$readonly,
					$required,
					$char_count,
					$description
				);
				break;
		}
		$html .= '</td></tr>';
		return $html;
	}
	
	/**
	 * Builds the label for the field input
	 * 
	 * @param	string	$id		The id of the field
	 * @param	array	$atts	Attributes of the field
	 *
	 * @return	string	Label text wrapped in a label element for fields that are friendly to labels,
	 *					just the label text for those that are not.
	 */
	private function generate_label( $id, $atts ) {
		$label_unfriendly = array( 'file', 'radio', 'range' );
		if ( !in_array( $atts['type'], $label_unfriendly ) ) {
			return '<label for="' . $id . '">' . $atts['label'] . '</label>';
		} else {
			return $atts['label'];
		}
	}
	
	/**
	 * Gets a more usable format of a date/time value for HTML5 input elements
	 *
	 * @param	string	$type	The input's type
	 * @param	string	$value	The value to be formatted
	 *
	 * @return	string	The formatted date/time string
	 */
	private function get_special_format( $type, $value ) {
		switch ( $type ) {
			case 'date':
				$value = date( 'Y-m-d', strtotime( $value ) );
				break;
			case 'datetime':
			case 'datetime-local':
				$value = date( 'Y-m-d\TH:i:s', strtotime( $value ) );
				break;
			case 'month':
				$value = date( 'Y-m', strtotime( $value ) );
				break;
			case 'time':
				$value = date( 'H:i:s', strtotime( $value ) );
				break;
			case 'week':
				$value = date( 'Y-\WW', strtotime( $value ) );
				break;
		}
		return $value;
	}

	/**
	 * Adjusts text to a more 'machine-readable' format
	 *
	 * @param	string	$text Text to be adjusted
	 *
	 * @return	string	More 'machine-readable' text
	 */
	private function slugify( $text ) {
		// remove html tags
		//$text = strip_tags( $text );
		$text = preg_replace('/<[a-z]*[^>]*?>.*?<\/[a-z]*>/i', '', $text);
		// decode entities
		$text = html_entity_decode( $text );
		// replace non letter or digits by -
		$text = preg_replace( '~[^\\pL\d]+~u', '-', $text );
		// trim
		$text = trim( $text, '-' );
		// transliterate
		$text = iconv( 'utf-8', 'us-ascii//TRANSLIT', $text );
		// lowercase
		$text = strtolower( $text );
		// remove unwanted characters
		$text = preg_replace( '~[^-\w]+~', '', $text );
		if ( empty( $text ) )  {
			return 'n-a';
		}
		return $text;
	}
}
