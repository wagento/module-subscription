## FOR SUPPORT PLEASE CONTACT THE FOLLOWING
support@wagento.com

## Introduction
This official Braintree Subscription module developed by wagento.

## Version
We are adding a new version management to make module installation available through composer, so this is the table for reference: 

Magento Version | Composer prefix 
----            | ---- 
2.2.x           | 101.0.9
2.3.X           | 101.0.9

So if you are in magento 2.2.x or magento 2.3.x to install by composer just execute: `composer require wagento/module-subscription:101.0.9`

BUT in file `etc/module.xml` version will be the same for all composer version, use `setup_version` as global version reference.

## Support
If you are facing any issue with module installation and configuration please send an email to support@wagento.com

## Changelog
Based in `setup_version`

v2.3.1
- Make module compatible with 2.4.4
- Resolve Magento_Braintree dependency issues.
- Module compatible with php 8.1 version.


v2.2.1
- Make module compatible with 2.3.4 and 2.3.5
- Resolve reminder email cron issue
- Resolve cart page Unsubscribe issue 
- Resolve checkout issue with subscription
- Resolve Minicart update after add subscription Product

v2.1.1
- Initial module integration for Braintree Subscription 
