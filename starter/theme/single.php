<?php
/**
 * お知らせ（投稿）の詳細。
 *
 * @package Hinata
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
<main>
	<?php
	hinata_breadcrumb( array( array( 'お知らせ', home_url( '/#news' ) ), array( get_the_title() ) ) );
	hinata_page_title( 'お知らせ', 'NEWS' );
	?>
<div class="container page-main"><article class="single-news"><time class="news-date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time><h2><?php the_title(); ?></h2><div class="content-section"><?php the_content(); ?></div><p class="center-link"><a class="outline-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ戻る　→</a></p></article></div></main>
	<?php
endwhile;

get_footer();
