<?php

$options = [
    'salt' => 'saltsaltsaltsaltsaltsaltsaltsaltsaltsaltsaltsaltsaltsaltsalt',
];
echo "\n".password_hash("rasmuslerdorf", PASSWORD_BCRYPT, $options)."\n";