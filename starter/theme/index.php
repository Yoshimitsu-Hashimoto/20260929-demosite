<?php
/**
 * 専用テンプレートがない一覧ページ。
 *
 * @package Hinata
 */

get_header();
?>
<main>
<?php hinata_page_title( wp_strip_all_tags( get_the_archive_title() ), 'ARCHIVE' ); ?>
<div class="container page-main"><div class="privacy-copy">
<?php if ( have_posts() ) : ?>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
<section class="privacy-item"><span class="news-date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></section>
	<?php endwhile; ?>
<?php else : ?>
<p class="no-results">記事がありません。</p>
<?php endif; ?>
</div></div></main>
<?php
get_footer();
