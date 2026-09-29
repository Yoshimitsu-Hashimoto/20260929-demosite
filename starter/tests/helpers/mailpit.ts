import { expect } from '@playwright/test';

/** 確認用メールボックス（Mailpit）の API。 */
const API = 'http://localhost:8091/api/v1';

type Address = { Name: string; Address: string };

export type MailSummary = {
  ID: string;
  Subject: string;
  To: Address[];
  ReplyTo: Address[];
};

export type Mail = MailSummary & { Text: string };

/** メールボックスを空にする。 */
export async function clearMailbox(): Promise<void> {
  const res = await fetch(`${API}/messages`, { method: 'DELETE' });
  expect(res.ok, 'Mailpit のメールを削除できること').toBeTruthy();
}

/** 届いているメールの一覧（新しい順）。 */
export async function listMails(): Promise<MailSummary[]> {
  const res = await fetch(`${API}/messages`);
  const data = (await res.json()) as { messages: MailSummary[] };
  return data.messages;
}

/** メール1通の詳細（本文を含む）。 */
export async function getMail(id: string): Promise<Mail> {
  const res = await fetch(`${API}/message/${id}`);
  return (await res.json()) as Mail;
}

/** メールが count 通届くまで待ち、届いたメールを返す。 */
export async function waitForMails(count: number, timeout = 10_000): Promise<MailSummary[]> {
  await expect
    .poll(async () => (await listMails()).length, { timeout, message: `メールが${count}通届くこと` })
    .toBe(count);
  return listMails();
}

/** 少し待ってもメールが届いていないことを確かめる。 */
export async function expectNoMail(waitMs = 1_500): Promise<void> {
  await new Promise((resolve) => setTimeout(resolve, waitMs));
  expect(await listMails(), 'メールが届いていないこと').toHaveLength(0);
}
