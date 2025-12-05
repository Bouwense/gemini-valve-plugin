<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class FormFieldsAttributes extends DataBaseModel {
	public static $trigger_name = 'cascade_delete_form_fields_attrs';
	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			{$primary_key} INT UNSIGNED NOT NULL AUTO_INCREMENT,
			field_id INT UNSIGNED NOT NULL,
			type VARCHAR(255) NOT NULL,
			text_data LONGTEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );

	}

	public static function insert_field_attributes( $field_id, $attributes ) {
		foreach ( $attributes as $attribute ) {
			$data = array(
				'field_id'  => $field_id,
				'type'      => $attribute['type'],
				'text_data' => is_array( $attribute['text_data'] ) ? wp_json_encode( $attribute['text_data'] ) : $attribute['text_data'],
			);
			self::insert( $data );
		}
	}

	public static function insert_front_field_attributes( $field_id, $attributes ) {
		unset( $attributes['tempId'] );
		$formatted_attributes = self::format_attributes( $attributes );
		self::insert_field_attributes( $field_id, $formatted_attributes );
	}

	public static function update_attributes( $field_id, $attributes ) {
		foreach ( $attributes as $key => $value ) {
			$where = array(
				'field_id' => $field_id,
				'type'     => $key,
			);
			$data  = array( 'text_data' => is_array( $value ) ? wp_json_encode( $value ) : $value );
			self::update( $data, $where );
		}
	}

	public static function duplicate_field_attributes( $new_field_ids, $old_field_ids ) {
		global $wpdb;

		$field_id_mapping            = array_combine( $old_field_ids, $new_field_ids );
		$form_fields_attribute_table = self::_table();

		foreach ( $field_id_mapping as $old_field_id => $new_field_id ) {
			$sql = "INSERT INTO {$form_fields_attribute_table} (field_id, type, text_data, created_at, updated_at)
					SELECT %d, type, text_data, NOW(), NOW()
					FROM {$form_fields_attribute_table}
					WHERE field_id = %d";

			$wpdb->query( $wpdb->prepare( $sql, $new_field_id, $old_field_id ) ); // phpcs:ignore
		}
	}

	public static function format_attributes( $attributes ) {
		$formatted_attributes = array();
		foreach ( $attributes as $key => $value ) {
			$formatted_attributes[] = array(
				'type'      => $key,
				'text_data' => $value,
			);
		}

		return $formatted_attributes;
	}

	public static function name_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Name',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Type your name',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function email_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Email',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Type your email',
			),
			array(
				'type'      => 'confirmation',
				'text_data' => $data['confirmation'] ?? '1',
			),
			array(
				'type'      => 'confirmation_label',
				'text_data' => $data['confirmation_label'] ?? 'Confirm your email',
			),
			array(
				'type'      => 'confirmation_placeholder',
				'text_data' => $data['confirmation_placeholder'] ?? '',
			),
			array(
				'type'      => 'position',
				'text_data' => $data['position'] ?? 'Vertical',
			),
			array(
				'type'      => 'primary',
				'text_data' => $data['primary'] ?? '1',
			),

		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function phone_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Phone',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Type your phone',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function input_textbox_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '1',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Input textbox',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Type your text',
			),
			array(
				'type'      => 'character_limit',
				'text_data' => $data['character_limit'] ?? '80',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function textarea_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Message',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Type your text',
			),
			array(
				'type'      => 'character_limit',
				'text_data' => $data['character_limit'] ?? '400',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function number_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Number',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Type your number',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function date_picker_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Date Picker',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Select date',
			),
			array(
				'type'      => 'range',
				'text_data' => $data['range'] ?? '',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function time_picker_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Date Picker',
			),
			array(
				'type'      => 'format',
				'text_data' => $data['format'] ?? '',
			),
			array(
				'type'      => 'range',
				'text_data' => $data['range'] ?? '',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function dropdown_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Dropdown',
			),
			array(
				'type'      => 'options',
				'text_data' => $data['options'] ?? '',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => $data['placeholder'] ?? 'Select an option',
			),
			array(
				'type'      => 'multiselect',
				'text_data' => $data['multiselect'] ?? '',
			),
		);

		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function radio_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Radio',
			),
			array(
				'type'      => 'options',
				'text_data' => $data['options'] ?? '',
			),
			array(
				'type'      => 'display',
				'text_data' => $data['display'] ?? 'Horizontal',
			),
		);

		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function checkbox_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => $data['required'] ?? '',
			),
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Checkbox',
			),
			array(
				'type'      => 'options',
				'text_data' => $data['options'] ?? '',
			),
			array(
				'type'      => 'display',
				'text_data' => $data['display'] ?? 'Horizontal',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function formatted_text_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'label',
				'text_data' => $data['label'] ?? 'Formatted text',
			),
			array(
				'type'      => 'text',
				'text_data' => $data['text'] ?? 'Formatted text',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function button_attributes( $field_id, $data = array() ) {
		$attributes = array(
			array(
				'type'      => 'button_text',
				'text_data' => $data['button_text'] ?? 'Submit Order',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	private static function trigger_exists() {
		global $wpdb;
		$trigger_name = self::$trigger_name;
		// phpcs:disable
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
					   FROM INFORMATION_SCHEMA.TRIGGERS
					   WHERE TRIGGER_NAME = %s
					   AND TRIGGER_SCHEMA = DATABASE();",
				$trigger_name
			)
		);
		// phpcs:enable
	}

	private static function create_trigger() {
		global $wpdb;
		$trigger_name       = self::$trigger_name;
		$table_name         = self::_table();
		$forms_fields_table = FormFields::_table();

		// phpcs:disable
		$wpdb->query(
			"CREATE TRIGGER $trigger_name
					AFTER DELETE ON $forms_fields_table
					FOR EACH ROW
					BEGIN
						DELETE FROM $table_name WHERE field_id = OLD.id;
					END;"
		);
		// phpcs:enable
	}

	public static function maybe_create_trigger() {
		if ( ! self::trigger_exists() ) {
			self::create_trigger();
		}
	}
}
