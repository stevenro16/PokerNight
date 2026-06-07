# Poker Night — CLAUDE.md

Project context and architectural decisions for this codebase. Read this before making changes.

---

## What This Is

A private poker group social site. Users create groups, schedule poker nights, upload photos, track attendance and winners, and view a win-count leaderboard. Built for Steve's personal use and friend groups.

**Live URL:** `https://rapidinsightdesigns.com/pokernight/` (subdirectory install on shared cPanel hosting)  
**Local dev:** `php artisan serve --port=9000` → `http://127.0.0.1:9000`  
**Repo:** `https://github.com/stevenro16/PokerNight`

---

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 / PHP 8.3 |
| Database (local) | SQLite (`database/database.sqlite`) |
| Database (production) | MySQL on cPanel shared hosting |
| CSS | Tailwind CSS v4 via `@tailwindcss/vite` |
| JS | Alpine.js v3 + `@alpinejs/persist` |
| Build | Vite with `laravel-vite-plugin` |
| File storage | Laravel Storage (`public` disk) → `public/storage` symlink |

---

## Deployment Rules (Critical)

**No npm on the production server.** Compiled assets must be built locally and committed to git.

- Run `npm run build` locally whenever CSS or JS changes, then commit `public/build/`.
- After `git pull` on the server: `php artisan migrate` (no npm, no build step needed).
- `public/build/` is **not** gitignored — it's intentionally committed.

**Production subdirectory setup:**
- `APP_URL` in the server's `.env` must be `https://rapidinsightdesigns.com/pokernight` (no trailing slash).
- The cPanel document root for `/pokernight` must point to the Laravel project's `public/` folder.
- If CSS breaks after a git pull, the first things to check: `APP_URL` in `.env`, and run `php artisan config:clear && php artisan view:clear`.

**After any migration:** run `php artisan migrate` on the server. If artisan isn't available, apply the SQL manually in phpMyAdmin and insert a row into the `migrations` table.

---

## Architecture Decisions

### UUID Primary Keys
All models use a `HasUuidKey` trait (`app/Traits/HasUuidKey.php`) that auto-generates a 32-char hex UUID on `creating`. PKs are `CHAR(32)`, `getIncrementing()` returns false.

### Custom Timestamp Column Names
All models use `createdAt` / `updatedAt` (camelCase) instead of Laravel's default snake_case. Every model declares:
```php
const CREATED_AT = 'createdAt';
const UPDATED_AT = 'updatedAt';
```

### No Soft Deletes
Hard deletes throughout. `isActive` booleans used instead for groups and users. When a poker night is deleted, its images, attendees, and comments are manually deleted first (no DB cascades defined).

### Two-Table Membership Model
There are two separate concepts:
- **`group_members`** — who has access to the group (auth/access control). Has `role` (OWNER or MEMBER).
- **`group_players`** — the roster of players tracked for statistics. Has `name`, `nickname`, `photo_path`. `user_id` is nullable (supports unlinked guest players).

A user can exist in `group_players` without being in `group_members` (invited guest), and vice versa if something went wrong. The login flow auto-heals this by creating `group_members` records for any `group_players` rows already linked to the user's ID.

### GroupPlayer as the Identity for Stats
All attendance records (`game_attendees`) link to `group_player_id`, not directly to `user_id`. This allows guests (no account) to be tracked. `user_id` on `game_attendees` is nullable and kept for convenience queries but `group_player_id` is the authoritative link.

`GroupPlayer.displayName()` returns nickname if set, otherwise name.

### Inline Styles Over Tailwind Arbitrary Classes
Tailwind v4 arbitrary classes (e.g. `h-[19.2rem]`) require a full `npm run build` to appear in the CSS bundle. Since builds must be done locally and committed, any sizing/positioning that's hard to express in standard Tailwind uses inline `style=""` attributes instead. This avoids invisible layout breakages from missing compiled classes.

### Server-Rendered Images + Alpine Opacity for Slideshows
Group cards and poker night cards show cycling image slideshows. Images are rendered server-side in a `@foreach` loop (all `<img>` tags in the HTML). Alpine.js only toggles opacity classes (`opacity-0` / `opacity-100`) using PHP integer counts. This avoids:
- Flash of empty content from `x-for` templates
- JSON escaping issues (`{{ $json }}` Blade-escapes quotes, breaking Alpine)

The pattern for cycling: `x-init="if (COUNT > 1) setInterval(() => idx = (idx + 1) % COUNT, 3500)"` where `COUNT` is a PHP integer echoed directly.

### Alpine Toggles Use Hidden Inputs, Not Checkboxes
Browser checkboxes only submit when checked, so "off" state isn't sent. Alpine toggles (e.g. invite code on/off) use `<input type="hidden" :value="on ? '1' : '0'">` bound to an Alpine boolean. The controller uses `$request->boolean('field_name')` which correctly treats `'0'` as false.

---

## Database Schema

### `users`
`id` (char32 UUID), `username` (unique), `email` (unique), `password`, `role` (USER/ADMIN/SUPERADMIN), `isActive` (bool), `avatar_url` (nullable), `remember_token`, `createdAt`, `updatedAt`

### `poker_groups`
`id`, `name`, `description`, `owner_id` → users, `invite_code` (8-char, unique, auto-generated), `isActive`, `avatar_path` (nullable — group icon image), `invite_enabled` (bool, default true), `createdAt`, `updatedAt`

### `group_members`
`id`, `group_id` → poker_groups, `user_id` → users, `role` (OWNER/MEMBER), `joined_at`

### `group_players`
`id`, `group_id` → poker_groups, `user_id` → users (nullable), `name`, `nickname` (nullable), `photo_path` (nullable), `role` (CORE/GUEST), `email` (nullable), `invite_token` (40-char, nullable, unique — auto-generated if email provided), `createdAt`, `updatedAt`

### `poker_nights`
`id`, `group_id` → poker_groups, `created_by` → users, `title`, `notes` (text, nullable), `scheduled_at` (datetime), `played_at` (datetime, nullable), `status` (SCHEDULED/COMPLETED/CANCELLED), `buy_in` (decimal 8,2, nullable), `createdAt`, `updatedAt`

### `game_attendees`
`id`, `poker_night_id` → poker_nights, `user_id` → users (nullable), `group_player_id` → group_players (nullable), `placement` (int, nullable — 1 = winner), `rsvp` (GOING/NOT_GOING/MAYBE, nullable), `createdAt`

### `game_images`
`id`, `poker_night_id` → poker_nights, `uploaded_by` → users, `file_path`, `caption` (nullable), `is_cover` (bool), `sort_order` (int), `createdAt`

### `night_comments`
`id`, `poker_night_id` → poker_nights, `user_id` → users, `message` (text), `createdAt`

---

## Authorization Model

| Action | Who can do it |
|---|---|
| View group / nights | Any group member |
| Create a poker night | Any group member |
| Edit a poker night | Any group member |
| Delete a poker night | Group owner or ADMIN |
| Record results (placements) | Group owner or ADMIN |
| Self-report own attendance | Any group member (cannot override owner-set placements) |
| RSVP | Any group member |
| Upload photos | Any group member |
| Manage roster (group_players) | Group owner or ADMIN |
| Edit group name/icon/settings | Group owner or ADMIN |
| Admin dashboard | ADMIN or SUPERADMIN role |
| Superadmin dashboard | SUPERADMIN role only |

Authorization is checked in controllers via inline guard methods (`authorizeOwner`, `authorizeGroupAccess`, `authorizeMember`). No Laravel Policies or Gates are used.

---

## Key Behaviors

### Invite Code System
- Each group gets a random 8-char invite code on creation.
- Owners can toggle `invite_enabled` on/off from the Edit Group page.
- When disabled: the join URL returns 403, and the invite code badge is hidden on group cards.
- The code itself is never regenerated (no rotation feature currently).

### Joining a Group
Joining via invite code or invite link does two things:
1. Creates a `group_members` row (access).
2. Creates a `group_players` row (roster) if one doesn't exist for that user in that group.

### Login Auto-Heal
On login, the controller:
1. Finds `group_players` with matching email and null `user_id` → links them and creates `group_members` rows.
2. Finds `group_players` already linked to the user's `user_id` → creates any missing `group_members` rows.

This handles the case where someone was added to a roster before they had an account.

### Attendance Flow
- **Owner**: uses the "Record Results" drag panel on the night show page. Submits ordered placements. This wipes and rewrites all attendee records for that night. RSVP records for non-placed players are preserved.
- **Member (self-report)**: uses the "My Attendance" card. Can add themselves (no placement) or remove themselves (only if owner hasn't set a placement for them). If the owner places them, the buttons are disabled.
- **Attended list logic**: includes (1) placed attendees in order, (2) GOING RSVPs, (3) attendees with no placement and no RSVP (self-reported). Status COMPLETED is auto-set when owner saves results with at least one placement.

### Group Cards (Dashboard + My Groups)
Cards show a cycling image slideshow: group avatar first, then cover images from recent nights. If no images exist, shows the ♠ placeholder. The bottom banner is 75% opacity with backdrop blur.

### File Storage
- Images stored at `storage/app/public/poker-nights/{night_id}/` and `storage/app/public/groups/{group_id}/`.
- Accessed via `asset('storage/' . $model->file_path)`.
- `php artisan storage:link` must be run once on each server to create the `public/storage` symlink.

---

## CSS Theme

Defined in `resources/css/app.css` using Tailwind v4 `@theme`:

```
--color-felt:         #1a3a1a   (deep green table felt)
--color-felt-dark:    #0f2210
--color-felt-light:   #244d24
--color-gold:         #c9a227
--color-gold-light:   #e8bf45
--color-card-bg:      #1c1c2e   (dark charcoal cards)
--color-surface:      #141414   (page background)
--color-surface-raised: #1e1e2e
--color-border:       #2d2d42
--color-muted:        #6b7280
```

Component classes defined in `@layer components`: `.btn`, `.btn-primary`, `.btn-gold`, `.btn-ghost`, `.btn-danger`, `.card`, `.input`, `.badge`, `.badge-gold`, `.badge-green`, `.badge-red`, `.badge-gray`, `.suit` (suit symbol styling).

---

## Alpine.js Components

Registered globally in `resources/js/app.js`:

- **`imageUpload()`** — drag-and-drop file upload with 10MB per-file validation, preview thumbnails, remove button.
- **`pokerCalendar(eventDates, baseUrl)`** — mini calendar with dots on event days, click navigates to night URL.
- **`imageCarousel(images)`** — auto-cycling image slideshow used on the group show page night cards. 3.5s interval.
- **`attendeeResults({attended, absent})`** — drag-to-reorder placement list for the owner's Record Results panel on the night show page.

---

## Migrations History (in order)

| File | What it does |
|---|---|
| `2026_06_06_000001_create_poker_schema` | Full initial schema: all core tables |
| `2026_06_06_000002_add_rsvp_and_comments` | Adds `rsvp` column to game_attendees; creates night_comments |
| `2026_06_06_000003_add_group_players` | Creates group_players; modifies game_attendees (nullable user_id + group_player_id column). **SQLite-specific syntax** — for MySQL, apply manually |
| `2026_06_07_022805_add_avatar_to_poker_groups` | Adds `avatar_path` nullable string to poker_groups |
| `2026_06_07_120000_add_invite_enabled_to_poker_groups` | Adds `invite_enabled` bool (default true) to poker_groups |

Migration `000003` uses SQLite PRAGMA and raw DDL statements. On MySQL (production), this migration must be applied manually — the artisan command will fail. See the MySQL-compatible SQL in the project history or re-derive from the schema table above.

---

## Things That Will Bite You

- **`000003` migration is SQLite-only.** Running `php artisan migrate` on MySQL will error on this migration. Apply it manually in phpMyAdmin.
- **Blade `{{ }}` escapes JSON.** Never pass JSON arrays through `{{ }}` into Alpine `x-data`. Use `{!! Js::from($array) !!}` or echo PHP integers directly.
- **`x-for` flashes on load.** Prefer server-rendered `@foreach` image tags with Alpine toggling `opacity` classes rather than `x-for` to avoid empty-content flash.
- **Tailwind arbitrary classes need a build.** Classes like `h-[19.2rem]` won't work unless `npm run build` is run and committed. Use inline `style=""` for one-off sizes.
- **No cascade deletes.** Deleting a poker night requires manually deleting images, attendees, and comments first (already handled in `PokerNightController::destroy`). If you add new related tables, update the destroy method.
- **`APP_URL` must include the subdirectory.** On production it must be `https://rapidinsightdesigns.com/pokernight` or Vite asset URLs will be wrong and CSS won't load.
- **`php artisan storage:link` must run once per server.** Without it, uploaded images 404.
