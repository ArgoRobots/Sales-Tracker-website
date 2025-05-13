<?php

/**
 * Get the two letter country code corresponding to a hostname or an IP address
 * @link https://www.php.net/manual/en/function.geoip-country-code-by-name.php
 * @param string $hostname The hostname or IP address whose location is to be looked-up.
 * @return string|false The two letter country code on success, or false on failure.
 */
function geoip_country_code_by_name(string $hostname) {}

/**
 * Get the full country name corresponding to a hostname or an IP address
 * @link https://www.php.net/manual/en/function.geoip-country-name-by-name.php
 * @param string $hostname The hostname or IP address whose location is to be looked-up.
 * @return string|false The country name on success, or false on failure.
 */
function geoip_country_name_by_name(string $hostname) {}
