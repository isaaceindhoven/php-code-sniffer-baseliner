# PHP_CodeSniffer Baseliner

Quickly integrate PHP_CodeSniffer in your project. Brings the number of errors to zero by adding an exclusions to each
rule for each file that does not pass the rule. This enables you to require that all new files adhere to the ruleset and
forbids new rule violations in existing files.

## Installation

First, add the path of this repo to the composer file in your project:

```shell script
composer config repositories.isaac-php-code-sniffer-baseliner vcs git@gitlab.isaac.local:php-module/isaac-php-code-sniffer-baseliner.git
```

Now require the package:

```shell script
composer require --dev isaac/php-code-sniffer
```

## Usage

In order to add file exclusions for each rule that fails to your PHP_CodeSniffer configuration, run the following
command. (This assumes that you have a PHP_CodeSniffer config file in your project root.)

```shell script
vendor/bin/phpcs-baseliner create-baseline
```

To remove the baseline:

```shell script
vendor/bin/phpcs-baseliner clean-up-baseline
```

After you fix some of the failing rules, run the following command to remove exclusions for violations that do not occur
anymore.  
