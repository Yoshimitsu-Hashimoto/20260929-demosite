# 株式会社ひなた デモサイト（講座用スターター）

講座「AIと作るWordPressサイト」の出発点です。ここから静的サイトをWordPress化し、テスト・Issue・修正まで進めます。

| パス | 内容 |
|---|---|
| `COURSE.md` | 講座の進行テキスト（全体の流れ、事前に準備してあるもの、各ステップ） |
| `docs/spec.md` | サイト仕様書 |
| `docs/test-data.md` | テストデータ |
| `static-site/` | WordPress化する静的サイト（HTML/CSS/画像） |
| `compose.yaml` | Docker環境（WordPress：http://localhost:8090/ 、Mailpit：http://localhost:8091/ ） |
| `docker/mu-plugins/local-mail.php` | WordPressのメールをMailpitに届ける設定 |
| `theme/` | WordPressテーマ `hinata`（WordPressの `wp-content/themes/hinata` にマウントされる） |
| `scripts/seed-test-data.php` | `docs/test-data.md` のテストデータを投入するWP-CLI用スクリプト（WP-CLIコンテナの `/scripts` にマウントされる） |

## ローカル環境の起動と初期設定

```bash
docker compose up -d
```

初回だけ、WP-CLIでWordPressをインストールして初期設定します。

```bash
docker compose run --rm wpcli wp core install --url=http://localhost:8090 --title="株式会社ひなた" --admin_user=admin --admin_password=hinata-70b4cebc710e --admin_email=admin@example.test --skip-email
docker compose run --rm wpcli wp language core install ja --activate
docker compose run --rm wpcli wp option update timezone_string Asia/Tokyo
docker compose run --rm wpcli wp rewrite structure '/%postname%/'
```

`rewrite structure` で出る「.htaccess を再生成できない」という警告は無視してかまいません。公式のWordPressイメージに `.htaccess` があらかじめ用意されているためです。

続けて、テーマを有効にしてテストデータを投入します。テストデータの投入は何度実行しても同じ状態になります。

```bash
docker compose run --rm wpcli wp theme activate hinata
docker compose run --rm wpcli wp eval-file /scripts/seed-test-data.php
```

### 管理画面のログイン情報（ローカル環境のテスト用）

| 項目 | 値 |
|---|---|
| 管理画面 | http://localhost:8090/wp-admin/ |
| ユーザー名 | `admin` |
| パスワード | `hinata-70b4cebc710e` |
| メールアドレス | `admin@example.test` |

このログイン情報はローカル環境のテスト専用です。本番環境では使わないでください。

## テーマの構成

| ファイル | 内容 |
|---|---|
| `functions.php` | テーマの初期設定（`inc/` の読み込み、CSS、タイトルの区切り） |
| `inc/post-types.php` | 制作実績（カスタム投稿タイプ `work`、URLは `/works/`）と業種（カスタムタクソノミー `industry`）、一覧の絞り込み |
| `inc/work-meta.php` | カスタムフィールド（顧客名 `client_name`・制作年 `production_year`・担当範囲 `scope`）と管理画面の入力欄 |
| `inc/contact-form.php` | お問い合わせフォームの入力チェックとメール送信（`wp_mail`） |
| `archive-work.php` / `single-work.php` | 制作実績の一覧（`/works/?industry=<スラッグ>` で絞り込み）と詳細 |
| `front-page.php` | トップページ |
| `page-contact.php` / `page-thanks.php` | お問い合わせと送信完了 |
| `page-company.php` / `page-privacy.php` | 会社案内とプライバシーポリシー |

## やり直すとき

最初からやり直すときは `docker compose down -v` でデータベースごと削除し、上の手順をもう一度実行します。
