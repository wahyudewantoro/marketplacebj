<?php

$imageVersion = [
    'buildTimestamp' => date('c'),
];

$file = fopen('image-version.json', 'w');
fwrite($file, json_encode($imageVersion));
fclose($file);

echo 'image-version.json updated';
