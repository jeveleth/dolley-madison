Here's a little repo to allow you to fork all U.S. government repos, just in case the originals disappear in the next few years.
If you have a git username and ouath key, you can fork the repos yourself.

To fork all of the repos:

```
composer install
php run.php
```

To update your forked repos:
```
php run.php update
```

Note that this looks only for master branches upstream. If one doesn't exist, then the exception is caught and logged to STDOUT.

TODOs:
* Tests
* Error Logging.
* Generally make it less dumb.
* DRY up the code.
* Update the docblocks.
* Better organization of classes.
