# Geiriau: 5 Welsh words a day

A NativePHP Mobile app (Laravel + Livewire) for learning Welsh vocabulary at **Sylfaen** level, with **Mynediad** words kept fresh alongside. It introduces 5 new words a day and uses spaced repetition so you see each word again just before you'd forget it.

## How it teaches

| Idea | What the app does |
|---|---|
| **Meet the word before the test** | Each day's 5 words get a short "meet the words" screen (word, meaning, gender, note) before any quizzing. |
| **Retrieval practice** | Cards ask you to *recall*, not recognise from a list. Tap or type before you see the answer. |
| **Spacing** | SM-2 scheduler (the classic Anki algorithm). In-session steps at 1 min and 10 min, then 1 day, ~3 days, ~8 days, ~3 weeks… The gap grows each time you remember a word and shrinks when you forget it. |
| **Both directions, staggered** | *Cymraeg → English* first. *English → Cymraeg* (the harder production card) unlocks the **next day** once you can recognise the word, so it's a genuine effort to recall. Typing is optional, and it tells you when only the accents (â, ŵ, ŷ…) are off. |
| **Interleaving** | Due reviews come in a random order, mixing themes and directions. |
| **Keep the workload sustainable** | If more than 80 reviews are waiting, no new words are added that day. 5 a day settles at roughly 20–30 cards a day. |
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

These are app files to add to a fresh Laravel project:

```bash
laravel new geiriau          # no starter kit, SQLite
cd geiriau
# copy this repo's files over the top (app/, bootstrap/providers.php, config/, database/, resources/views/, routes/, tests/)
composer require livewire/livewire nativephp/mobile
php artisan migrate:fresh
npm install && npm run build
php artisan serve            # try it in a browser first (use a phone-width window)
```

Then on to the phone:

```bash
# .env
NATIVEPHP_APP_ID=com.mightymango.geiriau
NATIVEPHP_APP_VERSION=1.0.0

php artisan native:install
php artisan native:run       # pick iOS simulator / Android device
```

Run the unit tests with `php artisan test --filter=SchedulerTest`.

## Structure

```
app/Srs/                 Pure-PHP scheduler (no Laravel), unit-tested
  Scheduler.php          SM-2: learning steps → review intervals × ease, lapses
  StudyDay.php           Days roll over at 03:00 London time
app/Services/
  StudyPlanner.php       Daily batch of new words, queue order, Mynediad placement, stats, streak
  WordBank.php           Parses/syncs words.txt
app/Livewire/            Home · Learn (meet today's words) · Study · Words · Settings
config/cymraeg.php       new_per_day, backlog_limit, steps, rollover hour…
```

## Ideas for later
- Daily reminder notification (NativePHP local notifications plugin).
- Recorded audio per word. Welsh text-to-speech on phones is patchy, so recordings from a native speaker (e.g. a tutor) would be best.
- Example sentences and cloze cards ("Dw i'n ___ coffi") once the core vocab is in.
- Mutation drills for feminine nouns after *y/'r* (cath → y gath).
