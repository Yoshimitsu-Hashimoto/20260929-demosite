<?php
/**
 * 制作実績（カスタム投稿タイプ work）と業種（カスタムタクソノミー industry）。
 *
 * @package Hinata
 */

defined( 'ABSPATH' ) || exit;

/**
 * 制作実績と業種を登録する。
 */
function hinata_register_post_types() {
	register_post_type(
		'work',
		array(
			'labels'        => array(
				'name'          => '制作実績',
				'singular_name' => '制作実績',
				'add_new_item'  => '制作実績を追加',
				'edit_item'     => '制作実績を編集',
				'all_items'     => '制作実績一覧',
			),
			'public'        => true,
			'has_archive'   => 'works',
			'rewrite'       => array(
				'slug'       => 'works',
				'with_front' => false,
			),
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-portfolio',
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		)
	);

	// 絞り込みは /works/?industry=<スラッグ> で行うため、業種ごとのアーカイブページは作らない。
	// query_var を false にして、industry パラメータを WordPress のクエリ変数として解釈させない。
	register_taxonomy(
		'industry',
		'work',
		array(
			'labels'             => array(
				'name'          => '業種',
				'singular_name' => '業種',
				'add_new_item'  => '業種を追加',
				'edit_item'     => '業種を編集',
			),
			'public'             => true,
			'publicly_queryable' => false,
			'query_var'          => false,
			'rewrite'            => false,
			'hierarchical'       => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
		)
	);
}
add_action( 'init', 'hinata_register_post_types' );

/**
 * 業種の一覧を、絞り込みボタンの並び順（登録順）で返す。実績が0件の業種も含む。
 *
 * @return WP_Term[]
 */
function hinata_get_industries() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'industry',
			'hide_empty' => false,
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * URL の ?industry= を読み取る。
 *
 * 戻り値:
 * - null：指定なし、または空（「すべて」）
 * - WP_Term：指定された業種
 * - false：存在しない業種（該当0件として扱う）
 *
 * @return WP_Term|false|null
 */
function hinata_get_requested_industry() {
	if ( ! isset( $_GET['industry'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return null;
	}

	$raw = wp_unslash( $_GET['industry'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_string( $raw ) ) {
		return false;
	}

	$raw = trim( $raw );
	if ( '' === $raw ) {
		return null;
	}

	// スラッグの大文字と小文字は区別しない。
	$slug = sanitize_title( strtolower( $raw ) );
	if ( '' === $slug ) {
		return false;
	}

	$term = get_term_by( 'slug', $slug, 'industry' );

	return $term instanceof WP_Term ? $term : false;
}

/**
 * URL の ?sort= を読み取る。
 *
 * 戻り値は 'year_desc'（制作年の新しい順）、'year_asc'（制作年の古い順）、
 * ''（公開日の新しい順。指定なし・空・存在しない値）のいずれか。
 *
 * @return string
 */
function hinata_get_requested_sort() {
	if ( ! isset( $_GET['sort'] ) || ! is_string( $_GET['sort'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '';
	}

	$sort = sanitize_key( wp_unslash( $_GET['sort'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	return array_key_exists( $sort, hinata_get_sort_options() ) ? $sort : '';
}

/**
 * 並べ替えの選択肢（表示順）。キーは ?sort= の値（'' は指定なし）。
 *
 * @return array<string, string>
 */
function hinata_get_sort_options() {
	return array(
		''          => '公開日の新しい順',
		'year_desc' => '制作年の新しい順',
		'year_asc'  => '制作年の古い順',
	);
}

/**
 * 制作実績の一覧を取得する（公開済みのみ）。
 *
 * 並び順は公開日の新しい順。$sort に 'year_desc' / 'year_asc' を指定すると、
 * カスタムフィールド production_year の順に並べ替える（同じ年は公開日の新しい順、未入力は最後）。
 *
 * @param WP_Term|false|null $industry hinata_get_requested_industry() の戻り値。
 * @param int                $limit    件数。-1 ですべて。
 * @param string             $sort     hinata_get_requested_sort() の戻り値。
 * @return WP_Query
 */
function hinata_query_works( $industry = null, $limit = -1, $sort = '' ) {
	$by_year = in_array( $sort, array( 'year_desc', 'year_asc' ), true );

	$args = array(
		'post_type'           => 'work',
		'post_status'         => 'publish',
		// 制作年で並べるときは全件を並べ替えてから件数を絞る。
		'posts_per_page'      => $by_year ? -1 : $limit,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $industry instanceof WP_Term ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy'         => 'industry',
				'field'            => 'term_id',
				'terms'            => $industry->term_id,
				'include_children' => false,
			),
		);
	} elseif ( false === $industry ) {
		$args['post__in'] = array( 0 );
	}

	$query = new WP_Query( $args );

	if ( $by_year ) {
		// meta_key で並べると制作年が未入力の実績がクエリから外れるため、取得後に PHP で並べ替える。
		// usort は安定ソートなので、同じ年の実績は取得時の公開日の新しい順のまま残る。
		$posts = $query->posts;
		usort(
			$posts,
			static function ( $a, $b ) use ( $sort ) {
				$year_a = trim( (string) get_post_meta( $a->ID, 'production_year', true ) );
				$year_b = trim( (string) get_post_meta( $b->ID, 'production_year', true ) );

				if ( '' === $year_a || '' === $year_b ) {
					return ( '' === $year_a ) <=> ( '' === $year_b ); // 未入力は最後。
				}

				return 'year_asc' === $sort ? (int) $year_a <=> (int) $year_b : (int) $year_b <=> (int) $year_a;
			}
		);

		if ( $limit > 0 ) {
			$posts = array_slice( $posts, 0, $limit );
		}

		$query->posts      = $posts;
		$query->post_count = count( $posts );
		$query->post       = $posts ? $posts[0] : null;
		$query->rewind_posts();
	}

	return $query;
}
