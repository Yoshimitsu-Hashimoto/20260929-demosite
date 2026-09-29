<?php
/**
 * 会社案内（/company/）。
 *
 * @package Hinata
 */

get_header();

$hinata_image = get_template_directory_uri() . '/assets/images/office-team.jpg';
?>
<main>
<?php
hinata_breadcrumb( array( array( '会社案内' ) ) );
hinata_page_title( '会社案内', 'COMPANY' );
?>
<div class="container page-main">
<section class="company-intro"><div><h2>地域の企業と、<br>ともに歩む。</h2><p>株式会社ひなたは、地域の企業に寄り添うWeb制作会社です。丁寧なヒアリングを通じて、それぞれの想いに向き合い、最適なかたちをご提案します。</p><p>Webサイトの企画・制作から、公開後の運用サポートまで。地域のビジネスの成長を、長く支えていきます。</p></div><img src="<?php echo esc_url( $hinata_image ); ?>" alt="相談しながらサイトをつくる株式会社ひなたのスタッフ"></section>
<section class="section"><div class="section-heading"><h2>大切にしていること</h2><span class="eyebrow">OUR VALUES</span><div class="heading-mark"></div></div><div class="values-grid"><article class="value"><span class="value-number">01</span><h3>丁寧に聴く</h3><p>お客様の想いや課題に丁寧に耳を傾け、本質を理解することを大切にします。</p></article><article class="value"><span class="value-number">02</span><h3>わかりやすく伝える</h3><p>専門的なこともわかりやすく、丁寧にご説明します。</p></article><article class="value"><span class="value-number">03</span><h3>公開後も寄り添う</h3><p>サイト公開後も、運用や改善のご相談に応え、長く伴走します。</p></article></div></section>
<section><div class="section-heading"><h2>会社概要</h2><span class="eyebrow">COMPANY</span><div class="heading-mark"></div></div><table class="company-table"><tbody><tr><th scope="row">会社名</th><td>株式会社ひなた</td></tr><tr><th scope="row">所在地</th><td>東京都○○区○○1-2-3（架空の所在地）</td></tr><tr><th scope="row">設立</th><td>2020年4月</td></tr><tr><th scope="row">代表者</th><td>山田 太郎</td></tr><tr><th scope="row">事業内容</th><td>Webサイト制作・WordPress構築・運用サポート</td></tr><tr><th scope="row">お問い合わせ</th><td><a class="text-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせフォームよりご連絡ください。</a></td></tr></tbody></table><p class="demo-caption">本サイトは講座用のデモです。会社情報はすべて架空です。</p></section></div>
<?php get_template_part( 'template-parts/contact-band', null, array( 'text' => 'Webサイトの制作・運用について、どのようなことでもお気軽にお問い合わせください。' ) ); ?>
</main>
<?php
get_footer();
