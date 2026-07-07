<?php

// Canonical list of togglable Sub-Admin features. Each key must match the
// `feature:<key>` middleware applied to the corresponding route group in
// routes/web.php. Seeded into the `permissions` table by PermissionSeeder.
return [
    ['key' => 'lotteries',           'label' => 'Lottery Management',     'description' => 'Create and manage lottery types'],
    ['key' => 'stock',               'label' => 'Stock',                  'description' => 'Ticket stock intake'],
    ['key' => 'bundle-counter',      'label' => 'Bundle Counter',         'description' => 'Bundle counting tool'],
    ['key' => 'assistants',          'label' => 'Sales Assistants',       'description' => 'Manage sales assistants and their ledgers'],
    ['key' => 'daily-sales',         'label' => 'Daily Sales',            'description' => 'Record and analyse daily sales'],
    ['key' => 'bulk-deposits',       'label' => 'Bulk Deposits',          'description' => 'Bulk deposit entry and distribution'],
    ['key' => 'ticket-distribution', 'label' => 'Ticket Distribution',    'description' => 'Distribute tickets to assistants and sub-sellers'],
    ['key' => 'board-settlement',    'label' => 'Board Settlement',       'description' => 'Board settlement entries'],
    ['key' => 'board-transactions',  'label' => 'Board Ledger',           'description' => 'Board transaction ledger'],
    ['key' => 'expenses',            'label' => 'Expenses',               'description' => 'Record and export expenses'],
    ['key' => 'cheques',             'label' => 'Cheques',                'description' => 'Cheque tracking and clearing'],
    ['key' => 'winnings',            'label' => 'Winnings',               'description' => 'Winning ticket calculator and records'],
];
