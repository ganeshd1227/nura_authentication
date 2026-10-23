import { api, $, showAlert, hideAlert, setLoading, fieldError, parseInterests } from "./common.js";
const alertBox = $("#alert"), form = $("#profileForm"), btn = $("#submit");
let current = { name: "", interests: [] };

function renderTags(list) {
  const wrap = $("#interestTags"); wrap.replaceChildren();
  list.forEach((t) => { const s = document.createElement("span"); s.className = "tag"; s.textContent = t; wrap.appendChild(s); });
  $("#interestsBlock").hidden = list.length === 0;
}
function renderIdentity(username, name) {
  $("#displayName").textContent = name || username;
  $("#avatar").textContent = (name || username || "?").trim().charAt(0).toUpperCase();
}

async function load() {
  let r;
  try { r = await api("php/profile.php"); } catch { return location.replace("index.html"); }
  if (!r.success) return location.replace("index.html");
  document.querySelectorAll(".skeleton").forEach((n) => n.classList.remove("skeleton"));
  const { user, profile } = r;
  current = { username: user.username, ...profile };
  $("#handle").textContent = "@" + user.username;
  $("#email").textContent = user.email;
  $("#since").textContent = new Date(user.created_at.replace(" ", "T")).toLocaleDateString(undefined, { year: "numeric", month: "long", day: "numeric" });
  $("#name").value = profile.name || "";
  $("#age").value = profile.age ?? "";
  $("#bio").value = profile.bio || "";
  $("#interests").value = (profile.interests || []).join(", ");
  $("#ageView").textContent = profile.age ? `${profile.age} years` : "—";
  $("#bioView").textContent = profile.bio || "No bio yet.";
  renderIdentity(user.username, profile.name);
  renderTags(profile.interests || []);
}

form.addEventListener("submit", async (e) => {
  e.preventDefault(); hideAlert(alertBox);
  const age = $("#age");
  const bad = age.value !== "" && (age.value < 13 || age.value > 120);
  fieldError(age, bad ? "Age must be between 13 and 120." : "");
  if (bad) return age.focus();
  setLoading(btn, true);
  try {
    const r = await api("php/profile.php", { method: "POST", data: { name: $("#name").value, age: age.value, bio: $("#bio").value, interests: $("#interests").value } });
    showAlert(alertBox, r.message, r.success ? "success" : "error");
    if (r.status === 401) return location.replace("index.html");
    if (r.success) {
      const list = parseInterests($("#interests").value);
      renderTags(list); renderIdentity(current.username, $("#name").value.trim());
      $("#ageView").textContent = age.value ? `${age.value} years` : "—";
      $("#bioView").textContent = $("#bio").value.trim() || "No bio yet.";
    }
  } catch { showAlert(alertBox, "Could not update profile. Please try again.", "error"); }
  finally { setLoading(btn, false); }
});

$("#logoutBtn").addEventListener("click", async () => {
  try { await api("php/logout.php", { method: "POST" }); } finally { location.href = "index.html"; }
});
load();
