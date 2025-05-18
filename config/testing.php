<?php

return [
    'log_disk' => env('TEST_LOG_DISK', 'local'),
    'container_timeout' => env('TEST_CONTAINER_TIMEOUT', 1200),
    'check_interval' => env('TEST_CHECK_INTERVAL', 5),
    'docker_images' => [
        'selenium_python' => env('TEST_DOCKER_IMAGE_SELENIUM_PYTHON', 'arxitest/selenium-python:latest'),
        'cypress' => env('TEST_DOCKER_IMAGE_CYPRESS', 'arxitest/cypress:latest'),
        'default' => env('TEST_DOCKER_IMAGE_DEFAULT', 'alpine:latest'),
    ],
];
