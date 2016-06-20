letsencrypt-vesta Changelog
===========================

June 19, 2016 (version 0.3)
---------------------------

* Updated installation instructions to use Certbot now that the Let's Encrypt project has decoupled the ACME client from the certificate authority.
* Added support for automatic renewals using at or cron
* Script now sources /etc/profile.d/vesta.sh so that it can be called without a full Vesta environment bing already set up, such as when using cron jobs (thanks [phillip-p-jones](https://github.com/philip-p-jones), [iscario-computer](https://github.com/iscario-computer))
* Added a changelog file and tagging/version numbering

January 12, 2016
----------------

* Allowed for one certificate to be issued for and installed across multiple Vesta accounts (thanks [GestDPS](https://github.com/GestDPS) for the suggestion)
* Added better support for Debian-based systems (thanks [ttcttctw](https://github.com/ttcttctw))
* Fixed a typo in the installion instructions (thanks [GestDPS](https://github.com/GestDPS))

December 29, 2015
-----------------

* Initial release...just a basic tool for installing Let's Encrypt certs on my Vesta-based sites.  I never had any idea it would become as popular as it has!  Thanks for all the support!