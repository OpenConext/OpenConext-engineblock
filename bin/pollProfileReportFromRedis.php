<?php
require_once 'vendor/autoload.php';

$redisClient = new Redis();
$redisClient->connect('127.0.0.1');

$reporter = new \Lvl\Profiler\Reporter();

while(true) {
    $profileKeys = $redisClient->keys('profile*');
    foreach ($profileKeys as $profileKey) {
        $profile = unserialize($redisClient->get($profileKey));
        $redisClient->del($profileKey);

        echo $reporter->getReport($profile['records'], $profile['metadata']);
    }
}