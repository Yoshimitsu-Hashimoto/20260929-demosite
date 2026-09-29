<?php
/**
 * 制作実績のカード（ループ内で使う）。
 *
 * $args['heading'] で見出しのタグ（h2 / h3）を指定する。
 *
 * @package Hinata
 */

$heading = isset( $args['heading'] ) && 'h3' === $args['heading'] ? 'h3' : 'h2';
?>
<a class="work-card" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?><<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- h2 か h3 のみ。 ?> class="work-title"><?php the_title(); ?></<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><span class="work-category"><?php echo esc_html( hinata_work_industry_name() ); ?></span></a>
