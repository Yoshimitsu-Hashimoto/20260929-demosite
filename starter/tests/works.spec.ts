import { expect, test, type Page } from '@playwright/test';

/**
 * 2. 制作実績一覧（/works/） TC-01〜TC-18、並べ替え TC-48〜TC-56
 */

const NAVY = 'rgb(23, 59, 91)'; // #173b5b
const WHITE = 'rgb(255, 255, 255)';

const ALL_TITLES = [
  '和食処 やまの葉 様',
  '高橋精密工業株式会社 様',
  'はるかサービス 様',
  'カフェ こもれび 様',
  '株式会社青木製作所 様',
  'みどり不動産 様',
  'ベーカリー麦の音 様',
  '東和木工株式会社 様',
  'さくら学習室 様',
];
const TITLES = {
  food: ['和食処 やまの葉 様', 'カフェ こもれび 様', 'ベーカリー麦の音 様'],
  making: ['高橋精密工業株式会社 様', '株式会社青木製作所 様', '東和木工株式会社 様'],
  service: ['はるかサービス 様', 'みどり不動産 様', 'さくら学習室 様'],
};
const DRAFT_TITLE = '喫茶 ひだまり 様';

const cards = (page: Page) => page.locator('main .work-card');
const cardTitles = (page: Page) => page.locator('main .work-card .work-title');
const countText = (page: Page) => page.getByText(/^\d+件の実績$/);
const filterGroup = (page: Page) => page.getByRole('navigation', { name: '業種で絞り込む' });
const filterButton = (page: Page, name: string) => filterGroup(page).getByRole('link', { name, exact: true });

/** 選んだボタンだけが選択状態で、他は選択状態でないこと（SPEC-09）。 */
async function expectSelected(page: Page, selected: string | null) {
  const buttons = filterGroup(page).getByRole('link');
  const names = await buttons.allInnerTexts();
  for (const name of names) {
    const button = filterButton(page, name.trim());
    if (name.trim() === selected) {
      await expect(button, `${name} が選択状態`).toHaveAttribute('aria-current', 'page');
      await expect(button).toHaveCSS('background-color', NAVY);
      await expect(button).toHaveCSS('color', WHITE);
    } else {
      await expect(button, `${name} は選択状態でない`).not.toHaveAttribute('aria-current', /.*/);
      await expect(button).toHaveCSS('background-color', WHITE);
    }
  }
}

test('TC-01 公開済みの9件が表示され「9件の実績」と表示される', async ({ page }) => {
  await page.goto('/works/');
  await expect(cards(page)).toHaveCount(9);
  await expect(countText(page)).toHaveText('9件の実績');
});

test('TC-02 下書きの実績は一覧に表示されない', async ({ page }) => {
  await page.goto('/works/');
  await expect(cardTitles(page)).toHaveCount(9);
  expect(await cardTitles(page).allInnerTexts()).not.toContain(DRAFT_TITLE);
  await expect(page.getByText(DRAFT_TITLE)).toHaveCount(0);
});

test('TC-03 下書きの実績の詳細URLはページが見つからない（404）', async ({ page }) => {
  const res = await page.goto('/works/hidamari-draft/');
  expect(res?.status()).toBe(404);
  await expect(page.getByText(DRAFT_TITLE)).toHaveCount(0);
});

test('TC-04 公開日の新しい順に並び、ページ送りはない', async ({ page }) => {
  await page.goto('/works/');
  expect(await cardTitles(page).allInnerTexts()).toEqual(ALL_TITLES);
  await expect(page.getByRole('link', { name: /次へ|次のページ|Next/ })).toHaveCount(0);
});

test('TC-05 カードに画像・タイトル・業種名があり、選ぶと詳細に移動する', async ({ page }) => {
  await page.goto('/works/');
  const count = await cards(page).count();
  expect(count).toBe(9);
  for (let i = 0; i < count; i++) {
    const card = cards(page).nth(i);
    await expect(card.locator('img')).toHaveCount(1);
    await expect(card.locator('.work-title')).not.toBeEmpty();
    await expect(card.locator('.work-category')).toHaveText(/^(飲食|製造|サービス|医療)$/);
  }
  await cards(page).filter({ hasText: '和食処 やまの葉 様' }).click();
  await expect(page).toHaveURL(/\/works\/yamanoha\/$/);
});

for (const [id, name, slug] of [
  ['TC-06', '飲食', 'food'],
  ['TC-07', '製造', 'making'],
  ['TC-08', 'サービス', 'service'],
] as const) {
  test(`${id} 「${name}」を選ぶと、その業種の3件だけが表示される`, async ({ page }) => {
    await page.goto('/works/');
    await filterButton(page, name).click();
    await expect(page).toHaveURL(new RegExp(`/works/\\?industry=${slug}$`));
    expect(await cardTitles(page).allInnerTexts()).toEqual(TITLES[slug]);
    await expect(page.locator('main .work-card .work-category')).toHaveText([name, name, name]);
    await expect(countText(page)).toHaveText('3件の実績');
  });
}

test('TC-09 「飲食」で絞り込んでも下書きの実績は表示されない', async ({ page }) => {
  await page.goto('/works/?industry=food');
  await expect(cards(page)).toHaveCount(3);
  await expect(page.getByText(DRAFT_TITLE)).toHaveCount(0);
});

test('TC-10 「すべて」を選ぶと絞り込みが解除される', async ({ page }) => {
  await page.goto('/works/');
  await filterButton(page, '飲食').click();
  await expect(cards(page)).toHaveCount(3);
  await filterButton(page, 'すべて').click();
  await expect(cards(page)).toHaveCount(9);
  await expect(countText(page)).toHaveText('9件の実績');
});

test('TC-11 「医療」を選ぶと「該当する実績はありません。」「0件の実績」と表示される', async ({ page }) => {
  await page.goto('/works/');
  await filterButton(page, '医療').click();
  await expect(cards(page)).toHaveCount(0);
  await expect(page.getByText('該当する実績はありません。')).toBeVisible();
  await expect(countText(page)).toHaveText('0件の実績');
});

test('TC-12 「N件の実績」と表示しているカードの数が一致する', async ({ page }) => {
  for (const name of ['すべて', '飲食', '製造', 'サービス', '医療']) {
    await page.goto('/works/');
    await filterButton(page, name).click();
    await expect(countText(page)).toBeVisible();
    const n = Number((await countText(page).innerText()).replace('件の実績', ''));
    await expect(cards(page), `${name}：表示件数と「${n}件の実績」が一致`).toHaveCount(n);
  }
});

test('TC-13 /works/ では「すべて」だけが選択状態', async ({ page }) => {
  await page.goto('/works/');
  await expectSelected(page, 'すべて');
});

test('TC-14 選んだボタンだけが選択状態になる', async ({ page }) => {
  for (const name of ['飲食', '製造', 'サービス', '医療']) {
    await page.goto('/works/');
    await filterButton(page, name).click();
    await expectSelected(page, name);
  }
});

test('TC-15 ボタンは「すべて」「飲食」「製造」「サービス」「医療」の順で、0件の「医療」も選べる', async ({ page }) => {
  await page.goto('/works/');
  const names = (await filterGroup(page).getByRole('link').allInnerTexts()).map((s) => s.trim());
  expect(names).toEqual(['すべて', '飲食', '製造', 'サービス', '医療']);
  await filterButton(page, '医療').click();
  await expect(page).toHaveURL(/industry=medical$/);
});

test('TC-16 スラッグの大文字と小文字は区別しない（?industry=FOOD）', async ({ page }) => {
  await page.goto('/works/?industry=FOOD');
  expect(await cardTitles(page).allInnerTexts()).toEqual(TITLES.food);
  await expect(countText(page)).toHaveText('3件の実績');
  await expectSelected(page, '飲食');
});

test('TC-17 ?industry= （値が空）は「すべて」と同じ扱い', async ({ page }) => {
  await page.goto('/works/?industry=');
  await expect(cards(page)).toHaveCount(9);
  await expect(countText(page)).toHaveText('9件の実績');
  await expectSelected(page, 'すべて');
});

test('TC-18 存在しない業種は0件で、どのボタンも選択状態にならない（ステータス200）', async ({ page }) => {
  const res = await page.goto('/works/?industry=unknown');
  expect(res?.status()).toBe(200);
  await expect(cards(page)).toHaveCount(0);
  await expect(page.getByText('該当する実績はありません。')).toBeVisible();
  await expect(countText(page)).toHaveText('0件の実績');
  await expectSelected(page, null);
});

/* 並べ替え（SPEC-14〜SPEC-16） */

const BY_YEAR_DESC = [
  '和食処 やまの葉 様',
  '高橋精密工業株式会社 様',
  'はるかサービス 様',
  'カフェ こもれび 様',
  '株式会社青木製作所 様',
  'みどり不動産 様',
  'ベーカリー麦の音 様',
  '東和木工株式会社 様',
  'さくら学習室 様',
];
const BY_YEAR_ASC = [
  'みどり不動産 様',
  'ベーカリー麦の音 様',
  '東和木工株式会社 様',
  'はるかサービス 様',
  'カフェ こもれび 様',
  '株式会社青木製作所 様',
  '和食処 やまの葉 様',
  '高橋精密工業株式会社 様',
  'さくら学習室 様',
];
const NO_YEAR_TITLE = 'さくら学習室 様';
const SORT_NAMES = ['公開日の新しい順', '制作年の新しい順', '制作年の古い順'];

const sortGroup = (page: Page) => page.getByRole('navigation', { name: '並べ替え' });
const sortLink = (page: Page, name: string) => sortGroup(page).getByRole('link', { name, exact: true });

/** 選んだ並べ替えだけに aria-current="page" が付いていること。 */
async function expectSortSelected(page: Page, selected: string) {
  await expect(sortGroup(page).getByRole('link')).toHaveText(SORT_NAMES);
  for (const name of SORT_NAMES) {
    if (name === selected) {
      await expect(sortLink(page, name), `${name} が選択状態`).toHaveAttribute('aria-current', 'page');
    } else {
      await expect(sortLink(page, name), `${name} は選択状態でない`).not.toHaveAttribute('aria-current', /.*/);
    }
  }
}

test('TC-48 「制作年の新しい順」を選ぶと制作年の新しい順に並び、未入力は最後', async ({ page }) => {
  await page.goto('/works/');
  await sortLink(page, '制作年の新しい順').click();
  await expect(page).toHaveURL(/\/works\/\?sort=year_desc$/);
  expect(await cardTitles(page).allInnerTexts()).toEqual(BY_YEAR_DESC);
  await expect(countText(page)).toHaveText('9件の実績');
});

test('TC-49 「制作年の古い順」を選ぶと制作年の古い順に並び、未入力は最後', async ({ page }) => {
  await page.goto('/works/');
  await sortLink(page, '制作年の古い順').click();
  await expect(page).toHaveURL(/\/works\/\?sort=year_asc$/);
  expect(await cardTitles(page).allInnerTexts()).toEqual(BY_YEAR_ASC);
  await expect(countText(page)).toHaveText('9件の実績');
});

test('TC-50 制作年が未入力の「さくら学習室 様」はどちらの並べ替えでも最後に表示される', async ({ page }) => {
  for (const sort of ['year_desc', 'year_asc']) {
    await page.goto(`/works/?sort=${sort}`);
    const titles = await cardTitles(page).allInnerTexts();
    expect(titles, `${sort}：9件`).toHaveLength(9);
    expect(titles.at(-1), `${sort}：未入力は最後`).toBe(NO_YEAR_TITLE);
  }
});

test('TC-51 「飲食」を選んでから「制作年の古い順」を選ぶと、絞り込みを保ったまま並ぶ', async ({ page }) => {
  await page.goto('/works/');
  await filterButton(page, '飲食').click();
  await sortLink(page, '制作年の古い順').click();
  const url = new URL(page.url());
  expect(url.searchParams.get('industry')).toBe('food');
  expect(url.searchParams.get('sort')).toBe('year_asc');
  expect(await cardTitles(page).allInnerTexts()).toEqual(['ベーカリー麦の音 様', 'カフェ こもれび 様', '和食処 やまの葉 様']);
  await expect(countText(page)).toHaveText('3件の実績');
  await expectSelected(page, '飲食');
  await expectSortSelected(page, '制作年の古い順');
});

test('TC-52 「制作年の新しい順」を選んでから「サービス」を選ぶと、並べ替えを保ったまま絞り込む', async ({ page }) => {
  await page.goto('/works/');
  await sortLink(page, '制作年の新しい順').click();
  await filterButton(page, 'サービス').click();
  const url = new URL(page.url());
  expect(url.searchParams.get('industry')).toBe('service');
  expect(url.searchParams.get('sort')).toBe('year_desc');
  expect(await cardTitles(page).allInnerTexts()).toEqual(['はるかサービス 様', 'みどり不動産 様', 'さくら学習室 様']);
  await expect(countText(page)).toHaveText('3件の実績');
  await expectSelected(page, 'サービス');
  await expectSortSelected(page, '制作年の新しい順');
});

test('TC-53 「飲食」＋「制作年の新しい順」でも下書きの実績は表示されない', async ({ page }) => {
  await page.goto('/works/?industry=food&sort=year_desc');
  expect(await cardTitles(page).allInnerTexts()).toEqual(['和食処 やまの葉 様', 'カフェ こもれび 様', 'ベーカリー麦の音 様']);
  await expect(page.getByText(DRAFT_TITLE)).toHaveCount(0);
});

test('TC-54 「医療」＋「制作年の新しい順」は「該当する実績はありません。」「0件の実績」', async ({ page }) => {
  await page.goto('/works/?industry=medical&sort=year_desc');
  await expect(cards(page)).toHaveCount(0);
  await expect(page.getByText('該当する実績はありません。')).toBeVisible();
  await expect(countText(page)).toHaveText('0件の実績');
  await expectSelected(page, '医療');
  await expectSortSelected(page, '制作年の新しい順');
});

test('TC-55 ?sort=unknown と ?sort= は公開日の新しい順で表示される', async ({ page }) => {
  for (const path of ['/works/?sort=unknown', '/works/?sort=']) {
    const res = await page.goto(path);
    expect(res?.status(), path).toBe(200);
    expect(await cardTitles(page).allInnerTexts(), path).toEqual(ALL_TITLES);
    await expect(countText(page)).toHaveText('9件の実績');
    await expectSortSelected(page, '公開日の新しい順');
  }
});

test('TC-56 選んだ並べ替えだけに aria-current="page" が付く', async ({ page }) => {
  await page.goto('/works/');
  await expectSortSelected(page, '公開日の新しい順');
  for (const name of SORT_NAMES) {
    await sortLink(page, name).click();
    await expectSortSelected(page, name);
  }
});
