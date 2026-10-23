// Shared helpers: API client, form utilities, password reveal.
export async function api(path, { method = "GET", data } = {}) {
  const opts = { method, credentials: "include" };
  if (data) opts.body = new URLSearchParams(data); // PHP reads $_POST
  const ctrl = new AbortController();
  opts.signal = ctrl.signal;
  const timer = setTimeout(() => ctrl.abort(), 15000); // never spin forever
  let res;
  try { res = await fetch(path, opts); }
  catch (e) { throw Object.assign(new Error(e.name === "AbortError" ? "Request timed out" : "Network error"), { timeout: e.name === "AbortError" }); }
  finally { clearTimeout(timer); }
  let json = null;
  try { json = await res.json(); } catch { /* non-JSON error */ }
  if (!json) throw Object.assign(new Error("Unexpected server response"), { status: res.status, notJson: true });
  json.status = res.status;
  return json;
}

export const $ = (s, r = document) => r.querySelector(s);
export const $$ = (s, r = document) => [...r.querySelectorAll(s)];

export function showAlert(el, message, type = "error") {
  el.textContent = message;
  el.className = "alert show" + (type === "success" ? " success" : "");
  el.setAttribute("role", type === "success" ? "status" : "alert");
}
export function hideAlert(el) { el.className = "alert"; el.textContent = ""; }

export function setLoading(btn, on) {
  btn.disabled = on;
  btn.classList.toggle("loading", on);
}

export function fieldError(input, message) {
  const f = input.closest(".field");
  f.classList.toggle("invalid", !!message);
  let e = f.querySelector(".err");
  if (!e) { e = document.createElement("div"); e.className = "err"; e.id = input.id + "-err"; f.appendChild(e); }
  e.textContent = message || "";
  input.setAttribute("aria-invalid", message ? "true" : "false");
  input.setAttribute("aria-describedby", e.id);
}

export function initReveal() {
  $$(".reveal").forEach((b) => {
    b.addEventListener("click", () => {
      const i = $("#" + b.dataset.for);
      const show = i.type === "password";
      i.type = show ? "text" : "password";
      b.textContent = show ? "Hide" : "Show";
      b.setAttribute("aria-pressed", String(show));
    });
  });
}

export const parseInterests = (s) => s.split(",").map((x) => x.trim()).filter(Boolean);
