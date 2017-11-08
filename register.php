<?php

spl_autoload_register(function ($class_name) {
    $filename = basename(str_replace('\\', '/', $class_name)).'.php';
    include 'src/'.$filename;
});