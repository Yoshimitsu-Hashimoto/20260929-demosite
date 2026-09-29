import { expect, test, type Page } from '@playwright/test';

/**
 * 3. 制作実績の詳細（/works/<スラッグ>/） TC-19〜TC-23
 * 4. トップページ（/） TC-24〜TC-25
 */

/** 詳細ページの表を { 見出し: 値 } にする。 */
async function detailTable(page: Page): Promise<Record<string, string>> {
  const rows = page.locator('main table tr');
  const result: Record<string, string> = {};
  for (let i = 0; i < (await rows.count()); i++) {
    const th = (await rows.nth(i).locator('th').innerText()).trim();
    const td = (await rows.nth(i).locator('td').innerText()).trim();
    result[th] = td;
  }
  return result;
}

test('TC-19 顧客名・制作年・担当範囲を表で表示する', async ({ page }) => {
  await page.goto('/works/yamanoha/');
  expect(await detailTable(page)).toEqual({
    顧客名: '和食処 やまの葉',
    制作年: '2026年',
    担当範囲: '企画・デザイン・WordPress構築',
  });
});

test('TC-20 制作年は「年」を付けて表示する', async ({ page }) => {
  await page.goto('/works/komorebi/');
  expect((await detailTable(page))['制作年']).toBe('2025年');
  await page.goto('/works/towa-mokko/');
  expect((await detailTable(page))['制作年']).toBe('2024年');
});

test('TC-21 制作年が未入力なら「制作年」の行を表示しない', async ({ page }) => {
  await page.goto('/works/sakura-gakushu/');
  expect(await detailTable(page)).toEqual({
    顧客名: 'さくら学習室',
    担当範囲: 'デザイン・WordPress構築',
  });
  await expect(page.locator('main table').getByText('制作年')).toHaveCount(0);
});

test('TC-22 業種名・タイトル・キャッチコピー・画像・表・本文を表示する', async ({ page }) => {
  await page.goto('/works/yamanoha/');
  const article = page.locator('main article');
  await expect(article.getByText('飲食', { exact: true })).toBeVisible();
  await expect(article.getByRole('heading', { name: '和食処 やまの葉 様' })).toBeVisible();
  // キャッチコピー：タイトルの直後の文章（テストデータに文言の指定はないため、空でないことを確かめる）
  await expect(article.locator('h2:has-text("和食処 やまの葉 様") + p')).not.toBeEmpty();
  await expect(article.locator('img')).toHaveCount(1);
  await expect(article.locator('table')).toBeVisible();
  // 本文：表のあとに文章がある
  await expect(article.locator('table ~ * p').first()).not.toBeEmpty();
});

test('TC-23 「制作実績一覧に戻る」で一覧（すべて）に戻る', async ({ page }) => {
  await page.goto('/works/yamanoha/');
  await page.getByRole('link', { name: /制作実績一覧に戻る/ }).click();
  await expect(page).toHaveURL(/\/works\/$/);
  await expect(page.locator('main .work-card')).toHaveCount(9);
});

const homeWorkTitles = (page: Page) =>
  page
    .locator('main section')
    .filter({ has: page.getByRole('heading', { name: '制作実績', exact: true }) })
    .locator('.work-title');

test('TC-24 トップの「制作実績」に新しい順に3件表示する', async ({ page }) => {
  await page.goto('/');
  expect(await homeWorkTitles(page).allInnerTexts()).toEqual([
    '和食処 やまの葉 様',
    '高橋精密工業株式会社 様',
    'はるかサービス 様',
  ]);
});

test('TC-25 トップに公開日が最も新しい下書きの実績は表示されない', async ({ page }) => {
  await page.goto('/');
  await expect(homeWorkTitles(page)).toHaveCount(3);
  await expect(page.getByText('喫茶 ひだまり 様')).toHaveCount(0);
});
