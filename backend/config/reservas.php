<?php

return [
    'hold_minutes' => (int) env('RESERVATION_HOLD_MINUTES', 15),
    'pii_retention_days' => (int) env('RESERVATION_PII_RETENTION_DAYS', 365),
];