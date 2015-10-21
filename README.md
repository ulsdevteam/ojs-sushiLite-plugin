# SUSHI Lite plugin for OJS

This plugin provides the NISO SUSHI-Lite standard for PKP Open Journal Systems.

## Requirements

* OJS 2.4.8
 * Base OJS 2.4.8, or 2.4.7 must be augmented by https://github.com/pkp/ojs/compare/ojs-stable-2_4_7...ulsdevteam:stable247_sushiLite
 * UsageStats plugin configured and enabled
 * restful_urls enabled for full standards compliance
* PHP 5.4+
 * libxml enabled

## Usage

Install and enable this plugin in OJS.  The URI *{base_url}*/sushiLite/*{version}*/ will respond to the GetReport requests as documented in the the SUSHI-Lite proposal.
