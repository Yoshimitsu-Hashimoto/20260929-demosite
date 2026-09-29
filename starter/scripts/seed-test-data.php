<?php
/**
 * docs/test-data.md のテストデータを投入する。
 *
 * 実行（starter/ で）：
 *   docker compose run --rm wpcli wp theme activate hinata
 *   docker compose run --rm wpcli wp eval-file /scripts/seed-test-data.php
 *
 * 何度実行しても同じ状態になる（スラッグで探し、あれば更新、なければ作成）。
 *
 * @package Hinata
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

if ( ! post_type_exists( 'work' ) || ! taxonomy_exists( 'industry' ) ) {
	WP_CLI::error( 'テーマ hinata が有効になっていません。先に wp theme activate hinata を実行してください。' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// 管理者として投入する（本文のHTMLがフィルターで削られないように）。
$hinata_admins = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
	)
);
if ( $hinata_admins ) {
	wp_set_current_user( $hinata_admins[0]->ID );
}

/**
 * スラッグで投稿を探し、あれば更新、なければ作成する。
 *
 * @param array $postarr wp_insert_post() に渡す値（post_name と post_type は必須）。
 * @return int 投稿ID。
 */
function hinata_seed_upsert_post( $postarr ) {
	$existing = get_posts(
		array(
			'name'           => $postarr['post_name'],
			'post_type'      => $postarr['post_type'],
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		$postarr['ID'] = $existing[0];
	}
	if ( isset( $postarr['post_date'] ) ) {
		$postarr['post_date_gmt'] = get_gmt_from_date( $postarr['post_date'] );
		$postarr['edit_date']     = true;
	}

	$id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $postarr['post_name'] . '：' . $id->get_error_message() );
	}

	return $id;
}

/**
 * static-site/images の画像をメディアに登録する（登録済みなら再利用する）。
 *
 * @param string $file ファイル名。
 * @param string $alt  代替テキスト。
 * @return int 添付ファイルID。
 */
function hinata_seed_image( $file, $alt ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_hinata_seed_file', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $existing ) {
		return $existing[0];
	}

	$tmp = wp_tempnam( $file );
	if ( ! copy( '/static-site/images/' . $file, $tmp ) ) {
		WP_CLI::error( '画像をコピーできません：' . $file );
	}
	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		$alt
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $file . '：' . $id->get_error_message() );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_hinata_seed_file', $file );

	return $id;
}

/**
 * 段落ブロックを作る。
 *
 * @param string $text 本文。
 * @return string
 */
function hinata_seed_paragraph( $text ) {
	return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->";
}

/**
 * 見出しブロックを作る。
 *
 * @param string $text 見出し。
 * @return string
 */
function hinata_seed_heading( $text ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . $text . "</h2>\n<!-- /wp:heading -->";
}

// ------------------------------------------------------------
// サイトの設定
// ------------------------------------------------------------
update_option( 'blogdescription', '想いをかたちに、事業の力に。' );

// インストール時のサンプル記事・サンプルページは使わないので削除する。
foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $hinata_sample ) {
	$hinata_sample_post = get_page_by_path( $hinata_sample[0], OBJECT, $hinata_sample[1] );
	if ( $hinata_sample_post ) {
		wp_delete_post( $hinata_sample_post->ID, true );
	}
}

// ------------------------------------------------------------
// 業種（絞り込みボタンはこの順＝登録順に並ぶ）
// ------------------------------------------------------------
$hinata_industries = array(
	'food'    => '飲食',
	'making'  => '製造',
	'service' => 'サービス',
	'medical' => '医療',
);
foreach ( $hinata_industries as $hinata_slug => $hinata_name ) {
	$hinata_term = get_term_by( 'slug', $hinata_slug, 'industry' );
	if ( $hinata_term ) {
		wp_update_term( $hinata_term->term_id, 'industry', array( 'name' => $hinata_name ) );
	} else {
		$hinata_result = wp_insert_term( $hinata_name, 'industry', array( 'slug' => $hinata_slug ) );
		if ( is_wp_error( $hinata_result ) ) {
			WP_CLI::error( $hinata_slug . '：' . $hinata_result->get_error_message() );
		}
	}
}
WP_CLI::log( '業種：' . count( $hinata_industries ) . '件' );

// ------------------------------------------------------------
// アイキャッチ画像
// ------------------------------------------------------------
$hinata_images = array(
	'food'    => hinata_seed_image( 'japanese-restaurant.jpg', '飲食店の料理のイメージ写真' ),
	'making'  => hinata_seed_image( 'precision-machining.jpg', '製造業の加工現場のイメージ写真' ),
	'service' => hinata_seed_image( 'community-care.jpg', '地域のサービス業のイメージ写真' ),
);

// ------------------------------------------------------------
// 制作実績
// 項目：公開日, タイトル, スラッグ, 業種, 状態, 顧客名, 制作年, 担当範囲, キャッチコピー, 概要, 取り組んだこと
// ------------------------------------------------------------
$hinata_works = array(
	array( '2026-09-01', '和食処 やまの葉 様', 'yamanoha', 'food', 'publish', '和食処 やまの葉', '2026', '企画・デザイン・WordPress構築', '季節の味わいと、お店の魅力が伝わるサイトに。', '地元の旬の食材を使ったお料理と、落ち着いた和の空間が伝わるよう、写真を中心にしたシンプルで上質なデザインで制作しました。季節のメニューやお知らせを、お店の方ご自身で簡単に更新できるようWordPressを導入しています。', 'お料理の写真を生かした、落ち着きのあるデザイン。<br>お店で更新しやすい、お知らせとメニューの構成。' ),
	array( '2026-08-20', '高橋精密工業株式会社 様', 'takahashi-seimitsu', 'making', 'publish', '高橋精密工業株式会社', '2026', 'デザイン・WordPress構築', '確かな技術力を、取引先に伝わるかたちに。', '精密加工の技術と設備を、写真と具体的な数値でわかりやすく紹介するサイトを制作しました。加工事例は社内で追加できるようWordPressで構築しています。', '加工事例を業種・素材から探せる構成。<br>設備一覧を表で見やすく整理。' ),
	array( '2026-08-05', 'はるかサービス 様', 'haruka-service', 'service', 'publish', 'はるかサービス', '2025', '企画・デザイン・WordPress構築・運用サポート', '地域の安心を、やさしく伝えるサイトに。', '介護サービスを検討するご家族が、必要な情報にすぐたどり着けるよう、サービス内容と利用の流れを中心に構成しました。公開後も、お知らせの更新や改善を継続してサポートしています。', '大きな文字と、わかりやすい導線。<br>利用開始までの流れを図で紹介。' ),
	array( '2026-07-15', 'カフェ こもれび 様', 'komorebi', 'food', 'publish', 'カフェ こもれび', '2025', 'デザイン・コーディング', '木漏れ日のような、くつろぎの時間を伝える。', 'やわらかな光が差し込む店内の雰囲気が伝わるよう、余白を生かしたデザインで制作しました。スマートフォンでメニューと営業時間をすぐに確認できる構成にしています。', '店内写真を大きく使ったトップページ。<br>営業時間とアクセスをすぐに確認できる配置。' ),
	array( '2026-06-25', '株式会社青木製作所 様', 'aoki-seisakusho', 'making', 'publish', '株式会社青木製作所', '2025', '企画・デザイン・WordPress構築', 'ものづくりの現場を、まっすぐに伝える。', '職人の技術と製品へのこだわりが伝わるよう、製造工程を写真とともに紹介するサイトを企画・制作しました。採用情報も更新しやすいようWordPressで構築しています。', '製造工程をステップごとに紹介。<br>採用ページで社員の声を掲載。' ),
	array( '2026-06-10', 'みどり不動産 様', 'midori-fudosan', 'service', 'publish', 'みどり不動産', '2024', 'WordPress構築・運用サポート', '住まい探しを、もっと身近に。', '既存のデザインを生かしながら、物件情報を担当者が更新できるようWordPressで再構築しました。公開後は、物件掲載の運用と改善をサポートしています。', '物件情報を登録しやすい入力画面。<br>エリア・間取りから探せる一覧。' ),
	array( '2026-05-20', 'ベーカリー麦の音 様', 'muginone', 'food', 'publish', 'ベーカリー麦の音', '2024', 'デザイン・WordPress構築', '焼きたての香りが伝わるサイトに。', '毎朝焼き上がるパンの魅力が伝わるよう、写真を主役にしたあたたかみのあるデザインで制作しました。季節の新商品をお店で紹介できるようWordPressを導入しています。', '商品写真を並べて見せる一覧ページ。<br>季節の新商品をお知らせで発信。' ),
	array( '2026-05-01', '東和木工株式会社 様', 'towa-mokko', 'making', 'publish', '東和木工株式会社', '2024', '企画・デザイン', '木のぬくもりと職人の技を伝える。', '木工製品の質感と、職人の手仕事が伝わるサイトを企画・デザインしました。製品ごとの使われ方がイメージしやすいよう、施工事例とあわせて紹介しています。', '木目を生かした落ち着いた配色。<br>製品と施工事例をつなげて紹介。' ),
	array( '2026-04-15', 'さくら学習室 様', 'sakura-gakushu', 'service', 'publish', 'さくら学習室', '', 'デザイン・WordPress構築', '学ぶ楽しさが伝わる教室サイトに。', '教室の雰囲気と指導方針が、保護者の方に伝わるよう制作しました。体験授業の案内や季節講習のお知らせを、教室で更新できるようWordPressで構築しています。', '体験授業までの流れをわかりやすく。<br>季節講習のお知らせを更新しやすい構成。' ),
	array( '2026-09-10', '喫茶 ひだまり 様', 'hidamari-draft', 'food', 'draft', '喫茶 ひだまり', '2026', 'デザイン', '陽だまりのような、あたたかい喫茶店のサイトに。', '昔ながらの喫茶店の落ち着いた雰囲気が伝わるデザインを制作中です。', 'レトロな雰囲気を生かした配色。' ),
);

foreach ( $hinata_works as $hinata_work ) {
	list( $hinata_date, $hinata_title, $hinata_slug, $hinata_industry, $hinata_status, $hinata_client, $hinata_year, $hinata_scope, $hinata_catch, $hinata_overview, $hinata_points ) = $hinata_work;

	$hinata_id = hinata_seed_upsert_post(
		array(
			'post_type'    => 'work',
			'post_title'   => $hinata_title,
			'post_name'    => $hinata_slug,
			'post_status'  => $hinata_status,
			'post_date'    => $hinata_date . ' 10:00:00',
			'post_excerpt' => $hinata_catch,
			'post_content' => implode(
				"\n\n",
				array(
					hinata_seed_heading( '制作の概要' ),
					hinata_seed_paragraph( $hinata_overview ),
					hinata_seed_heading( '取り組んだこと' ),
					hinata_seed_paragraph( $hinata_points ),
				)
			),
		)
	);

	wp_set_object_terms( $hinata_id, $hinata_industry, 'industry' );
	set_post_thumbnail( $hinata_id, $hinata_images[ $hinata_industry ] );

	$hinata_meta = array(
		'client_name'     => $hinata_client,
		'production_year' => $hinata_year,
		'scope'           => $hinata_scope,
	);
	foreach ( $hinata_meta as $hinata_key => $hinata_value ) {
		if ( '' === $hinata_value ) {
			delete_post_meta( $hinata_id, $hinata_key );
		} else {
			update_post_meta( $hinata_id, $hinata_key, $hinata_value );
		}
	}
}
WP_CLI::log( '制作実績：' . count( $hinata_works ) . '件（うち下書き1件）' );

// ------------------------------------------------------------
// 固定ページ（本文はテーマのテンプレートで表示する）
// ------------------------------------------------------------
$hinata_pages = array(
	'top'     => 'トップ',
	'company' => '会社案内',
	'contact' => 'お問い合わせ',
	'thanks'  => '送信完了',
	'privacy' => 'プライバシーポリシー',
);
$hinata_page_ids = array();
foreach ( $hinata_pages as $hinata_slug => $hinata_title ) {
	$hinata_page_ids[ $hinata_slug ] = hinata_seed_upsert_post(
		array(
			'post_type'   => 'page',
			'post_title'  => $hinata_title,
			'post_name'   => $hinata_slug,
			'post_status' => 'publish',
		)
	);
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $hinata_page_ids['top'] );
update_option( 'wp_page_for_privacy_policy', $hinata_page_ids['privacy'] );
WP_CLI::log( '固定ページ：' . count( $hinata_pages ) . '件' );

// ------------------------------------------------------------
// お知らせ
// ------------------------------------------------------------
hinata_seed_upsert_post(
	array(
		'post_type'    => 'post',
		'post_title'   => 'コーポレートサイトを公開しました。',
		'post_name'    => 'site-open',
		'post_status'  => 'publish',
		'post_date'    => '2026-09-15 10:00:00',
		'post_content' => hinata_seed_paragraph( '株式会社ひなたのコーポレートサイトを公開しました。制作実績や会社案内をご覧いただけます。今後ともよろしくお願いいたします。' ),
	)
);
WP_CLI::log( 'お知らせ：1件' );

flush_rewrite_rules( false );

WP_CLI::success( 'テストデータを投入しました。' );
