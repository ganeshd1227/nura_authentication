# Free hosting guide (Render + Atlas + Upstash + Aiven)

| Part | Service | Free limits (checked Oct 2026) |
|---|---|---|
| PHP app (Docker) | Render web service | Sleeps after 15 min idle (~1 min wake-up), 750 h/month |
| MongoDB | MongoDB Atlas M0 | 0.5 GB, 500 connections |
| Redis | Upstash | 250 MB, 500K commands/month, TLS |
| MySQL | Aiven free MySQL | 1 GB, may be powered off if idle |

## 1. MongoDB Atlas
Create free M0 cluster → Database Access: add user → Network Access: allow 0.0.0.0/0 →
Connect → Drivers → copy `mongodb+srv://user:pass@cluster.xxxxx.mongodb.net/?retryWrites=true&w=majority`.

## 2. Upstash Redis
Create database (any region) → copy Endpoint (host), Port and Password from the "TCP" section.
Use `REDIS_SCHEME=tls`.

## 3. MySQL (Aiven)
Create a free MySQL service → copy Host, Port, User, Password. Open a SQL client
(or Aiven's web console / `mysql` CLI) and run `sql/schema.sql`. Note: Aiven's default database is
`defaultdb` — either set `MYSQL_DATABASE=defaultdb` and remove the first two lines
(CREATE DATABASE / USE) of schema.sql, or create a database called `authentication`.
Set `MYSQL_SSL=true`.

## 4. Push to GitHub
```
git init && git add . && git commit -m "Nura auth v2"
git branch -M main && git remote add origin https://github.com/<you>/<repo>.git && git push -u origin main
```
(`.env` is git-ignored — never commit it.)

## 5. Deploy on Render
New → Web Service → connect repo → Language/Runtime: **Docker** → Instance type: **Free**.
(Or New → Blueprint, which reads `render.yaml`.)
Add Environment variables: MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE,
MYSQL_SSL=true, MONGO_URI, MONGO_DATABASE, REDIS_HOST, REDIS_PORT, REDIS_PASSWORD, REDIS_SCHEME=tls.
Deploy (first build takes several minutes). Open `https://<name>.onrender.com/index.html`.

## Troubleshooting
- "Database unavailable": MySQL host/port/user/password wrong, or `MYSQL_SSL` missing.
- "Profile store unavailable": bad `MONGO_URI`, or Atlas Network Access doesn't allow 0.0.0.0/0.
- "Session store unavailable": Redis host/password wrong, or `REDIS_SCHEME` not `tls`.
- First request is slow: free Render service was asleep — wait ~1 minute.
- Render → Logs tab shows PHP errors.
