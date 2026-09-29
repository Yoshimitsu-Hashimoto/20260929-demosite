<?php
/**
 * 制作実績一覧（/works/）。?industry=<スラッグ> で業種を絞り込み、?sort= で並べ替える。
 *
 * @package Hinata
 */

get_header();

// null：すべて、WP_Term：その業種、false：存在しない業種（0件）。
$hinata_industry = hinata_get_requested_industry();
$hinata_sort     = hinata_get_requested_sort();
$hinata_works    = hinata_query_works( $hinata_industry, -1, $hinata_sort );
$hinata_archive  = get_post_type_archive_link( 'work' );
// 業種ボタンは並べ替えを、並べ替えのリンクは業種を引き継ぐ。
$hinata_filter_base = '' === $hinata_sort ? $hinata_archive : add_query_arg( 'sort', $hinata_sort, $hinata_archive );
$hinata_sort_base   = $hinata_industry instanceof WP_Term ? add_query_arg( 'industry', $hinata_industry->slug, $hinata_archive ) : $hinata_archive;
?>
<main class="works-page">
<?php
hinata_breadcrumb( array( array( '制作実績' ) ) );
hinata_page_title( '制作実績', 'WORKS' );
?>
<div class="container page-main"><p class="lead">さまざまな業種の企業・店舗のWeb制作をお手伝いしています。</p>
<nav class="filter-row" aria-label="業種で絞り込む">
<a href="<?php echo esc_url( $hinata_filter_base ); ?>"<?php echo null === $hinata_industry ? ' aria-current="page"' : ''; ?>>すべて</a>
<?php foreach ( hinata_get_industries() as $hinata_term ) : ?>
<a href="<?php echo esc_url( add_query_arg( 'industry', $hinata_term->slug, $hinata_filter_base ) ); ?>"<?php echo ( $hinata_industry instanceof WP_Term && $hinata_industry->term_id === $hinata_term->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $hinata_term->name ); ?></a>
<?php endforeach; ?>
</nav>
<nav class="filter-row sort-row" aria-label="並べ替え">
<?php foreach ( hinata_get_sort_options() as $hinata_value => $hinata_label ) : ?>
<a href="<?php echo esc_url( '' === $hinata_value ? $hinata_sort_base : add_query_arg( 'sort', $hinata_value, $hinata_sort_base ) ); ?>"<?php echo $hinata_sort === $hinata_value ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $hinata_label ); ?></a>
<?php endforeach; ?>
</nav>
<p class="result-count"><?php echo esc_html( $hinata_works->post_count . '件の実績' ); ?></p>
<?php if ( $hinata_works->have_posts() ) : ?>
<div class="works-grid">
	<?php
	while ( $hinata_works->have_posts() ) :
		$hinata_works->the_post();
		get_template_part( 'template-parts/work-card' );
	endwhile;
	wp_reset_postdata();
	?>
</div>
<?php else : ?>
<p class="no-results">該当する実績はありません。</p>
<?php endif; ?>
</div>
<?php get_template_part( 'template-parts/contact-band' ); ?>
</main>
<?php
get_footer();
