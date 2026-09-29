<?php
/**
 * Plugin Name: Local Mail (Mailpit)
 * Description: ローカル環境で送信したメールをすべて Mailpit に届ける。
 */

if ( 'local' !== wp_get_environment_type() ) {
	return;
}

add_action(
	'phpmailer_init',
	static function ( $phpmailer ) {
		$phpmailer->isSMTP();
		$phpmailer->Host     = 'mailpit';
		$phpmailer->Port     = 1025;
		$phpmailer->SMTPAuth = false;
	}
);

// 既定の送信元 wordpress@localhost はメールアドレスとして不正と判定されるため置き換える。
// テーマなどが送信元を指定した場合はそのまま使う。
add_filter(
	'wp_mail_from',
	static function ( $from ) {
		return 'wordpress@localhost' === $from ? 'no-reply@example.test' : $from;
	}
);
