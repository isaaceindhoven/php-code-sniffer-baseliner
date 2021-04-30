# PHP_CodeSniffer Baseliner

This tool enables you to integrate [PHP_CodeSniffer][php-code-sniffer] into an existing
project by automatically adding `phpcs:ignore` and `phpcs:disable`/`phpcs:enable` instructions throughout the codebase
as a baseline. This allows you to make PHP_CodeSniffer pass without changing any source code, making it
possible to use PHP_CodeSniffer in e.g. continuous integration pipelines or git hooks. This way, you can enforce that
all new code adheres to your coding standard without touching the existing code.

## Installation

Require the package with composer:

```sh
composer require --dev isaac/php-code-sniffer-baseliner
```

It is also possible to install this package as a global composer dependency.

## Usage

In order to add `phpcs:ignore` and `phpcs:disable`/`phpcs:enable` instructions throughout your project, run:

```sh
vendor/bin/phpcs-baseliner create-baseline
```

## How does it work?

First, the tool runs `vendor/bin/phpcs` and captures the report. Based on the report output, it will add
`// phpcs:ignore` instructions to the source code for each violation. It will only ignore the sniffs that actually are
violated. In rare cases, adding these instructions could introduce new violations. Therefore, this process is repeated
until no violations are reported by `phpcs`.

## Example

Let's say we want to enforce `declare(strict_types = 1);` statements and native property type hints using
PHP_CodeSniffer. The [Slevomat Coding Standard][slevomat-coding-standard] has sniffs for this:
[`SlevomatCodingStandard.TypeHints.DeclareStrictTypes`][declare-strict-types-sniff]
and [`SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint`][property-type-hint-sniff]. We install
Slevomat Coding Standard and add the sniffs to our ruleset in `phpcs.xml`.

If we now run `vendor/bin/phpcs-baseliner create-baseline` in our project, it will add ignore instructions in all files
not containing `declare(strict_types = 1);` statements or native property type declarations:

```diff
- <?php
+ <?php // phpcs:ignore SlevomatCodingStandard.TypeHints.DeclareStrictTypes -- baseline
  
  class Foo {
+     // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint -- baseline
      private $bar;
  }
```

In some cases, it is not possible to insert a `// phpcs:ignore` instruction directly above the violated line (e.g.
multi-line strings). In those cases, `// phpcs:disable` and `// phpcs:enable` instructions are added:

```diff
  <?php

  class Foo {
+     // phpcs:disable Generic.Files.LineLength.TooLong -- baseline  
      public const BAR = '
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas malesuada, lectus vitae vestibulum vulputate, mi morbi.';
+     // phpcs:enable Generic.Files.LineLength.TooLong -- baseline
  }
```

## Features
- Automatic indentation
- Ignoring a group of multiple exclusions per line, e.g. `// phpcs:ignore Generic.Files.LineLength.TooLong, Generic.Arrays.DisallowLongArraySyntax -- baseline`
- Merging new instructions with existing instructions
- Messages of existing instructions are merged as wel: `// phpcs:ignore Generic.Files.LineLength.TooLong, Generic.Arrays.DisallowLongArraySyntax -- existing message; baseline`
- Using `phpcs:disable`/`phpcs:enable` when inserting `phpcs:ignore` is not possible (i.e. for multi-line strings, including HEREDOCs and NOWDOCs)
- Adding a star prefix when a violation is found within a comment block with stars, e.g.:
  ```php
  /*
   * phpcs:ignore Generic.Files.LineLength.TooLong
   * Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas malesuada, lectus vitae vestibulum vulputate, mi morbi.
   */
  ```
- All features are unit tested, see the [`AddBaselineProcessorTestDataProvider`][unit-test-data-set] class for an extensive test data set.

## Roadmap
- Support processing files that do not start with `<?php` on the first line.
- Support processing files that contain `?>`.
- Support ignoring violations on the first line of a file that end with a multi-line string, example:
  ```php
  <?php echo 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas malesuada, lectus vitae vestibulum vulputate, mi
  morbi.';
  ?>
  ```
- Support detection of and merging with older types of ignore instructions, such as `@phpcsSuppress`.

[php-code-sniffer]: (https://github.com/squizlabs/PHP_CodeSniffer)
[slevomat-coding-standard]: (https://github.com/slevomat/coding-standard)
[declare-strict-types-sniff]: (https://github.com/slevomat/coding-standard#slevomatcodingstandardtypehintsdeclarestricttypes-)
[property-type-hint-sniff]: (https://github.com/slevomat/coding-standard#slevomatcodingstandardtypehintspropertytypehint-)
[unit-test-data-set]: (https://github.com/isaaceindhoven/php-code-sniffer-baseliner/blob/master/tests/File/AddBaselineProcessorTestDataProvider.php)
