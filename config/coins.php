<?php

return [
    'linkvertise' => [
        // The publisher's numeric Linkvertise user ID (found in the Linkvertise
        // dashboard). This is a public identifier, not a secret API key — Linkvertise
        // does not offer a public link-creation API, so links are constructed
        // directly using their documented dynamic-link URL format.
        'user_id' => env('COINS_LINKVERTISE_USER_ID'),
        'reward' => (int) env('COINS_LINKVERTISE_REWARD', 50),
        // Max links a single user may generate per day, regardless of whether
        // they're ever completed — caps abuse of the generation endpoint itself.
        'daily_limit' => (int) env('COINS_LINKVERTISE_DAILY_LIMIT', 15),
    ],

    'afk' => [
        'reward_per_minute' => (int) env('COINS_AFK_REWARD_PER_MINUTE', 5),
        // Raw HTML/JS the admin pastes in for their ad network of choice, shown
        // on the AFK page. Left empty, the page just shows a placeholder.
        'ad_slot_html' => env('COINS_AFK_AD_SLOT_HTML', ''),
    ],

    'shop' => [
        'memory_unit_mib' => (int) env('COINS_SHOP_MEMORY_UNIT_MIB', 128),
        'memory_price' => (int) env('COINS_SHOP_MEMORY_PRICE', 20),
        'disk_unit_mib' => (int) env('COINS_SHOP_DISK_UNIT_MIB', 512),
        'disk_price' => (int) env('COINS_SHOP_DISK_PRICE', 15),
        'cpu_unit_percent' => (int) env('COINS_SHOP_CPU_UNIT_PERCENT', 10),
        'cpu_price' => (int) env('COINS_SHOP_CPU_PRICE', 25),
        'backup_price' => (int) env('COINS_SHOP_BACKUP_PRICE', 40),
        'slot_price' => (int) env('COINS_SHOP_SLOT_PRICE', 100),
    ],

    'server' => [
        // A single fixed-spec tier for servers bought outright with coins,
        // billed monthly. Independent of the free self-service resource pool.
        'monthly_price' => (int) env('COINS_SERVER_MONTHLY_PRICE', 300),
        'memory' => (int) env('COINS_SERVER_MEMORY', 2048),
        'disk' => (int) env('COINS_SERVER_DISK', 5120),
        'cpu' => (int) env('COINS_SERVER_CPU', 100),
        'backups' => (int) env('COINS_SERVER_BACKUPS', 1),
        // How many days a coin-funded server may sit suspended for non-payment
        // before it's deleted outright to free up the node's resources.
        'suspension_grace_days' => (int) env('COINS_SERVER_SUSPENSION_GRACE_DAYS', 30),
        // Coin-funded servers are bought outright and billed separately from
        // the free self-service pool, so by default they don't eat into a
        // user's memory/disk/cpu/backup/slot limits there. Flip this on to
        // have them count towards the pool anyway.
        'count_towards_pool' => (bool) env('COINS_SERVER_COUNT_TOWARDS_POOL', false),
        // What fraction of a server's *remaining, unused* paid time is refunded
        // in coins when a user cancels (stops + deletes) a coin-funded server
        // before its next renewal is due.
        'cancellation_refund_percent' => (int) env('COINS_SERVER_CANCELLATION_REFUND_PERCENT', 50),
    ],

    'daily' => [
        // Coins granted for the daily login reward; 0 disables the feature.
        'reward' => (int) env('COINS_DAILY_REWARD', 25),
        // Extra coins added for every consecutive day already claimed (streak).
        'streak_bonus' => (int) env('COINS_DAILY_STREAK_BONUS', 5),
        // The streak stops growing the bonus after this many consecutive days.
        'streak_max' => (int) env('COINS_DAILY_STREAK_MAX', 7),
    ],

    'referral' => [
        // Coins the referrer gets once the referred user first earns coins; 0 disables referrals.
        'referrer_reward' => (int) env('COINS_REFERRAL_REFERRER_REWARD', 100),
        // Welcome bonus for a new user who signed up through a referral link.
        'referred_bonus' => (int) env('COINS_REFERRAL_REFERRED_BONUS', 50),
    ],
];
