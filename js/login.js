import { api, $, showAlert, hideAlert, setLoading, fieldError, initReveal } from "./common.js";
initReveal();
const form = $("#loginForm"), alertBox = $("#alert"), btn = $("#submit");

// Already signed in? Skip straight to the profile.
api("php/session.php").then((r) => { if (r.authenticated) location.replace("profile.html"); }).catch(() => {});

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  hideAlert(alertBox);
  const email = $("#email"), password = $("#password");
  fieldError(email, email.validity.valid ? "" : "Enter a valid email address.");
  fieldError(password, password.value ? "" : "Enter your password.");
  if (!email.validity.valid || !password.value) return;
  setLoading(btn, true);
  try {
    const r = await api("php/login.php", { method: "POST", data: { email: email.value.trim(), password: password.value } });
    if (r.success) location.href = "profile.html";
    else showAlert(alertBox, r.message);
  } catch (err) { showAlert(alertBox, err.notJson ? "The server replied with a web page instead of data. If you are using a dev tunnel, set the port to Public, or open http://localhost:8000. Otherwise check the PHP error log." : err.timeout ? "The server did not respond. Check that PHP, MySQL and Redis are running (and the tunnel port is Public)." : "Login failed. Please try again."); }
  finally { setLoading(btn, false); }
});
