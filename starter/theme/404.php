<?php
/**
 * ページが見つからないとき（未公開の制作実績を含む）。
 *
 * @package Hinata
 */

get_header();
?>
<main>
<?php hinata_page_title( 'ページが見つかりません', 'NOT FOUND' ); ?>
<section class="container thanks-content"><h2>お探しのページは見つかりませんでした。</h2><p>URLが間違っているか、ページが削除・非公開になった可能性があります。</p><p class="center-link"><a class="outline-button" href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">制作実績一覧へ　→</a></p><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ戻る　→</a></section></main>
<?php
get_footer();
