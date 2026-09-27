# Security & Code Quality Audit — ESP8266_Temperature-Humidity

Audit performed by IBM Bob 2.0 (Ask mode) as part of the BobGuard hackathon project.
Findings are grouped into fix batches, in the order they should be executed.

Status legend: ⬜ Not started · 🟡 In progress · ✅ Fixed & verified

---

## Batch 1 — Critical (fix first, do together)

These three live in the same files and should be fixed in one pass.

- ⬜ **#1 SQL Injection** — `logs.php` (lines 8–9, 39, 49). Raw `$_GET` values interpolated into SQL. Fix: prepared statements with bound parameters.
- ⬜ **#2 Hardcoded DB root credentials** — `logs.php:3`, `get_sensor_data.php:5`. `root` user, empty password. Fix: dedicated low-privilege MySQL user, credentials outside web root (`.env` / `config.php`).
- ⬜ **#6 Unauthenticated write endpoint** — `logs.php` accepts unauthenticated GET writes. Fix: shared-secret token check, sent from firmware.

## Batch 2 — High severity

- ⬜ **#4 `String` heap fragmentation** — `main.ino:49-53`. Replace `String` concatenation with fixed `char` buffer + `snprintf()`.
- ⬜ **#5 No range validation on sensor values** — firmware side (`main.ino`) and PHP side (`logs.php`). Add physical-range checks (-40–80°C, 0–100% humidity) on both ends.
- ⬜ **#7 CORS wildcard** — `get_sensor_data.php:3`. Restrict `Access-Control-Allow-Origin` to the actual dashboard origin.

## Batch 3 — Medium severity (dashboard hardening)

- ⬜ **#8 Unhandled fetch status** — `index.html:135-136`. Add `if (!response.ok) throw new Error(...)`.
- ⬜ **#9 `setInterval` handle discarded** — `index.html:175`. Store the interval ID.
- ⬜ **#10 Race condition on overlapping fetches** — `index.html:133-171`. Add an in-flight guard flag.
- ⬜ **#12 Missing Content-Security-Policy** — add CSP meta tag restricting script/style/font sources.
- ⬜ **#13 Repeated DOM queries** — cache `getElementById` results at module scope.

## Batch 4 — Minor / informational

- ⬜ **#14 Host header mismatch** — `main.ino:55` sends `Host: localhost` but connects to `192.168.1.81`.
- ⬜ **#15 No WiFi connect timeout** — `main.ino:17-20` blocks forever if SSID unreachable. Add retry counter + `ESP.restart()`.
- ⬜ **#16 DB connection error leaked** — `logs.php:5` echoes `$conn->connect_error` to the HTTP response.
- ⬜ **#17 Same leak** — `get_sensor_data.php:7`.

---

## Already fixed (prior to this audit)

- ✅ WiFi SSID/password removed from `main.ino` in a later commit *(note: still present in git history — see history-cleanup step)*

## Outstanding cross-cutting item

- ⬜ **Git history cleanup** — old WiFi credentials remain visible in commit history even though removed from the current file. Requires `git filter-repo` or BFG Repo-Cleaner + force-push, and rotating the actual WiFi password since it was publicly exposed.
