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
