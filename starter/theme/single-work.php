<?php
/**
 * 制作実績の詳細（/works/<スラッグ>/）。
 *
 * @package Hinata
 */

get_header();

while ( have_posts() ) :
	the_post();
	$hinata_details = hinata_get_work_details( get_the_ID() );
	?>
<main>
	<?php
	hinata_breadcrumb(
		array(
			array( '制作実績', get_post_type_archive_link( 'work' ) ),
			array( get_the_title() ),
		)
	);
	hinata_page_title( '制作実績', 'WORKS' );
	?>
<div class="container page-main"><article class="work-detail"><div class="detail-top"><span class="work-category"><?php echo esc_html( hinata_work_industry_name() ); ?></span><h2><?php the_title(); ?></h2>
	<?php if ( has_excerpt() ) : ?>
<p><?php echo esc_html( get_the_excerpt() ); ?></p>
	<?php endif; ?>
</div>
	<?php the_post_thumbnail( 'full', array( 'class' => 'detail-hero' ) ); ?>
	<?php if ( $hinata_details ) : ?>
<table class="meta-table"><tbody>
		<?php foreach ( $hinata_details as $hinata_row ) : ?>
<tr><th scope="row"><?php echo esc_html( $hinata_row['label'] ); ?></th><td><?php echo esc_html( $hinata_row['value'] ); ?></td></tr>
		<?php endforeach; ?>
</tbody></table>
	<?php endif; ?>
<div class="content-section work-body"><?php the_content(); ?></div>
<p class="center-link"><a class="outline-button" href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">制作実績一覧に戻る　→</a></p></article></div>
	<?php
	get_template_part(
		'template-parts/contact-band',
		null,
		array(
			'heading' => 'Webサイト制作のご相談はこちら',
			'text'    => '貴社の想いを丁寧に伺い、課題に合わせたWebサイトをご提案します。',
		)
	);
	?>
</main>
	<?php
endwhile;

get_footer();
