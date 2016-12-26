Here's a little repo to allow you to fork all U.S. government repos, just in case the originals disappear in the next few years.

If you have a git username and oauth key, you can fork the repos yourself. Running this script will create ~1670 new repositories in your account, so you may want to create a special account just for this. You will need a [new token](https://help.github.com/articles/creating-an-access-token-for-command-line-use/) for the new account. Be sure to copy the new key when you create it, as Github will not show it to you again.

To fork all of the repos:

```
composer install
export GH_USERNAME='Your Username here'
export GH_OAUTH_KEY='Your OAUTH Key Here'
php run.php
```

To update your forked repos:
```
export GH_USERNAME='Your Username here'
export GH_OAUTH_KEY='Your OAUTH Key Here'
php run.php update
```

(Obviously you can use also some other mechanism to set the relevant environment variables.)

Note that this looks only for master branches upstream. If one doesn't exist, then the exception is caught and logged to STDOUT.

TODOs:
* Tests
* Error Logging.
* Generally make it less dumb.
* DRY up the code.
* Update the docblocks.
* Better organization of classes.

Requirements:
* PHP 5.4 or greater (so you can use traits)
* [Composer](https://getcomposer.org/)
