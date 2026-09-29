<?php
/**
 * ヘッダー。
 *
 * @package Hinata
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ( is_front_page() ) : ?>
<meta name="description" content="地域の企業に寄り添うWeb制作会社、株式会社ひなたのデモサイトです。">
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header"><a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a><nav class="main-nav" aria-label="メインナビゲーション"><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>">事業内容</a><a href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>"<?php echo hinata_nav_current( 'works' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>制作実績</a><a href="<?php echo esc_url( home_url( '/company/' ) ); ?>"<?php echo hinata_nav_current( 'company' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>会社案内</a><a href="<?php echo esc_url( home_url( '/#news' ) ); ?>">お知らせ</a><a class="nav-contact" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"<?php echo hinata_nav_current( 'contact' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>お問い合わせ</a></nav></header>
