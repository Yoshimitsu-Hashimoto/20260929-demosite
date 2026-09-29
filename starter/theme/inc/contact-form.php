<?php
/**
 * お問い合わせフォーム（/contact/）の送信処理。
 *
 * 入力に問題がなければ管理者にメールを送り、送信完了ページ（/thanks/）へ移動する。
 * 問題があれば送信せず、エラーと入力内容を残したままお問い合わせページを表示する。
 *
 * 入力欄の name 属性は your_name などにしている。name は WordPress のクエリ変数で、
 * POST に含めると別の投稿を探してしまい、ページが見つからない表示になるため。
 *
 * @package Hinata
 */

defined( 'ABSPATH' ) || exit;

/**
 * フォームの状態（入力値とエラー）を保持する。
 *
 * @param array|null $new_state 保存する状態。省略時は現在の状態を返す。
 * @return array{values: array<string, string>, consent: bool, errors: array<string, string>}
 */
function hinata_contact_state( $new_state = null ) {
	static $state = array(
		'values'  => array(
			'your_name'    => '',
			'your_email'   => '',
			'your_message' => '',
		),
		'consent' => false,
		'errors'  => array(),
	);

	if ( null !== $new_state ) {
		$state = $new_state;
	}

	return $state;
}

/**
 * 前後の空白（半角スペース・全角スペース・タブ・改行）を取り除く。
 *
 * @param mixed $value 入力値。
 * @return string
 */
function hinata_contact_trim( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	return (string) preg_replace( '/\A[\s\x{3000}]+|[\s\x{3000}]+\z/u', '', $value );
}

/**
 * 入力内容を検証する。
 *
 * @param array<string, string> $values  入力値（前後の空白は除去済み）。
 * @param bool                  $consent 同意にチェックがあるか。
 * @return array<string, string> 項目ごとのエラーメッセージ。
 */
function hinata_contact_validate( $values, $consent ) {
	$errors = array();

	if ( '' === $values['your_name'] ) {
		$errors['your_name'] = 'お名前を入力してください。';
	}

	if ( '' === $values['your_email'] ) {
		$errors['your_email'] = 'メールアドレスを入力してください。';
	} elseif ( ! is_email( $values['your_email'] ) ) {
		$errors['your_email'] = 'メールアドレスの形式が正しくありません。';
	}

	if ( '' === $values['your_message'] ) {
		$errors['your_message'] = 'お問い合わせ内容を入力してください。';
	}

	if ( ! $consent ) {
		$errors['consent'] = 'プライバシーポリシーへの同意が必要です。';
	}

	return $errors;
}

/**
 * 管理者にお問い合わせメールを送る。
 *
 * @param array<string, string> $values 入力値。
 * @return bool 送信できたか。
 */
function hinata_contact_send( $values ) {
	// 件名やヘッダーに改行が入らないよう、1行にする。
	$name  = trim( (string) preg_replace( '/[\x00-\x1F\x7F]+/u', ' ', $values['your_name'] ) );
	$email = $values['your_email'];
	$site  = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	$subject = sprintf( '【%s】お問い合わせ（%s 様）', $site, $name );
	$body    = implode(
		"\n",
		array(
			$site . 'のWebサイトからお問い合わせがありました。',
			'',
			'お名前：' . $name,
			'メールアドレス：' . $email,
			'',
			'お問い合わせ内容：',
			$values['your_message'],
			'',
			'-- ',
			'送信日時：' . wp_date( 'Y-m-d H:i:s' ),
			'送信元ページ：' . home_url( '/contact/' ),
		)
	);
	$headers = array( 'Reply-To: ' . $email );

	return wp_mail( get_option( 'admin_email' ), $subject, $body, $headers );
}

add_action(
	'template_redirect',
	static function () {
		if ( ! is_page( 'contact' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['hinata_contact'] ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- 前後の空白だけ除き、入力内容をそのままメールに含める。表示時にエスケープする。
		$values = array();
		foreach ( array( 'your_name', 'your_email', 'your_message' ) as $key ) {
			$values[ $key ] = hinata_contact_trim( isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '' );
		}
		// phpcs:enable
		$consent = isset( $_POST['consent'] ) && 'agree' === $_POST['consent'];

		$errors = array();
		if ( ! isset( $_POST['hinata_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hinata_contact_nonce'] ), 'hinata_contact' ) ) {
			$errors['form'] = '送信の有効期限が切れました。お手数ですが、もう一度送信してください。';
		} else {
			$errors = hinata_contact_validate( $values, $consent );
		}

		if ( ! $errors && ! hinata_contact_send( $values ) ) {
			$errors['form'] = '送信できませんでした。時間をおいて、もう一度お試しください。';
		}

		if ( ! $errors ) {
			wp_safe_redirect( home_url( '/thanks/' ), 303 );
			exit;
		}

		hinata_contact_state(
			array(
				'values'  => $values,
				'consent' => $consent,
				'errors'  => $errors,
			)
		);
	}
);
