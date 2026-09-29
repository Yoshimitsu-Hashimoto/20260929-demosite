<?php
/**
 * 株式会社ひなた テーマの初期設定。
 *
 * @package Hinata
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/work-meta.php';
require_once get_template_directory() . '/inc/contact-form.php';
require_once get_template_directory() . '/inc/template-tags.php';

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$path = get_template_directory() . '/style.css';
		wp_enqueue_style( 'hinata', get_stylesheet_uri(), array(), (string) filemtime( $path ) );
	}
);

// タイトルの区切りを静的サイトと同じ「|」にする。
add_filter(
	'document_title_separator',
	static function () {
		return '|';
	}
);

// テーマを有効にしたとき、制作実績のURL（/works/）を使えるようにする。
add_action(
	'after_switch_theme',
	static function () {
		hinata_register_post_types();
		flush_rewrite_rules( false );
	}
);
