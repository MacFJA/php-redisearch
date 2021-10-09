# How to contribute

## Reporting and improving

### Did you find a bug?

* **Ensure the bug was not already reported** by searching on GitHub under [Issues](https://github.com/MacFJA/php-redisearch/issues).

* If you're unable to find an open issue addressing the problem, [open a new one](https://github.com/MacFJA/php-redisearch/issues/new). Be sure to include a **title and clear description**, as much relevant information as possible

### Did you write a patch that fixes a bug?

* Open a new GitHub pull request with the patch.

* Ensure the PR description clearly describes the problem and solution. Include the relevant issue number if applicable.

### Do you have an idea to improve the library?

* **Ensure the suggestion was not already ask** by searching on GitHub under [Issues](https://github.com/MacFJA/php-redisearch/issues).

* If you're unable to find an open issue about your feature, [open a new one](https://github.com/MacFJA/php-redisearch/issues/new). Be sure to include a **title and clear description**, as much relevant information as possible

### Do you want to contribute to the library documentation?

* **Ensure the documentation improvement was not already submitted** by searching on GitHub under [Issues](https://github.com/MacFJA/php-redisearch/issues).

* If you're unable to find an open issue addressing this, clone the wiki git on your computer

* [Open a new issue](https://github.com/MacFJA/php-redisearch/issues/new). Be sure to include a **title and clear description**, as much relevant information as possible and the patch for the documentation

## Coding conventions

The application use PSR-12 code conventions, strong typing.

The source code must be, at least, compatible with **PHP 7.2**.

Check your code by running the commands:
```sh
make analyze
make test
```
The command will output any information worth knowing. No error should be left.

If Xdebug is installed, you can also run the commands:
```sh
make coverage
```

----

Thanks!