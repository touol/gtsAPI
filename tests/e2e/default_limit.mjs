// 100 строк на страницу по умолчанию (gtsAPI 1.2.16): options таблицы без limit в конфиге отдаёт limit 100,
// таблица с limit в конфиге — свой limit; на странице PVTables строк больше 10 (если в таблице их больше).
// Логин ai — modx.pl/Extras/.claude/ai_user.env (на деве modx28 заводится тем же ai_user.php).
//   node tests/e2e/default_limit.mjs [<base>] [<таблица без limit>] [<страница с PVTable>]
// По умолчанию — дев modx28: таблица NFBlockType, страница admin/manager/orders.html.
import fs from 'fs'
import path from 'path'
import { pathToFileURL } from 'url'

const vkExtras = 'V:/OSPanel/home/modx.pl/public/Extras'
const env = Object.fromEntries(fs.readFileSync(path.join(vkExtras, '.claude/ai_user.env'), 'utf8')
  .split(/\r?\n/).filter(l => /=/.test(l)).map(l => { const i = l.indexOf('='); return [l.slice(0, i).trim(), l.slice(i + 1).trim()] }))
const base = process.argv[2] || 'http://modx28.loc'
const noLimitTable = process.argv[3] || 'NFBlockType'
const pagePath = process.argv[4] || 'admin/manager/orders.html'

const { chromium } = await import(pathToFileURL(path.join(vkExtras, 'GtsApiTests/node_modules/playwright/index.mjs')).href)
const browser = await chromium.launch()
const page = await (await browser.newContext({ viewport: { width: 1600, height: 3000 } })).newPage()
await page.goto(base + '/manager/')
await page.fill('input[name="username"]', env.AI_USERNAME)
await page.fill('input[name="password"]', env.AI_PASSWORD)
await page.locator('button[type="submit"], input[type="submit"]').first().click()
await page.waitForSelector('input[name="username"]', { state: 'detached', timeout: 20000 })

const errs = []
const check = (cond, msg) => { console.log((cond ? 'ok   ' : 'FAIL ') + msg); if (!cond) errs.push(msg) }
const options = async table => {
  const r = await page.request.post(`${base}/api/${table}/?api_action=options`, { data: {} })
  try { return await r.json() } catch { return {} }
}
const o = await options(noLimitTable)
check(o.success && +o.data?.limit === 100, `${noLimitTable} (без limit в конфиге): limit = ${o.data?.limit}`)

// страница: строк на первой странице больше 10 (виртуализатор рисует видимые — окно высокое)
let total = 0
page.on('response', async r => {
  if (/api_action=read|\/api\//.test(r.url()) && r.request().method() === 'POST') {
    try { const j = await r.json(); if (j?.data?.total) total = Math.max(total, +j.data.total) } catch {}
  }
})
await page.goto(`${base}/${pagePath}`, { waitUntil: 'load', timeout: 60000 })
await page.waitForSelector('table tbody tr', { timeout: 30000 }).catch(() => {})
await page.waitForTimeout(2000)
const rows = await page.locator('table tbody tr').count()
const pager = await page.locator('select:visible').first().inputValue().catch(() => '')
check(total <= 10 || rows > 10, `${pagePath}: строк на экране ${rows} (всего ${total}), «Строк:» ${pager}`)

await browser.close()
console.log(errs.length ? `ОШИБОК: ${errs.length}` : 'OK')
process.exit(errs.length ? 1 : 0)
