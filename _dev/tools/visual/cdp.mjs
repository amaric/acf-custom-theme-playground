// Minimal headless-Chrome driver over the DevTools protocol.
// No npm dependencies: uses Node's built-in fetch + WebSocket (Node 22+).

import { spawn } from 'node:child_process';
import { existsSync, mkdtempSync, readFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

export const THEME_ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '../../..');
export const OUT_DIR = join(THEME_ROOT, '_dev/tools/visual/out');

const CHROME_CANDIDATES = [
  process.env.CHROME_PATH,
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
  '/Applications/Chromium.app/Contents/MacOS/Chromium',
  '/usr/bin/google-chrome',
  '/usr/bin/chromium',
].filter(Boolean);

const wait = (ms) => new Promise((r) => setTimeout(r, ms));

// Site URL: --url flag beats PROXY_URL from .env.
export function siteUrl(args) {
  const flag = args.find((a) => a.startsWith('--url='));
  if (flag) return flag.slice(6);

  const envFile = join(THEME_ROOT, '.env');
  if (existsSync(envFile)) {
    const match = readFileSync(envFile, 'utf8').match(/^PROXY_URL=["']?([^"'\n]+)/m);
    if (match) return match[1].trim();
  }
  throw new Error('No site URL: set PROXY_URL in .env or pass --url=https://…');
}

export async function launch() {
  const chromePath = CHROME_CANDIDATES.find((p) => existsSync(p));
  if (!chromePath) throw new Error('Chrome not found — set CHROME_PATH.');

  const profile = mkdtempSync(join(tmpdir(), 'elysian-visual-'));
  const chrome = spawn(chromePath, [
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    '--ignore-certificate-errors', // Local's self-signed HTTPS
    '--remote-debugging-port=0',
    `--user-data-dir=${profile}`,
    'about:blank',
  ], { stdio: 'ignore' });

  // Chrome writes the port it picked to DevToolsActivePort.
  const portFile = join(profile, 'DevToolsActivePort');
  for (let i = 0; i < 100 && !existsSync(portFile); i++) await wait(100);
  const port = readFileSync(portFile, 'utf8').split('\n')[0];

  let targets = [];
  for (let i = 0; i < 50 && !targets.length; i++) {
    targets = (await (await fetch(`http://127.0.0.1:${port}/json`)).json()).filter((t) => t.type === 'page');
    if (!targets.length) await wait(100);
  }

  const ws = new WebSocket(targets[0].webSocketDebuggerUrl);
  await new Promise((r, j) => { ws.onopen = r; ws.onerror = j; });

  let id = 0;
  const pending = new Map();
  const listeners = new Map();
  ws.onmessage = (event) => {
    const msg = JSON.parse(event.data);
    if (msg.id && pending.has(msg.id)) {
      const { resolve: ok, reject } = pending.get(msg.id);
      pending.delete(msg.id);
      msg.error ? reject(new Error(msg.error.message)) : ok(msg.result);
    } else if (msg.method && listeners.has(msg.method)) {
      listeners.get(msg.method).forEach((fn) => fn(msg.params));
    }
  };

  const send = (method, params = {}) => new Promise((ok, reject) => {
    const callId = ++id;
    pending.set(callId, { resolve: ok, reject });
    ws.send(JSON.stringify({ id: callId, method, params }));
  });

  const once = (method) => new Promise((r) => {
    const fn = (params) => {
      listeners.set(method, listeners.get(method).filter((f) => f !== fn));
      r(params);
    };
    listeners.set(method, [...(listeners.get(method) || []), fn]);
  });

  const evaluate = async (expression) => {
    const res = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    if (res.exceptionDetails) throw new Error(res.exceptionDetails.exception?.description || 'evaluate failed');
    return res.result.value;
  };

  await send('Page.enable');

  return {
    send,
    evaluate,
    wait,
    // Viewport: < 1024 is emulated as a touch/mobile device, like a real phone/tablet.
    async viewport(width, height = 900) {
      await send('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: width < 1024 });
    },
    async open(url, settle = 1500) {
      const loaded = once('Page.loadEventFired');
      await send('Page.navigate', { url });
      await Promise.race([loaded, wait(15000)]);
      await wait(settle); // fonts, Swiper init, transitions
    },
    async close() {
      ws.close();
      chrome.kill();
      await wait(200);
      rmSync(profile, { recursive: true, force: true });
    },
  };
}
