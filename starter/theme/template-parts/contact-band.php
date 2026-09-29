<?php
/**
 * ページ下部の「お問い合わせ」帯。
 *
 * $args['heading'] と $args['text'] で文言を変えられる。
 *
 * @package Hinata
 */

$heading = $args['heading'] ?? 'お気軽にご相談ください';
$text    = $args['text'] ?? 'Webサイトの制作・リニューアル、運用のご相談など、お気軽にお問い合わせください。';
?>
<section class="contact-band"><h2><?php echo esc_html( $heading ); ?></h2><p><?php echo esc_html( $text ); ?></p><a class="button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ　→</a></section>
