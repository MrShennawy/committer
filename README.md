# The Committer

<a href="https://github.com/mrshennawy/committer/actions"><img src="https://github.com/mrshennawy/committer/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/mrshennawy/committer"><img src="https://img.shields.io/packagist/dt/mrshennawy/committer" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/mrshennawy/committer"><img src="https://img.shields.io/packagist/v/mrshennawy/committer" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/mrshennawy/committer"><img src="https://img.shields.io/packagist/l/mrshennawy/committer" alt="License"></a>

<p align="center">
  <img width="600" src="docs/assets/committer.svg">
</p>


**committer** is a Composer package that streamlines the process of writing standard Git commit messages. It helps developers maintain a consistent and clean commit history.

## Table of Contents

1. [Installation](#installation)
2. [Usage](#usage)
3. [Authors](#authors)
4. [License](#license)

## Installation

Committer utilizes [Composer](https://getcomposer.org/) to manage its dependencies. So, before using Committer, make sure you have Composer installed on your machine.


```bash
composer global require mrshennawy/committer
```
Make sure to place Composer's system-wide vendor bin directory in your `$PATH` so the Committer executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

- macOS: `$HOME/.composer/vendor/bin`
  - ```bash
    export PATH=$PATH:$HOME/.composer/vendor/bin
- Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`
  - ```bash
    set PATH=%PATH%;%USERPROFILE%\AppData\Roaming\Composer\vendor\bin
- GNU / Linux Distributions: `$HOME/.config/composer/vendor/bin` or `$HOME/.composer/vendor/bin`
  - ```bash
    export PATH=$PATH:$HOME/.config/composer/vendor/bin
  or
  - ```bash
    export PATH=$PATH:$HOME/.composer/vendor/bin`

You could also find the composer's global installation path by running `composer global about` and looking up from the first line.

## Usage

After installing the package, you can use it by running the following command:

```bash
gc
```

## Authors

- Mahmoud Shennawy  | [GitHub](https://github.com/MrShennawy)  | [LinkedIn](https://www.linkedin.com/in/mrshennawy) | <m.alshenaawy@gmail.com>

See also the list of [contributors](https://github.com/mrshennawy/committer/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
