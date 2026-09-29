import { defineConfig, devices } from '@playwright/test';

/**
 * docs/test-plan.md のテスト。
 * 事前に docker compose up -d でサイトとMailpitを起動し、テストデータを投入しておく。
 */
export default defineConfig({
  testDir: './tests',
  // お問い合わせのテストは確認用メールボックス（1つ）を共有するため、順番に実行する。
  fullyParallel: false,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: 'http://localhost:8090',
    locale: 'ja-JP',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
