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
| `theme/` | WordPressテーマ（`hinata` としてマウントされる。ここに作る） |

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

### 管理画面のログイン情報（ローカル環境のテスト用）

| 項目 | 値 |
|---|---|
| 管理画面 | http://localhost:8090/wp-admin/ |
| ユーザー名 | `admin` |
| パスワード | `hinata-70b4cebc710e` |
| メールアドレス | `admin@example.test` |

このログイン情報はローカル環境のテスト専用です。本番環境では使わないでください。

最初からやり直すときは `docker compose down -v` でデータベースごと削除し、上の手順をもう一度実行します。
