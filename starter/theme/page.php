<?php
/**
 * 固定ページ（専用テンプレートがないページ）。
 *
 * @package Hinata
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
<main>
	<?php
	hinata_breadcrumb( array( array( get_the_title() ) ) );
	hinata_page_title( get_the_title(), strtoupper( get_post_field( 'post_name' ) ) );
	?>
<div class="container page-main"><article class="privacy-copy"><?php the_content(); ?></article></div></main>
	<?php
endwhile;

get_footer();
