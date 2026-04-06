<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Opportunistic reminder scan interval
    |--------------------------------------------------------------------------
    |
    | After an authenticated request, date-based reminders may run in the
    | background (middleware terminate). This limits how often a full scan
    | runs, so every page view does not hit the database.
    |
    */

    'opportunistic_interval_minutes' => max(5, (int) env('NOTIFICATIONS_OPPORTUNISTIC_INTERVAL_MINUTES', 15)),

    /*
    |--------------------------------------------------------------------------
    | Mirror notifications to super-admins
    |--------------------------------------------------------------------------
    |
    | When enabled, each new notification is also stored for every active user
    | with the "super-admin" role (unless they are already the recipient).
    |
    */

    'mirror_to_super_admins' => (bool) env('NOTIFICATIONS_MIRROR_TO_SUPER_ADMINS', true),

];
