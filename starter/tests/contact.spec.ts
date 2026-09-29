import { expect, test, type Locator, type Page } from '@playwright/test';
import { clearMailbox, expectNoMail, getMail, waitForMails } from './helpers/mailpit';

/**
 * 5. お問い合わせ（/contact/） TC-26〜TC-47
 * 各テストの前に確認用メールボックスを空にする。
 */

const VALID = {
  name: '山田 太郎',
  email: 'yamada@example.test',
  message: 'Webサイト制作について相談したいです。',
  consent: true,
};
type Input = typeof VALID;

const MSG = {
  name: 'お名前を入力してください。',
  emailEmpty: 'メールアドレスを入力してください。',
  emailFormat: 'メールアドレスの形式が正しくありません。',
  message: 'お問い合わせ内容を入力してください。',
  consent: 'プライバシーポリシーへの同意が必要です。',
};
const SUMMARY = '入力内容に問題があります。';

const nameField = (page: Page) => page.getByLabel('お名前');
const emailField = (page: Page) => page.getByLabel('メールアドレス');
const messageField = (page: Page) => page.getByLabel('お問い合わせ内容');
const consentBox = (page: Page) => page.getByRole('checkbox', { name: /プライバシーポリシー/ });

test.beforeEach(async () => {
  await clearMailbox();
});

/** お問い合わせページを開いて入力し、送信する。 */
async function submit(page: Page, overrides: Partial<Input> = {}) {
  const input = { ...VALID, ...overrides };
  await page.goto('/contact/');
  await fillAndSubmit(page, input);
}

async function fillAndSubmit(page: Page, input: Input) {
  await nameField(page).fill(input.name);
  await emailField(page).fill(input.email);
  await messageField(page).fill(input.message);
  await consentBox(page).setChecked(input.consent);
  await Promise.all([
    page.waitForResponse((res) => res.request().method() === 'POST'),
    page.getByRole('button', { name: /送信/ }).click(),
  ]);
  await page.waitForLoadState('load');
}

/** 送信できないこと：お問い合わせページに留まり、送信完了ページに移動せず、メールも届かない。 */
async function expectNotSent(page: Page) {
  await expect(page).toHaveURL(/\/contact\/$/);
  await expect(page.getByText('お問い合わせを受け付けました。')).toHaveCount(0);
  await expectNoMail();
}

/** フォームの上に問題の一覧があり、該当する項目の下にもメッセージがあること。 */
async function expectErrors(page: Page, expected: { message: string; field: Locator }[]) {
  const summary = page.getByText(SUMMARY);
  await expect(summary).toBeVisible();
  const formTop = (await page.locator('form').boundingBox())!.y;
  const summaryY = (await summary.boundingBox())!.y;
  const firstFieldY = (await nameField(page).boundingBox())!.y;
  expect(summaryY, '問題の一覧はフォームの項目より上').toBeLessThan(firstFieldY);
  expect(summaryY).toBeGreaterThanOrEqual(formTop - 200);

  for (const { message, field } of expected) {
    const occurrences = page.getByText(message, { exact: true });
    // フォームの上の一覧と、項目の下の2か所
    await expect(occurrences, `「${message}」が一覧と項目の下に表示される`).toHaveCount(2);
    const fieldBox = (await field.boundingBox())!;
    const underField = (await occurrences.nth(1).boundingBox())!;
    expect(underField.y, `「${message}」が項目の下にある`).toBeGreaterThan(fieldBox.y);
    expect((await occurrences.nth(0).boundingBox())!.y, `「${message}」の1つ目はフォームの上の一覧`).toBeLessThan(firstFieldY);
  }
}

// ---------------------------------------------------------------------------
// 正常系
// ---------------------------------------------------------------------------

test('TC-26 正しく入力して送信すると送信完了ページに移動し、メールが1通届く', async ({ page }) => {
  await submit(page);
  await expect(page).toHaveURL(/\/thanks\/$/);
  await expect(page.getByText('お問い合わせを受け付けました。')).toBeVisible();
  await waitForMails(1);
});

test('TC-27 届いたメールの宛先・件名・返信先・本文', async ({ page }) => {
  await submit(page);
  const [summary] = await waitForMails(1);
  const mail = await getMail(summary.ID);
  expect(mail.To.map((a) => a.Address)).toEqual(['admin@example.test']);
  expect(mail.Subject).toBe('【株式会社ひなた】お問い合わせ（山田 太郎 様）');
  expect(mail.ReplyTo.map((a) => a.Address)).toEqual(['yamada@example.test']);
  expect(mail.Text).toContain(VALID.name);
  expect(mail.Text).toContain(VALID.email);
  expect(mail.Text).toContain(VALID.message);
});

test('TC-28 複数行のお問い合わせ内容がそのままメール本文に含まれる', async ({ page }) => {
  await submit(page, { message: '1行目\n2行目' });
  await expect(page).toHaveURL(/\/thanks\/$/);
  const [summary] = await waitForMails(1);
  expect((await getMail(summary.ID)).Text).toMatch(/1行目\r?\n2行目/);
});

test('TC-29 お名前の前後の空白は取り除かれる', async ({ page }) => {
  await submit(page, { name: '　山田 太郎 ' });
  await expect(page).toHaveURL(/\/thanks\/$/);
  const [summary] = await waitForMails(1);
  expect(summary.Subject).toBe('【株式会社ひなた】お問い合わせ（山田 太郎 様）');
});

// ---------------------------------------------------------------------------
// 異常系（未入力・同意なし）
// ---------------------------------------------------------------------------

test('TC-30 お名前が空欄だと送信できない', async ({ page }) => {
  await submit(page, { name: '' });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.name, field: nameField(page) }]);
});

test('TC-31 メールアドレスが空欄だと送信できない', async ({ page }) => {
  await submit(page, { email: '' });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.emailEmpty, field: emailField(page) }]);
});

test('TC-32 お問い合わせ内容が空欄だと送信できない', async ({ page }) => {
  await submit(page, { message: '' });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.message, field: messageField(page) }]);
});

test('TC-33 同意にチェックがないと送信できない', async ({ page }) => {
  await submit(page, { consent: false });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.consent, field: consentBox(page) }]);
});

test('TC-34 何も入力しないと4つのメッセージがすべて表示される', async ({ page }) => {
  await submit(page, { name: '', email: '', message: '', consent: false });
  await expectNotSent(page);
  await expectErrors(page, [
    { message: MSG.name, field: nameField(page) },
    { message: MSG.emailEmpty, field: emailField(page) },
    { message: MSG.message, field: messageField(page) },
    { message: MSG.consent, field: consentBox(page) },
  ]);
});

test('TC-35 お名前が半角スペースだけなら空欄とみなす', async ({ page }) => {
  await submit(page, { name: '   ' });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.name, field: nameField(page) }]);
});

test('TC-36 お名前が全角スペースだけなら空欄とみなす', async ({ page }) => {
  await submit(page, { name: '　　' });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.name, field: nameField(page) }]);
});

test('TC-37 お問い合わせ内容が改行とスペースだけなら空欄とみなす', async ({ page }) => {
  await submit(page, { message: '\n  \n　\n' });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.message, field: messageField(page) }]);
});

test('TC-38 メールアドレスがスペースだけなら「入力してください」（形式エラーではない）', async ({ page }) => {
  await submit(page, { email: '   ' });
  await expectNotSent(page);
  await expectErrors(page, [{ message: MSG.emailEmpty, field: emailField(page) }]);
  await expect(page.getByText(MSG.emailFormat)).toHaveCount(0);
});

// ---------------------------------------------------------------------------
// 異常系（メールアドレスの形式）
// ---------------------------------------------------------------------------

for (const [id, email, note] of [
  ['TC-39', 'yamada', '@ がない'],
  ['TC-40', '@example.test', '@ の前が空'],
  ['TC-41', 'yamada@', '@ の後が空'],
  ['TC-42', 'yamada@example', '@ の後に . がない'],
] as const) {
  test(`${id} メールアドレスが「${email}」（${note}）だと送信できない`, async ({ page }) => {
    await submit(page, { email });
    await expectNotSent(page);
    await expectErrors(page, [{ message: MSG.emailFormat, field: emailField(page) }]);
  });
}

test('TC-43 メールアドレスの前後の空白は取り除かれ、送信できる', async ({ page }) => {
  await submit(page, { email: ' yamada@example.test ' });
  await expect(page).toHaveURL(/\/thanks\/$/);
  const [summary] = await waitForMails(1);
  expect(summary.ReplyTo.map((a) => a.Address)).toEqual(['yamada@example.test']);
});

// ---------------------------------------------------------------------------
// 入力内容の保持
// ---------------------------------------------------------------------------

test('TC-44 送信できなかったとき、入力内容と同意のチェックが残る', async ({ page }) => {
  await submit(page, { email: 'yamada' });
  await expectNotSent(page);
  await expect(nameField(page)).toHaveValue(VALID.name);
  await expect(emailField(page)).toHaveValue('yamada');
  await expect(messageField(page)).toHaveValue(VALID.message);
  await expect(consentBox(page)).toBeChecked();
});

test('TC-45 同意なしで送信できなかったとき、3項目が残り、チェックは入っていないまま', async ({ page }) => {
  await submit(page, { consent: false });
  await expectNotSent(page);
  await expect(nameField(page)).toHaveValue(VALID.name);
  await expect(emailField(page)).toHaveValue(VALID.email);
  await expect(messageField(page)).toHaveValue(VALID.message);
  await expect(consentBox(page)).not.toBeChecked();
});

test('TC-46 複数の条件に当てはまると、当てはまるメッセージがすべて表示される', async ({ page }) => {
  await submit(page, { name: '', email: 'yamada@example', consent: false });
  await expectNotSent(page);
  await expectErrors(page, [
    { message: MSG.name, field: nameField(page) },
    { message: MSG.emailFormat, field: emailField(page) },
    { message: MSG.consent, field: consentBox(page) },
  ]);
  await expect(page.getByText(MSG.message)).toHaveCount(0);
});

test('TC-47 エラーのあと直して送信すると、メールは1通だけ届く', async ({ page }) => {
  await submit(page, { email: 'yamada' });
  await expectNotSent(page);
  await emailField(page).fill(VALID.email);
  await Promise.all([
    page.waitForResponse((res) => res.request().method() === 'POST'),
    page.getByRole('button', { name: /送信/ }).click(),
  ]);
  await expect(page).toHaveURL(/\/thanks\/$/);
  await expect(page.getByText('お問い合わせを受け付けました。')).toBeVisible();
  await waitForMails(1);
  await new Promise((resolve) => setTimeout(resolve, 1_500));
  await waitForMails(1);
});
