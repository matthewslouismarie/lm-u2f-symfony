# security-comparator

[![Build Status](https://travis-ci.com/matthewslouismarie/security-comparator.svg?token=ZVBwfXqKFH8rFj3NGf55&branch=master)](https://travis-ci.com/matthewslouismarie/security-comparator)

A website demonstrating the use of U2F authentication with Symfony 4.

The application is designed to be used by only one user at the same time. While
it will most certainly work with multiple users, the code is based on the assumption
that the database is not modified outside the during request.

Each method must be aware of the fact there is no guarantee the state (session +
database) has not been modified since the start of the execution process, or
even that the state won't be changed later (including between two calls to the
same method).

## Overview

_security-comparator_ mimics a banking website. It provides a registration page,
a login page, a page to transfer money, and a page to manage the user account
(updating the password and managing the U2F devices).

User studies can be run on this dummy website. Participants would be asked to
perform a certain set of tasks (logging in and transferring money for example).

From the administration panel, the investigator will be able to set the security
strategy. This includes the requirements for the password complexity, the number
of U2F devices the member can have. This way, it is possible to run user studies
to evaluate and compare the usability of different usability strategies.

It is also possible to import and export settings, making peer reviews much
easier. (It is possible to import the settings from a user study into the
website to run the user study one more time and confirm the findings.)

## Installation

### Docker

Installing _security-comparator_ using Docker is a straightforward process. Note
you will also need docker-compose.

Simply clone the repository. Then, from the repository root folder, run:
 1. docker-compose up --build -d
 2. docker exec -it *containerid* bash (*containerid* is the id of the container you created in step 1.)
 3. composer install
 4. npm install
 5. bin/console doctrine:database:create
 6. bin/console doctrine:schema:create
 7. bin/console doctrine:fixtures:load

### Without Docker

If you don't want to use Docker, you will need a web server with PHP > 7.2,
HTTPS, MariaDB / MySQL, Composer and NPM.

Put the content of the "symfony" folder where you put your web applications.
(It contains the source code of the website.) Then, copy .env.dist to .env, and
edit DATABASE_URL.

 1. composer install
 2. npm install
 3. bin/console doctrine:database:create
 4. bin/console doctrine:schema:create
 5. bin/console doctrine:fixtures:load

## Administration Panel

By default, the username of the administrator is "louis" and the password is
"hello". You can use these credentials to log in to the website. In the footer,
you will see an "Administration" link.

From there, you will be able to:

 - Configure U2F: Number of devices asked during registration, during login,
 etc.
 - Select the Security Strategy: Either U2F or Password
 - Configure Password Complexity: minimum length, uppercase letters, etc.
 - Configure the User Study Mode: you can turn the user study mode on or off.
 When it is on, it collects the time it takes for the current participant on
 page.
 - See user metrics per participant, and download the metrics to a CSV file.
 - Export settings to a JSON FILE
 - Import settings from a JSON file