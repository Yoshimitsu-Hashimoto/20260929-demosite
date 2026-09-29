<?php
/**
 * テンプレートで使う表示用の関数。
 *
 * @package Hinata
 */

defined( 'ABSPATH' ) || exit;

/**
 * パンくずリストを表示する。
 *
 * @param array<int, array{0: string, 1?: string}> $items [表示名, URL] の配列。最後の項目は URL なし。
 */
function hinata_breadcrumb( $items ) {
	$parts = array( '<a href="' . esc_url( home_url( '/' ) ) . '">トップ</a>' );
	foreach ( $items as $item ) {
		$parts[] = isset( $item[1] )
			? '<a href="' . esc_url( $item[1] ) . '">' . esc_html( $item[0] ) . '</a>'
			: esc_html( $item[0] );
	}
	echo '<div class="container breadcrumb">' . implode( '　›　', $parts ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 各要素はエスケープ済み。
}

/**
 * ページ上部の見出し（日本語の見出しと英字の小見出し）を表示する。
 *
 * @param string $title   見出し。
 * @param string $eyebrow 英字の小見出し。
 */
function hinata_page_title( $title, $eyebrow ) {
	printf(
		'<div class="page-title"><h1>%s</h1><span class="eyebrow">%s</span></div>',
		esc_html( $title ),
		esc_html( $eyebrow )
	);
}

/**
 * 制作実績の業種名を返す（複数ある場合は「・」でつなぐ）。
 *
 * @param int|WP_Post|null $post 制作実績。
 * @return string
 */
function hinata_work_industry_name( $post = null ) {
	$terms = get_the_terms( $post, 'industry' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}

	return implode( '・', wp_list_pluck( $terms, 'name' ) );
}

/**
 * メインナビゲーションで、現在のページのリンクに aria-current を付ける。
 *
 * @param string $section works / company / contact。
 * @return string
 */
function hinata_nav_current( $section ) {
	$current = false;
	switch ( $section ) {
		case 'works':
			$current = is_post_type_archive( 'work' ) || is_singular( 'work' );
			break;
		case 'company':
			$current = is_page( 'company' );
			break;
		case 'contact':
			$current = is_page( 'contact' );
			break;
	}

	return $current ? ' aria-current="page"' : '';
}
