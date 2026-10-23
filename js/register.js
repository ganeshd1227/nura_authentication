import { api, $, $$, showAlert, hideAlert, setLoading, fieldError, initReveal } from "./common.js";
initReveal();
const form = $("#registerForm"), alertBox = $("#alert"), btn = $("#submit");
const pw = $("#password"), meter = $(".meter");
const checks = { len: (v) => v.length >= 8, upper: (v) => /[A-Z]/.test(v), num: (v) => /[0-9]/.test(v), sym: (v) => /[^A-Za-z0-9]/.test(v) };

pw.addEventListener("input", () => {
  let score = 0;
  for (const [k, fn] of Object.entries(checks)) {
    const ok = fn(pw.value);
    $(`[data-rule="${k}"]`).classList.toggle("ok", ok);
    if (ok) score++;
  }
  meter.dataset.s = pw.value ? score : 0;
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  hideAlert(alertBox);
  const f = (id) => $("#" + id);
  const errs = {
    username: f("username").value.trim().length < 3 ? "At least 3 characters." : "",
    email: f("email").validity.valid ? "" : "Enter a valid email address.",
    password: checks.len(pw.value) && checks.upper(pw.value) && checks.num(pw.value) ? "" : "Needs 8+ characters, an uppercase letter and a number.",
    age: f("age").value === "" || (f("age").value >= 13 && f("age").value <= 120) ? "" : "Age must be between 13 and 120.",
  };
  for (const [k, m] of Object.entries(errs)) fieldError(f(k), m);
  if (Object.values(errs).some(Boolean)) { $(".invalid input")?.focus(); return; }

  setLoading(btn, true);
  try {
    const r = await api("php/register.php", { method: "POST", data: {
      username: f("username").value.trim(), email: f("email").value.trim(), password: pw.value,
      name: f("name").value, age: f("age").value, bio: f("bio").value, interests: f("interests").value,
    }});
    if (r.success) {
      showAlert(alertBox, "Account created. Taking you to sign in…", "success");
      setTimeout(() => (location.href = "index.html"), 1100);
    } else showAlert(alertBox, r.message);
  } catch (err) { showAlert(alertBox, err.notJson ? "The server replied with a web page instead of data. If you are using a dev tunnel, set the port to Public, or open http://localhost:8000. Otherwise check the PHP error log." : err.timeout ? "The server did not respond. Check that PHP, MySQL and MongoDB are running (and the tunnel port is Public)." : "Something went wrong. Please try again."); }
  finally { setLoading(btn, false); }
});
