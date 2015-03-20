<?php
	/**
	 * Copyright (c) 2015, Jeremy Hixon, All rights reserved
	 * 
	 * - Get list of available styles for inputs
	 */
	class RationalMetaBox {
		
		// ==========================================================================
		// Variables
		// ==========================================================================
		private $meta_box_atts = array(
			'id'			=> 'rational-meta-box',	// WP default: None
			'title'			=> 'Rational Meta Box',	// WP default: None
			'screen'		=> 'post',				// WP default: null
			'context'		=> 'advanced',			// WP default: advanced
			'priority'		=> 'default',			// WP default: default
			'callback_args'	=> null,				// WP default: null
		);
		private $fields = array(
			array(
				'type'			=> 'text',
				'name'			=> 'text-name',
				'value'			=> 'Text, default value',
				'label'			=> 'Text Field',
				'placeholder'	=> 'Text Placeholder',
				'atts'			=> array(
					'class'	=> 'large-text',
				),
			),
			array(
				'type'			=> 'textarea',
				'name'			=> 'textarea-name',
				'value'			=> 'Textarea, default value',
				'label'			=> 'Textarea Label',
				'placeholder'	=> 'Textarea Placeholder',
				'atts'			=> array(
					'class'	=> 'large-text',
					'rows'	=> 3,
				),
			),
			array(
				'type'			=> 'select',
				'name'			=> 'select-name',
				'label'			=> 'Select Label',
				'options'		=> array(
					'null'		=> 'Choose One&hellip;',
					'Option One',
					'option-two' => 'Option Two',
				),
			),
			array(
				'type'			=> 'checkbox',
				'name'			=> 'checkbox-name',
				'value'			=> 1,
				'label'			=> 'Checkbox',
				'description'	=> 'Descriptive text for checkbox',
			),
			array(
				'type'			=> 'radio',
				'name'			=> 'radio-name',
				'label'			=> 'Radio Label',
				'options'		=> array(
					'Option One',
					'option-two' => 'Option Two',
				),
			),
		);
		private $text_domain = 'rational';
		private $stored_values = false;
		
		public function __construct() {
			add_action( 'save_post', array( $this, 'save_box' ) );
		}
		
		// ==========================================================================
		// Standard functions
		// ==========================================================================
		public function save_box( $post_id ) {
			// validate save
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
			if ( ! isset( $_POST[$this->meta_box_atts['id'] . '_nonce'] ) || ! wp_verify_nonce( $_POST[$this->meta_box_atts['id'] . '_nonce'], '_' . $this->meta_box_atts['id'] . '_nonce' ) ) return;
			if ( ! current_user_can( 'edit_post' ) ) return;
			
			// process fields
			foreach ( $this->fields as $field ) {
				if ( $field['type'] === 'checkbox') {
					if ( isset( $_POST[$field['name']] ) ) {
						update_post_meta( $post_id, $field['name'], esc_attr( $_POST[$field['name']] ) );
					} else {
						update_post_meta( $post_id, $field['name'], false );
					}
				} else {
					if ( isset( $_POST[$field['name']] ) ) {
						update_post_meta( $post_id, $field['name'], esc_attr( $_POST[$field['name']] ) );
					}
				}
			}
		}
		
		public function add_box( $atts = false, $fields = false ) {
			global $post;
			if ( $fields ) {
				$this->fields = $fields;
			}
			if ( isset( $post ) ) {
				foreach ( $this->fields as $field ) {
					$value = get_post_meta( $post->ID, $field['name'], true );
					$this->stored_values[$field['name']] = ( ! empty( $value ) ) ? $value : false;
				}
			}
			
			if ( $atts ) {
				foreach ( $atts as $att_key => $att_value ) {
					if ( isset( $this->meta_box_atts[$att_key] ) ) {
						$this->meta_box_atts[$att_key] = $att_value;
					}
				}
			}
			add_meta_box(
				$this->meta_box_atts['id'],
				$this->meta_box_atts['title'],
				array( $this, 'meta_box_callback' ),
				$this->meta_box_atts['screen'],
				$this->meta_box_atts['context'],
				$this->meta_box_atts['priority'],
				$this->meta_box_atts['callback_args']
			);
			global $wp_scripts;
		}
		
		public function meta_box_callback( $post ) {
			// nonce
			wp_nonce_field( '_' . $this->meta_box_atts['id'] . '_nonce', $this->meta_box_atts['id'] . '_nonce' );
			$output  =  "\r\n";
			$tabled = ( $this->meta_box_atts['context'] !== 'side' ) ? true : false;
			if ( $tabled ) {
				// open table
				$output .= '<table class="form-table">' . "\r\n";
				$output .= $this->tabs(1) . '<tbody>' . "\r\n";
			}
			// process fields
			foreach ( $this->fields as $field ) {
				if ( $tabled ) {
					$output .= $this->tabs(2) . '<tr>' . "\r\n";
				}
				$output .= $this->generate_label( $field, $tabled );
				$output .= $this->generate_input( $field['type'], $field, $tabled );
				if ( $tabled ) {
					$output .= $this->tabs(2) . '</tr>' . "\r\n";
				}
			}
			if ( $tabled ) {
				// close table
				$output .= $this->tabs(1) . '</tbody>' . "\r\n";
				$output .= '</table>' . "\r\n";
			}
			echo $output;
		}
		
		// ==========================================================================
		// Generators
		// ==========================================================================
		/**
		 * Generate input (all)
		 */
		private function generate_input( $type, $field, $tabled ) {
			$input_field  = $this->tabs(3);
			$input_field .= ( $tabled ) ? '<td>' : '<p>';
			switch ($type) {
				case 'textarea':
					$input_field .= sprintf( '<textarea class="%s" name="%s" id="%s" placeholder="%s" rows="%d">%s</textarea>',
						( isset( $field['atts']['class'] ) ) ? $field['atts']['class'] : 'regular-text',
						$field['name'],
						$field['name'],
						__( $field['placeholder'] ),
						( isset( $field['atts']['rows'] ) ) ? $field['atts']['rows'] : 5,
						( $this->stored_values[$field['name']] ) ? __( $this->stored_values[$field['name']] ) : __( $field['value'] )
					);
					break;
				case 'select':
					$input_field .= $this->tabs(4) . '<select name="' . $field['name'] . '" id="' . $field['name'] . '">' . "\r\n";
					foreach ($field['options'] as $option_value => $option_text) {
						$input_field .= $this->tabs(5);
						$selected = '';
						if ( is_int( $option_value ) ) {
							if ( $this->stored_values[$field['name']] && $this->stored_values[$field['name']] ===  $option_text) {
								$selected = 'selected';
							}
							$input_field .= '<option ' . $selected . '>' . $option_text . '</option>';
						} else {
							if ( $this->stored_values[$field['name']] && $this->stored_values[$field['name']] ===  $option_value) {
								$selected = 'selected';
							}
							$input_field .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_text . '</option>';
						}
						$input_field .= "\r\n";
					}
					$input_field .= $this->tabs(4) . '</select>' . "\r\n";
					break;
				case 'checkbox':
					$input_field .= sprintf( '<label><input type="checkbox" name="%s" id="%s" value="%s" %s> %s</label>',
						$field['name'],
						$field['name'],
						$field['value'],
						( $this->stored_values[$field['name']] && $this->stored_values[$field['name']] === strval($field['value']) ) ? 'checked' : '',
						__( $field['description'] )
					);
					break;
				case 'radio':
					$i = 0;
					foreach ($field['options'] as $option_value => $option_text) {
						$value = ( is_int( $option_value ) ) ? $option_text : $option_value;
						$input_field .= $this->tabs(4);
						$input_field .= sprintf( '<label><input type="radio" name="%s" %s value="%s" %s> %s</label>%s',
							$field['name'],
							( $i === 0 ) ? 'id="' . $field['name'] . '"' : '',
							$value,
							( ( ! $this->stored_values[$field['name']] && $i === 0 ) || ( $this->stored_values[$field['name']] && $this->stored_values[$field['name']] === $value ) ) ? 'checked' : '',
							$option_text,
							( $i !== count( $field['options'] ) - 1 ) ? '<br>' : ''
						);
						$input_field .= "\r\n";
						$i++;
					}
					break;
				default:
					$input_field .= sprintf( '<input class="%s" type="text" name="%s" id="%s" value="%s" placeholder="%s">',
						( isset( $field['atts']['class'] ) ) ? $field['atts']['class'] : 'regular-text',
						$field['name'],
						$field['name'],
						( $this->stored_values[$field['name']] ) ? __( $this->stored_values[$field['name']] ) : __( $field['value'] ),
						__( $field['placeholder'] )
					);
					break;
			}
			$input_field .= ( $tabled ) ? '</td>' : '</p>';
			$input_field .= "\r\n";
			return $input_field;
		}
		
		/**
		 * Generate Label
		 *
		 * @param string format Is this for a table or not
		 */
		private function generate_label( $field, $tabled = false ) {
			if ( $tabled ) {
				$label  = $this->tabs(3) . '<th scope="row">';
				$label .= '<label for="' . $field['name'] . '">' . __( $field['label'], $this->text_domain ) . '</label>';
				$label .= '</th>' . "\r\n";
			} else {
				$label  = $this->tabs(3) . '<label for="' . $field['name'] . '"><strong>' . __( $field['label'], $this->text_domain ) . '</strong></label>' . "\r\n";
			}
			return $label;
		}
		
		// ==========================================================================
		// Helpers
		// ==========================================================================
		private function tabs( $count ) {
			$tabs = "";
			for ($i = 0; $i < $count; $i++)
				$tabs .= "\t";
			return $tabs;
		}
	}