# lm-u2f-symfony

[![Build Status](https://travis-ci.com/matthewslouismarie/lm-u2f-symfony.svg?token=ZVBwfXqKFH8rFj3NGf55&branch=master)](https://travis-ci.com/matthewslouismarie/lm-u2f-symfony)

A website demonstrating the use of U2F authentication with Symfony 4.

The application is designed to be used by only one user at the same time. While
it will most certainly work with multiple users, the code is based on the assumption
that the database is not modified outside the during request.

Each method must be aware of the fact there is no guarantee the state (session +
database) has not been modified since the start of the execution process, or
even that the state won't be changed later (including between two calls to the
same method).