<?php
return [
    // Extra charge applied per day AFTER 30 days from rent_start_date when isReceive = 1
    'extra_charge_per_day' => env('EXTRA_CHARGE_PER_DAY', 0), // e.g. 200
];
