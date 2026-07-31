# HPV·HSV Compass — v2.0

A database-driven sexual health education platform: structured HPV/HSV lessons,
progress tracking, a rule-based educational AI assistant, a screening booking
wizard, user profiles, and an admin CMS. Built in plain **PHP 8 + MySQL** (no
framework) so it runs on any standard LAMP stack, XAMPP, or MAMP.

This was tested end-to-end during development: every page and API endpoint was
exercised against a real MySQL database with the PHP built-in server — full
register → learn → quiz → save article → chat → book → admin-edit-content flow,
with zero PHP warnings/errors in the log.

## 1. Requirements

- PHP 8.1+ with the `pdo_mysql` and `mbstring` extensions (both bundled by
  default in XAMPP/MAMP; on bare Ubuntu: `sudo apt install php php-mysql php-mbstring`)
- MySQL 5.7+ / MariaDB 10.3+
- Any web server (Apache/Nginx) — or just PHP's built-in server for local dev

## 2. Setup

### Step 1 — Create the database
```bash
mysql -u root -p < database/schema.sql
```
This creates the `hpv_hsv_compass` database, all tables, a default **admin
account**, and seeds the HPV/HSV lesson content, quiz questions, and FAQs
(reused verbatim from the original site copy).

### Step 2 — Configure the database connection
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'hpv_hsv_compass');
define('DB_USER', 'root');
define('DB_PASS', '');   // set your MySQL password here
```

### Step 3 — Point your web server at the project root
- **XAMPP/MAMP**: drop this folder into `htdocs`, then visit
  `http://localhost/hpv-hsv-compass/index.php`
- **PHP built-in server (quick local test, no Apache needed)**:
  ```bash
  cd hpv-hsv-compass
  php -S localhost:8000
  ```
  then open `http://localhost:8000/index.php`

### Step 4 — Log in
- **Admin panel**: `admin@hpvhsvcompass.local` / `Admin@123` — **change this
  password immediately** via Settings after first login.
- **Regular users**: register a free account from the homepage.

## 3. Project structure

```
config/database.php        PDO connection settings
includes/                  Shared PHP: auth/session helpers, layout, AI logic
assets/css/style.css       All styling (original teal branding preserved)
assets/js/main.js          All front-end interactivity (progressive enhancement)
database/schema.sql        Full schema + seed data (content, quiz, FAQs, admin)
api/                       AJAX endpoints (progress, chat, booking, settings, saves)
admin/                     Admin panel (users, bookings, content CMS, FAQs, messages)

index.php, about.php, faq.php, contact.php,
login.php, register.php, hpv.php, hsv.php    Public pages
dashboard.php, profile.php, settings.php,
assistant.php, booking.php                    Authenticated user pages
```

## 4. How each requirement is implemented

| Requirement | Where |
|---|---|
| User authentication | `login.php`, `register.php`, `includes/auth.php` — bcrypt password hashing (`password_hash`/`password_verify`), PHP sessions, CSRF tokens on every form |
| Email verification | `includes/mailer.php`, `verify.php`, `check-email.php`, `resend_verification.php` — token-based verification required before login; see below |
| Sessions | Native `$_SESSION`, regenerated on login (`session_regenerate_id`) |
| User management | `admin/users.php` — promote/demote/delete |
| Booking management | `booking.php` (4-step wizard), `admin/bookings.php` (status updates) |
| Learning progress | `learning_progress` table, one row per user per topic, updated live via `api/progress.php` as sections/quizzes are completed |
| Database design | `database/schema.sql` — 9 normalized tables with foreign keys (see below) |
| AI integration | `includes/ai_responses.php` — rule-based keyword matching over HPV/HSV topics, with an explicit "I can't help with that" fallback for off-topic questions, and a medical disclaimer shown on the Assistant and About pages |
| Admin CMS | `admin/content.php` + `admin/content_edit.php` — edit lesson body text stored in the `content` table; changes appear on `hpv.php`/`hsv.php` immediately |
| Images & videos in lessons | `admin/content_edit.php` media library — upload directly from the Overview/Symptoms/Causes/Prevention/Vaccination editor, then click "Insert" to drop it into that section. Stored in `content_media` + `/uploads/content/` |

### Adding images/videos to a lesson section
1. Admin panel → **Content (CMS)** → click **Edit** on the section (e.g. HPV → Prevention)
2. Under "Add an image or video," choose a file and an optional caption, then **Upload**
3. The upload appears in the media gallery below — click **Insert** to append it to that section's body (shown immediately on the live `hpv.php`/`hsv.php` page)
4. To reposition it within the text, cut/paste the `<figure class="lesson-media">...</figure>` block in the Body textarea above
5. **Delete** removes the file from disk and the gallery, but won't retroactively strip it from body text you've already inserted it into — remove that `<figure>` block manually if needed

Limits: images up to 5MB (JPG/PNG/GIF/WEBP), videos up to 75MB (MP4/WEBM/MOV) — adjustable in `includes/media.php`. The real file content is verified via `finfo`/MIME sniffing, not just the file extension, so a disguised `.php` file renamed to `.jpg` is rejected. If large video uploads fail, raise `upload_max_filesize` and `post_max_size` in `php.ini`. If you're upgrading an existing install (not a fresh `schema.sql` import), run `database/migration_add_media.sql` once to add the `content_media` table.


### Database tables
`users`, `content`, `quiz_questions`, `faqs`, `learning_progress`,
`saved_articles`, `bookings`, `chat_logs`, `contact_messages`.

## 5. Security notes

- All queries use PDO prepared statements (no string-concatenated SQL).
- All forms include a CSRF token, verified server-side.
- Passwords are hashed with PHP's `password_hash()` (bcrypt), never stored in plain text.
- Output is escaped with `htmlspecialchars()` via the `e()` helper everywhere
  user-supplied data is rendered, except lesson `body` HTML which is
  intentionally admin-authored via the CMS.
- Admin routes are protected by `require_admin()`, which checks both session
  authentication and role.

## 7. Notes on the AI Assistant

Per project scope, this is a **rule-based** assistant (keyword matching against
a curated set of HPV/HSV answers) — no external API calls, no cost, no data
leaves your server. It explicitly declines off-topic questions and every page
that surfaces it carries a medical disclaimer. If you later want to swap in a
real LLM (e.g. the Claude API), the only file that needs to change is
`includes/ai_responses.php` — the rest of the app (chat UI, logging, session
gating) stays the same.

