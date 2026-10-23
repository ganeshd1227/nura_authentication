# Nura Auth v2

Same stack and API as the original assignment (MySQL credentials · MongoDB profile · Redis sessions),
with a brand-new front end: a light "paper & ink" design, vanilla ES modules (no jQuery/Bootstrap/build step).

## What changed
- **UI**: split-screen auth pages, underline inputs, pill buttons, serif display type (Fraunces + DM Sans/Mono), terracotta accent. Profile page is an ID card + editor.
- **UX**: inline field validation (no `alert()`), show/hide password, live password-strength checklist, loading states, skeleton loading on profile, auto-redirect if already signed in, 401 handling.
- **Backend** (API contract unchanged): `REDIS_SCHEME` env (`tls` for cloud, `tcp` for local); session cookie `secure` flag only when served over HTTPS.

## Structure
```
index.html  register.html  profile.html
css/style.css
js/common.js  login.js  register.js  profile.js
php/  config db mongo redis register login profile logout
sql/schema.sql  .env.example  composer.json  Dockerfile
```

## Run
1. Import `sql/schema.sql` into MySQL.
2. `cp .env.example .env` and fill in MySQL, `MONGO_URI`, Redis values.
3. `composer install` (needs the `mongodb` PHP extension) — or deploy the `Dockerfile`.
4. Serve this folder with Apache/PHP and open `index.html`.

## Using a local MongoDB server (Community Server app)
1. Install MongoDB Community Server (https://www.mongodb.com/try/download/community) — it runs as a Windows service on port 27017. (Optional: MongoDB Compass to browse data.)
2. In `.env` set:
   ```
   MONGO_URI=mongodb://127.0.0.1:27017
   MONGO_DATABASE=authentication
   ```
3. Enable the PHP `mongodb` extension (`extension=mongodb` in php.ini) and run `composer install`.
4. Restart Apache. The `authentication` database and `profiles` collection are created on first registration.
