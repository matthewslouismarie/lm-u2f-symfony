# security-comparator

[![Build Status](https://travis-ci.org/matthewslouismarie/security-comparator.svg?branch=master)](https://travis-ci.org/matthewslouismarie/security-comparator)

A website demonstrating the use of U2F authentication with Symfony 4.

This is a web application aimed at being used by Human-Computer Interaction
researchers, both to conduct user studies and to peer review existing ones.

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

The investigator can turn the "User Study Mode" on, and the web application will
start recording how much time does the participant spend on each page (excluding
the administration pages).

It is also possible to import and export settings, making peer reviews much
easier. (It is possible to import the settings from a user study into the
website to run the user study one more time and confirm the findings.)

## Installation

### Docker

Installing _security-comparator_ using Docker is a straightforward process. Note
you will also need docker-compose.

Simply clone the repository. Then, from the repository root folder, run: ./bootstrap.

### Without Docker

If you don't want to use Docker, you will need a web server with PHP > 7.2,
HTTPS, MariaDB / MySQL, Composer and NPM.

Put the content of the "symfony" folder where you put your web applications.
(It contains the source code of the website.) Then, copy .env.dist to .env, and
edit DATABASE_URL.

 1. composer install
 2. npm install
 3. grunt copy, grunt sass
 4. bin/console doctrine:database:create
 5. bin/console doctrine:schema:create
 6. bin/console doctrine:fixtures:load

## Administration Panel

By default, the username of the administrator is "louis" and the password is
"hello". You can use these credentials to log in to the website. In the footer,
you will see an "Administration" link.

From there, you will be able to:

 - See the Security Score of the current Security Strategy
 - Configure U2F: Number of devices asked during registration, during login,
 etc.
 - Select the Security Strategy: Either U2F or Password
 - Configure Password Complexity: minimum length, uppercase letters, etc.
 - Configure the User Study Mode: you can turn the user study mode on or off.
 When it is on, it collects the time it takes for the current participant on
 page.
 - See user metrics per participant, and download the metrics to a CSV file. You
 can also see the number of errors.
 - Export settings to a JSON FILE
 - Import settings from a JSON file