<?php

return [
    // New words introduced each study day (changeable in the app's settings).
    'new_per_day' => 5,

    // A study day starts at this local hour, so a late-night session still counts as "today".
    'rollover_hour' => 3,
    'timezone' => 'Europe/London',

    // If more reviews than this are waiting, no new words are added that day.
    'backlog_limit' => 80,

    // When Mynediad words are loaded as "already known", this many come up for a check each day.
    'mynediad_per_day' => 10,

    // When an unseen learning card is due within this many minutes, show it early instead of stopping.
    'learn_ahead_minutes' => 20,

    // Daily reminder (changeable in Settings). Driver: "local" uses the on-device notifications
    // plugin; "log" just writes to the log (handy in a browser). Defaults to "local" when installed.
    'reminder_time' => '19:00',
    'reminder_driver' => env('REMINDER_DRIVER'),

    'learning_steps' => [1, 10],
    'relearning_steps' => [10],
];
