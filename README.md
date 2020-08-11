# PHP_CodeSniffer Baseliner

Quickly integrate PHP_CodeSniffer in your project. Brings the number of errors to zero by adding an exclusions to each
rule for each file that does not pass the rule. This enables you to require that all new files adhere to the ruleset and
forbids new rule violations in existing files.

## Installation

First, configure the ISAAC composer package repository:

```sh
fin composer config repositories.isaac composer https://composer-packages.hq.isaac.nl/repository
```

Now require the package:

```sh
composer require --dev isaac/php-code-sniffer-baseliner
```

## Usage

In order to add file exclusions for each rule that fails to your PHP_CodeSniffer configuration, run the following
command. (This assumes that you have a PHP_CodeSniffer config file in your project root.)

```sh
vendor/bin/phpcs-baseliner create-baseline
```

To remove the baseline:

```sh
vendor/bin/phpcs-baseliner remove-baseline
```

After you fix some of the failing rules, run the following command to remove exclusions for violations that do not occur
anymore.  

```sh
vendor/bin/phpcs-baseliner clean-up-baseline
```
