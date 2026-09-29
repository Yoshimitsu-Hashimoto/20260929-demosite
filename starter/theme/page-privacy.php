<?php
/**
 * プライバシーポリシー（/privacy/）。
 *
 * @package Hinata
 */

get_header();
?>
<main>
<?php
hinata_breadcrumb( array( array( 'プライバシーポリシー' ) ) );
hinata_page_title( 'プライバシーポリシー', 'PRIVACY POLICY' );
?>
<div class="container page-main"><article class="privacy-copy"><p class="intro">株式会社ひなたは、お問い合わせ時にお預かりする情報を大切に取り扱います。</p><p class="privacy-notice">こちらは講座用デモサイトのサンプル文面です。</p>
<section class="privacy-item"><h2>1. 取得する情報</h2><p>お問い合わせフォームでは、お名前、メールアドレス、お問い合わせ内容を入力いただきます。</p></section>
<section class="privacy-item"><h2>2. 利用目的</h2><p>入力された情報は、お問い合わせへの対応および講座内の動作確認に使用します。</p></section>
<section class="privacy-item"><h2>3. 情報の管理</h2><p>講座用のテスト情報を使用し、確認が終わったデータは適切に整理・削除します。</p></section>
<section class="privacy-item"><h2>4. 第三者への提供</h2><p>このデモサイトでは、入力情報を広告配信などの目的で第三者に提供することはありません。</p></section>
<section class="privacy-item"><h2>5. お問い合わせ窓口</h2><p>本ページについてのお問い合わせは、<a class="text-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせフォーム</a>をご利用ください。</p></section>
<p class="policy-date">制定日：2026年9月28日<br>株式会社ひなた</p><p class="center-link"><a class="outline-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップページへ戻る　→</a></p></article></div></main>
<?php
get_footer();
