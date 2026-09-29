<?php
/**
 * 送信完了（/thanks/）。
 *
 * @package Hinata
 */

get_header();
?>
<main>
<?php
hinata_breadcrumb(
	array(
		array( 'お問い合わせ', home_url( '/contact/' ) ),
		array( '送信完了' ),
	)
);
hinata_page_title( 'お問い合わせ', 'CONTACT' );
?>
<section class="container thanks-content"><div class="checkmark" aria-hidden="true">✓</div><h2>お問い合わせを受け付けました。</h2><p>ご入力いただき、ありがとうございます。</p><p>内容を確認のうえ、担当者よりご連絡いたします。</p><div class="info-note">このサイトは講座用デモです。送信した内容は、確認用メールボックス（Mailpit）に届きます。</div><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ戻る　→</a></section></main>
<?php
get_footer();
