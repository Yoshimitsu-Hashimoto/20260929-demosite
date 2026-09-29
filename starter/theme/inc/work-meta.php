<?php
/**
 * 制作実績のカスタムフィールド（顧客名・制作年・担当範囲）。プラグインは使わない。
 *
 * @package Hinata
 */

defined( 'ABSPATH' ) || exit;

/**
 * カスタムフィールドの定義（メタキー => 表示名）。表の行もこの順に並ぶ。
 *
 * @return array<string, string>
 */
function hinata_work_fields() {
	return array(
		'client_name'     => '顧客名',
		'production_year' => '制作年',
		'scope'           => '担当範囲',
	);
}

add_action(
	'init',
	static function () {
		foreach ( array_keys( hinata_work_fields() ) as $key ) {
			register_post_meta(
				'work',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}
);

/**
 * 詳細ページの表に出す行を返す。未入力の項目は含めない。
 *
 * @param int $post_id 制作実績のID。
 * @return array<int, array{label: string, value: string}>
 */
function hinata_get_work_details( $post_id ) {
	$rows = array();

	foreach ( hinata_work_fields() as $key => $label ) {
		$value = trim( (string) get_post_meta( $post_id, $key, true ) );
		if ( '' === $value ) {
			continue;
		}
		if ( 'production_year' === $key ) {
			$value .= '年';
		}
		$rows[] = array(
			'label' => $label,
			'value' => $value,
		);
	}

	return $rows;
}

// 管理画面の編集画面に入力欄を出す。
add_action(
	'add_meta_boxes_work',
	static function () {
		add_meta_box( 'hinata-work-details', '実績の詳細', 'hinata_render_work_meta_box', 'work', 'normal', 'high' );
	}
);

/**
 * 入力欄を表示する。
 *
 * @param WP_Post $post 編集中の制作実績。
 */
function hinata_render_work_meta_box( $post ) {
	wp_nonce_field( 'hinata_save_work_meta', 'hinata_work_meta_nonce' );
	$notes = array(
		'production_year' => '西暦4桁（例：2026）。未入力の場合、詳細ページに「制作年」の行は表示されません。',
	);
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( hinata_work_fields() as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		printf(
			'<tr><th scope="row"><label for="hinata-%1$s">%2$s</label></th><td><input type="text" class="regular-text" id="hinata-%1$s" name="hinata_work[%1$s]" value="%3$s">%4$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $value ),
			isset( $notes[ $key ] ) ? '<p class="description">' . esc_html( $notes[ $key ] ) . '</p>' : ''
		);
	}
	echo '</tbody></table>';
}

add_action(
	'save_post_work',
	static function ( $post_id ) {
		if ( ! isset( $_POST['hinata_work_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hinata_work_meta_nonce'] ), 'hinata_save_work_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$input = isset( $_POST['hinata_work'] ) && is_array( $_POST['hinata_work'] ) ? wp_unslash( $_POST['hinata_work'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( array_keys( hinata_work_fields() ) as $key ) {
			$value = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
);
