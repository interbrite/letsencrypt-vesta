Automate Let's Encrypt Certificate Installation for VestaCP
===========================================================

VestaCP is an open-source web hosting control panel permits website owners to manage their sites through
an easy to use web interface.  Vesta supports optional secure web hosting via HTTPS.

Let's Encrypt is a new certificate authority (CA) that issues free domain validated (DV) SSL/TLS
certificates for enabling secure (HTTPS) web connections. Let's Encrypt automates the certificate request
process, making it possible to secure a domain with a single command.

This tool bridges the gap between Vesta's certificate management and the Certbot client used to install 
Let's Encrypt certificates.  Given one or more Vesta user accounts and, optionally, a list of domain names,
it verifies that the domains exist in Vesta, requests a certificate for each domain and all associated 
aliases, and (upon successful validation) installs the certificate on each domain.

The Certbot client currently requires Python 2.7.

Update
------

This update adds support for automatic certificate renewals using the "at" job scheduler.   It also adds
support for calling the script from cron jobs for those who prefer to handle auto-renewals via cron.

Since the last release, the Let's Encrypt project decoupled the ACME client from the Let's Encrypt
certificate authority.  This release addresses this change.  If upgrading, be sure to install the new
Certbot client, as the script has now been updated to use it.

Usage
-----

Once installed, certificates can be requested by running letsencrypt-vesta command.  Several options
can be passed to determine which domains will be included in the certificate:

    sudo letsencrypt-vesta [-a days] [-m email] [-u] user1 [domainlist1] [...-u userN [domainlistN]]

* The `-a` option schedules an automatic upgrade in `days` days using the at scheduler, if it is available.
* The `-m` option allows the contact email address, passed to Let's Encrypt, to be specified.  If omitted, the email address from the first domain in the certificate will be used.
* The `-u` option specifies a Vesta username and an optional space-separated list of Vesta domains (sites) hosted under that username to add to the certificate.  Each domain and all aliases of that domain will be added to the certificate.  If no domains are specified, the certificate will be issued to every domain in the account.
* Multiple `-u` options can be specified to include domains across multiple Vesta accounts.  For backwards compatibility, the `-u` is optional for the first account.

The same command is used to request new certificates and to renew previously installed certificates.
Note that Let's Encrypt certificates expire every 90 days.  It's recommended to renew them after
60 days.

If a site doesn't already have SSL support it will be enabled with public_html as the SSL home.
Otherwise, the existing SSL certificate will be replaced with the one issued by Let's Encrypt. 

How It Works
------------

Given one or more Vesta-managed usernames and an optional list of domains hosted under that user account, letsencrypt-vesta does the following:

* Looks up the first account's email address to use as the contact email with the certificate request, unless one has been specified with the `-m` option.
* Gets the list of domain aliases for the given domain.  The primary domain name of the first site in the first account listed will be used for the certificate's common name, with any aliases and/or additional domains will be added as subject alternate names (SANs) on the certificate.
* Uses the certbot client to generate a certificate request, validate the request, and download the certificate.
* Uses Vesta's command line tools to install the certificate on each site.
* If the `-a` option is specified and the at scheduler is available, the same command will be scheduled to run again in the specified number of days (60 is recommended).  This will perpetually schedule regular, automatic updates to the certificate without user intervention.

Installation
------------

Installation must be done as root.  If your system doesn't support root logins, append `sudo` to each
of the following commands, or open a root shell with `sudo su -`.

1. Clone both the Let's Encrypt client and this tool into /usr/local.  This will create two new directories, /usr/local/certbot and /usr/local/letsencrypt-vesta.

        cd /usr/local
        git clone https://github.com/certbot/certbot.git
        git clone https://github.com/interbrite/letsencrypt-vesta.git

2. Create the "webroot" directory where Let's Encrypt will write the files needed for domain verification.
    
        mkdir -p /etc/letsencrypt/webroot

3. Choose to implement either the Apache configuration or Nginx configuration (both below) depending on your specific server configuration (the Apache configuration is recommended unless you're only running Nginx).

4. Symlink certbot-auto and letsencrypt-vesta in /usr/local/bin for easier access.  This allows them to be run without needing to know the full path to the programs.

        ln -s /usr/local/certbot/certbot-auto /usr/local/bin/certbot-auto
        ln -s /usr/local/letsencrypt-vesta/letsencrypt-vesta /usr/local/bin/letsencrypt-vesta

5. Create your first certificate.

        letsencrypt-vesta USERNAME DOMAIN

The first time you run certbot-auto (either via letsencrypt-vesta or separately) it will do some initial setup work that could take a few minutes.  Subsequent runs should be faster, as this setup is only needed once per server.


### Apache Configuration

The Apache configuration is recommended for any server running Apache (with or without Nginx).

1. Symlink the Apache conf file in your Apache conf.d directory. This enables Apache to properly serve the validation files from the webroot directory above.

        Depending on OS:
        ln -s /usr/local/letsencrypt-vesta/letsencrypt.conf /etc/httpd/conf.d/letsencrypt.conf
        ln -s /usr/local/letsencrypt-vesta/letsencrypt.conf /etc/apache2/conf.d/letsencrypt.conf

2. Restart Apache to pick up the configuration change.

        Depending on OS:
        service httpd restart
        service apache2 restart

### Nginx Configuration

The Nginx configuration is best suited to servers _not_ running Apache.  On servers running both web servers, the Apache configuration is recommended.

1. Add the following to any of the Nginx virtual host configuration templates you plan to use.  Templates can be found in /usr/local/vesta/data/templates/web/nginx and /usr/local/vesta/data/templates/web/nginx/php5-fpm.  You should add this block along with the other "location" blocks in the file and before the "location @fallback" block, if one exists.

        location /.well-known/acme-challenge {
            default_type text/plain;
            root /etc/letsencrypt/webroot;
        }


2. Reapply the modified template to each existing account with the following command.  This will enable existing sites to use Let's Encrypt certificates.

        /usr/local/vesta/bin/v-rebuild-web-domains USERNAME

3. Restart Nginx to pick up the configuration changes.

        service nginx restart

Updating
--------

To ensure you are using the latest version of letsencrypt-vesta, run the following:

    cd /usr/local/letsencrypt-vesta  
    git pull origin master

Also be sure you have replaced the original Let's Encrypt client with the new Certbot client if you've been running letsencrypt-vesta for a while.  See the installation instructions above for details.

Automatic Renewals
------------------

letsencrypt-vesta now supports automatic renewals using at or cron.  Be sure you have the latest version of letsencrypt-vesta installed, as older versions did not support this functionality.

### at

at is the preferred autorenewal method as it requires no external setup to configure.  However, it uses the Unix at scheduler, which is not running by default on all systems.

Assuming that at is available, simply call letsencrypt-vesta with the `-a` option, followed by the number of days before the certificate should be renewed (60 is recommended):

        letsencrypt-vesta -a 60 USERNAME DOMAIN

letsencrypt-vesta will go through it's normal certificate request and installation process and, when complete, will attempt to schedule the same command to run again in the specified number of days.  Since all subsequent commands will also contain the -a flag, this will effectively schedule updates perpetually.  If at is not available, or the at daemon is not running, letsencrypt-vesta will display a warning and will not reschedule the job, but the certificate initial will be installed.

To check if at is available, run the following commands:

* `which at atd atq atrm`
* `service atd status`

If the first command returns nothing, at is most likely not installed.  To install it, run one of the following commands:

        Depending on OS:
        sudo yum install at
        sudo apt-get install at

Once installed, or if the second command indicates that the service is installed but not running, run the following to start the at daemon:

        sudo service atd start

### cron

Cron is the most well-know job scheduling tool for Unix-type systems.  It schedules jobs to occur automatically at set times on a recurring basis and is installed by default on most systems.  Unlike at, however, cron requires an additional step to set up recurring certificate installations.

If you choose to use cron, you must first run the letsencrypt-vesta command on its own to complete the initial certificate request and installation.  Then you must manually schedule the job to run again by adding it to the root user's crontab file.

To edit the crontab, type the following command:

        sudo crontab -e

If you aren't familiar with the format of a crontab file, [the Wikipedia article on Cron](https://en.wikipedia.org/wiki/Cron) does a good job of describing it.  As an example, this command will schedule the job to run at 2:08 am on the first day of each even numbered month (February, April, June, ...):

        8  2  1  */2  *  /usr/local/bin/letsencrypt-vesta USERNAME DOMAIN

Be sure not to use the -a option when using cron as it could cause the same certificates to be double-renewed.