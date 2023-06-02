<?php
/**
 * Caching functions
 */

function cache_get($key) {
    global $cache_options;
    switch($cache_options['type']) {
        default:
        case 'disabled':
            return null;
        case 'memcached':
            return cache_memcached_get($key);
        case 'redis':
            return cache_redis_get($key);
        case 'db':
            return cache_db_get($key);
    }
}

function cache_set($key, $value) {
    global $cache_options;
    switch($cache_options['type']) {
        default:
        case 'disabled':
            return null;
        case 'memcached':
            return cache_memcached_set($key, $value);
        case 'redis':
            return cache_redis_set($key, $value);
        case 'db':
            return cache_db_set($key, $value);
    }
}

function cache_db_get($key) {
    $result = db_query_to_variable("
        SELECT `content` FROM `cache` WHERE `hash` = '$key' AND NOW() < `valid_until`
    ");
    return $result;
}

function cache_db_set($key, $value) {
    global $cache_options;
    $value_escaped = db_escape($value);
    db_query("
        INSERT INTO `cache` (`hash`, `content`, `valid_until`)
        VALUES ('$key', '$value_escaped',
            DATE_ADD(NOW(), INTERVAL " . $cache_options['interval'] . " SECOND))
        ON DUPLICATE KEY UPDATE `content` = VALUES(`content`),
            `valid_until` = VALUES(`valid_until`)
    ");
}

function cache_memcached_get($key) {
    global $cache_options;
    $memcache_resource = new Memcached();
    $memcache_resource->addServer($cache_options['server'], $cache_options['port']);
    $result = $memcache_resource->get($key);
    return $result;
}

function cache_memcached_set($key, $value) {
    global $cache_options;
    $memcache_resource = new Memcached();
    $memcache_resource->addServer($cache_options['server'], $cache_options['port']);
    $memcache_resource->set($key, $value, $cache_options['interval']);
}

function cache_redis_get($key) {
    global $cache_options;
    $redis = new Redis();
    $redis->connect($cache_options['server'], $cache_options['port']);
    if($cache_options['password']) {
        $redis->auth($cache_options['password']);
    }
    $result = $redis->get($key);
    return $result;
}

function cache_redis_set($key, $value) {
    global $cache_options;
    $redis = new Redis();
    $redis->connect($cache_options['server'], $cache_options['port']);
    if($cache_options['password']) {
        $redis->auth($cache_options['password']);
    }
    $redis->set($key, $value, $cache_options['interval']);
}
