## FOR SUPPORT PLEASE CONTACT THE FOLLOWING
support@mage365.zendesk.com

## Introduction
This official Braintree Subscription module developed by wagento.

## Version
We are adding a new version management to make module installation available through composer, so this is the table for reference: 

Magento Version | Composer prefix 
----            | ---- 
2.2.x           | 100.x.x

So if you are in magento 2.2.x to install by composer just execute: `composer require wagento/module-subscription::100.0.13`

BUT in file `etc/module.xml` version will be the same for all composer version, use `setup_version` as global version reference.

## Support
If you are facing any issue with module installation and configuration please send an email to support@mage365.zendesk.com

## Changelog
Based in `setup_version`

v2.1.1
- Initial module integration for Braintree Subscription 
