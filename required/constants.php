<?php

const CURRENT_PAGE_DEFAULT = 1;
const RESULTS_PER_PAGE = [25, 50, 100, 250, 500];
const RESULTS_PER_PAGE_DEFAULT = RESULTS_PER_PAGE[0];
const HASH_OPTIONS = [
    "algo" => PASSWORD_BCRYPT,
    "options" => [
        "cost" => 10
    ]
];