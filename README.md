# SUSHI Lite plugin for OJS
This plugin provides the NISO SUSHI-Lite standard for PKP Open Journal Systems.
## Requirements
* OJS 2.4+
** UsageStats plugin configured and enabled
** restful_urls enabled for full standards compliance
* PHP 5.4+
** libxml enabled
## Usage
Install and enable this plugin in OJS.  The URI sushi-lite/ will respond to the GetReport, GetRegistryEntry, GetStatus, and GetMethods requests as documented in the the SUSHI-Lite proposal.
