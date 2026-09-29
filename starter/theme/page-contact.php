<?php
/**
 * お問い合わせ（/contact/）。送信処理は inc/contact-form.php。
 *
 * 入力チェックはサーバー側で行い、エラーを画面に表示するため、フォームに novalidate を付けて
 * ブラウザの入力チェックを止めている。
 *
 * @package Hinata
 */

get_header();

$hinata_state  = hinata_contact_state();
$hinata_values = $hinata_state['values'];
$hinata_errors = $hinata_state['errors'];

/**
 * 項目のエラーメッセージと aria 属性を返す。
 *
 * @param string $key 項目名。
 * @param array  $errors エラー。
 * @return array{attrs: string, message: string}
 */
$hinata_field_error = static function ( $key, $errors ) {
	if ( ! isset( $errors[ $key ] ) ) {
		return array(
			'attrs'   => '',
			'message' => '',
		);
	}

	return array(
		'attrs'   => sprintf( ' aria-invalid="true" aria-describedby="%s-error"', esc_attr( $key ) ),
		'message' => sprintf( '<p class="field-error" id="%s-error">%s</p>', esc_attr( $key ), esc_html( $errors[ $key ] ) ),
	);
};
$hinata_name    = $hinata_field_error( 'your_name', $hinata_errors );
$hinata_email   = $hinata_field_error( 'your_email', $hinata_errors );
$hinata_message = $hinata_field_error( 'your_message', $hinata_errors );
$hinata_consent = $hinata_field_error( 'consent', $hinata_errors );
?>
<main>
<?php
hinata_breadcrumb( array( array( 'お問い合わせ' ) ) );
hinata_page_title( 'お問い合わせ', 'CONTACT' );
?>
<div class="container page-main"><p class="lead">Webサイトの制作・リニューアル、運用のご相談など、お気軽にお問い合わせください。<br>必須項目をご入力のうえ、送信してください。</p>
<form class="contact-form" action="<?php echo esc_url( get_permalink() ); ?>" method="post" novalidate>
<?php if ( $hinata_errors ) : ?>
<div class="form-errors" role="alert" tabindex="-1" id="form-errors"><p>入力内容に問題があります。</p><ul>
	<?php foreach ( $hinata_errors as $hinata_error ) : ?>
<li><?php echo esc_html( $hinata_error ); ?></li>
	<?php endforeach; ?>
</ul></div>
<?php endif; ?>
<?php wp_nonce_field( 'hinata_contact', 'hinata_contact_nonce' ); ?>
<input type="hidden" name="hinata_contact" value="1">
<?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- attrs と message はエスケープ済み。 ?>
<div class="form-row"><label for="your_name">お名前 <span class="required">必須</span></label><input id="your_name" name="your_name" type="text" autocomplete="name" placeholder="例：山田 太郎" required value="<?php echo esc_attr( $hinata_values['your_name'] ); ?>"<?php echo $hinata_name['attrs']; ?>><?php echo $hinata_name['message']; ?></div>
<div class="form-row"><label for="your_email">メールアドレス <span class="required">必須</span></label><input id="your_email" name="your_email" type="email" autocomplete="email" placeholder="例：yamada@example.com" required value="<?php echo esc_attr( $hinata_values['your_email'] ); ?>"<?php echo $hinata_email['attrs']; ?>><?php echo $hinata_email['message']; ?></div>
<div class="form-row"><label for="your_message">お問い合わせ内容 <span class="required">必須</span></label><textarea id="your_message" name="your_message" placeholder="ご相談内容をご記入ください。" required<?php echo $hinata_message['attrs']; ?>><?php echo esc_textarea( $hinata_values['your_message'] ); ?></textarea><?php echo $hinata_message['message']; ?></div>
<div class="consent"><label><input type="checkbox" id="consent" name="consent" value="agree" required<?php checked( $hinata_state['consent'] ); ?><?php echo $hinata_consent['attrs']; ?>> <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意する <span class="required">必須</span></label><small>送信前に個人情報の取り扱いをご確認ください。</small><?php echo $hinata_consent['message']; ?></div>
<?php // phpcs:enable ?>
<div class="form-actions"><button class="button" type="submit">送信する　→</button><p class="demo-notice">このフォームは講座用デモです。テスト用の情報を入力してください。</p></div>
</form></div></main>
<?php
get_footer();
