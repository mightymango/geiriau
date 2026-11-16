# Geiriau: 5 Welsh words a day

A NativePHP Mobile app (Laravel with native iOS and Android screens) for learning Welsh vocabulary at **Sylfaen** level, with **Mynediad** words kept fresh alongside. It introduces 5 new words a day and uses spaced repetition so you see each word again just before you'd forget it.

## How it teaches

| Idea | What the app does |
|---|---|
| **Meet the word before the test** | Each day's 5 words get a short "meet the words" screen (word, meaning, gender, note) before any quizzing. |
| **Retrieval practice** | Cards ask you to *recall*, not recognise from a list. Tap or type before you see the answer. |
| **Spacing** | SM-2 scheduler (the classic Anki algorithm). In-session steps at 1 min and 10 min, then 1 day, ~3 days, ~8 days, ~3 weeks… The gap grows each time you remember a word and shrinks when you forget it. |
| **Both directions, staggered** | *Cymraeg → English* first. *English → Cymraeg* (the harder production card) unlocks the **next day** once you can recognise the word, so it's a genuine effort to recall. Typing is optional, and it tells you when only the accents (â, ŵ, ŷ…) are off. |
| **Interleaving** | Due reviews come in a random order, mixing themes and directions. |
| **Keep the workload sustainable** | If more than 80 reviews are waiting, no new words are added that day. 5 a day settles at roughly 20–30 cards a day. |
| **A daily cue** | One reminder a day at a time you choose (default 19:00). It says what's waiting, is skipped once you've finished for the day, and stops after a week if you stop opening the app. |
| **Gender shown every time** | Nouns show **eb** (feminine) / **eg** (masculine), the same labels as the coursebooks, because gender drives mutations. |

### Mynediad handling (chosen on first launch, changeable in Settings)
- **I know them, keep them fresh** *(default)*: Mynediad words go in as reviews, about 10 words a day, both directions. Any you've forgotten drop back into relearning.
- **Teach them again**: Mynediad words fill the daily 5 first, then Sylfaen.
- **Leave them out**

## Word bank

`database/data/words.txt` has ~207 Mynediad and ~261 Sylfaen words and phrases, using **South Wales forms** (nawr, moyn, llaeth, 'da fi, mam-gu…) with North equivalents in the notes. It's arranged by theme, not by coursebook unit, and isn't the official Dysgu Cymraeg list. You can make it match your course in two ways:

- **In the app:** Geiriau → *+ Add word* (for words from class). Your own words are introduced first.
- **In the file:** edit `words.txt` (`Welsh | English | pos | m/f | note`), then add a migration that calls `WordBank::sync(database_path('data/words.txt'))`. NativePHP runs migrations when the app starts, and words you've already studied keep their progress.

At 5 a day the Sylfaen bank lasts about 52 days.

## Setup

Requires PHP 8.3+ and Composer, plus Xcode for iOS or Android Studio for Android.

```bash
git clone https://github.com/mightymango/geiriau.git
cd geiriau
composer run setup     # installs packages, creates .env + SQLite db, migrates (loads the word bank)
composer run native:plugins    # one-time: registers the local-notifications plugin
php artisan native:install
php artisan native:run         # pick a simulator or connected device
```

The app ID and version are in `.env` (`NATIVEPHP_APP_ID=com.mightymango.geiriau`).

Keep `NATIVEPHP_APP_VERSION=DEBUG` while developing, so the phone unpacks fresh code on every launch. Use `php artisan native:release` to set a real version for TestFlight or the App Store.

Tests: `composer test`. `tests/Unit/SchedulerTest.php` covers the scheduling maths and word bank; `tests/Feature/StudyPlannerTest.php` covers the daily 5, unlocking production cards and Mynediad placement. Each screen has its own test in `tests/Feature`, using NativePHP's `Native::test()`.

## App icon

`public/icon.png` (1024×1024, no transparency) is what NativePHP builds the iOS and Android icons from. It's option A, a flashcard with ŵ and the daily five. The SVG sources for all three options are in `resources/icon/`. To switch, render another option with `rsvg-convert -w 1024 resources/icon/b-ll.svg | convert - -alpha off public/icon.png`, then run `php artisan native:run` again.

## Splash screens

`public/splash.png` (light) and `public/splash-dark.png` (dark) are 1290×2796 and match the icon: the card stack, **Geiriau** and *Pum gair y dydd* ("five words a day"). Everything sits in the middle, so cropping for other screen shapes only trims background. The SVG sources are in `resources/icon/`.

## Daily reminder

Uses [smronju/nativephp-local-notifications](https://packagist.org/packages/smronju/nativephp-local-notifications) (free, MIT; iOS 15+, Android 8+). Notifications are scheduled on the phone, so no server or Firebase is needed.

- The next 7 days are scheduled as one-off notifications and rebuilt whenever you open the app or finish a session. A repeating alarm couldn't skip days you've already done.
- The app asks for notification permission once, when you finish the welcome screen.
- Off the phone there's nothing to notify, so set `REMINDER_DRIVER=log` and check `storage/logs/laravel.log` to see what would be scheduled.
- To use NativePHP's paid official plugin instead (tap actions, badges), write a gateway implementing `App\Services\Reminders\NotificationGateway` and bind it in `StudyServiceProvider`.

## Structure

```
app/Srs/                 Pure-PHP scheduler (no Laravel), unit-tested
  Scheduler.php          SM-2: learning steps → review intervals × ease, lapses
  StudyDay.php           Days roll over at 03:00 London time
app/Services/
  StudyPlanner.php       Daily batch of new words, queue order, Mynediad placement, stats, streak
  WordBank.php           Parses/syncs words.txt
  Reminders/             DailyReminders + notification gateways (plugin / log)
app/NativeComponents/    Home · Learn (meet today's words) · Study · Words · Settings
app/NativeLayouts/       AppLayout: the tab bar shared by the four tab screens
resources/views/native/  The screens' native views (SwiftUI on iOS, Compose on Android)
routes/mobile.php        Native routes
config/native-ui.php     Theme colours, light and dark
config/cymraeg.php       new_per_day, backlog_limit, steps, rollover hour…
```

## Ideas for later
- Recorded audio per word. Welsh text-to-speech on phones is patchy, so recordings from a native speaker (e.g. a tutor) would be best.
- Example sentences and cloze cards ("Dw i'n ___ coffi") once the core vocab is in.
- Mutation drills for feminine nouns after *y/'r* (cath → y gath).
