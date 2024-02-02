<?php

const FOLDER_NAME = 'FolderName';
const USERNAME = "your@email.com";
const PASSWORD = "limited_password";
const GPRO_URL = 'https://www.gpro.net/gb/';
const HASH = '68c6e8ddaddcfa57a59a6b08c43fe477';
const GPRO_UA = 'GPRO Home Server';
const DB_FOLDER_NAME = 'database';
const GPRO_TIMEZONE = 'Europe/Berlin';
const GPRO_HOME_SERVER_FOLDER = __DIR__;

define('ACCOUNTS', [
    FOLDER_NAME => [
        'username' => USERNAME,
        'password' => PASSWORD,
    ],
]);
