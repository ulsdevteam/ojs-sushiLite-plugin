# SUSHI Lite plugin for OJS

This plugin provides the NISO SUSHI-Lite standard for PKP Open Journal Systems.

## Requirements

* OJS 2.4.8 or a later release of OJS 2.x
  * UsageStats plugin configured and enabled
    * see: User Home -> Journal Manager -> System Plugins -> Generic Plugins -> Usage Statistics
  * For full standards compliance, disable_path_info should remain off
    * see: config.inc.php's general section and the disable_path_info directive for details.
* PHP 5.3 or later
  * libxml enabled

## Installation

Install this as a "generic" plugin in OJS.  To install manually via the filesystem, extract the contents of this archive to a directory (e.g. "sushiLite") under "plugins/generic" in your OJS root.  To install via Git submodule, target that same directory path: `git submodule add https://github.com/ulsdevteam/ojs-sushiLite-plugin plugins/generic/sushiLite` and `git submodule update --init --recursive plugins/generic/sushiLite`.  Run the upgrade script to register this plugin, e.g.: `php tools/upgrade.php upgrade`

## Usage

The URI *{base_url}*/sushiLite/*{version}*/ will respond to the GetReport requests as documented in the the [SUSHI-Lite proposal](http://www.niso.org/workrooms/sushi/sushi_lite/).  The currently support SUSHI-Lite version is 1.7.  For example:
* Fetch the current AR1, reflecting the last month's usage, for any journal hosted on the site:
  * /ojs/index.php/index/sushiLite/v1_7/GetReport?Report=AR1
* Fetch the release 4.1 JR1 for all of 2015 for "myJournal":
  * /ojs/index.php/myJournal/sushiLite/v1_7/GetReport?Report=JR1&Release=4.1&BeginDate=2015-01-01&EndDate=2015-12-31

Your base URL and journal name will vary.

## Author / License

Written by Clinton Graham for the [University of Pittsburgh](http://www.pitt.edu).  Copyright (c) University of Pittsburgh.

Released under a license of GPL v2 or later.
