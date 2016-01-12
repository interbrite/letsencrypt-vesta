Automate Let's Encrypt Certificate Installation for VestaCP
===========================================================

VestaCP is an open-source web hosting control panel permits website owners to manage their sites through
and easy to use web interface.  Vesta supports optional secure web hosting via HTTPS.

Let's Encrypt is a new certificate authority (CA) that issues free domain validated (DV) SSL/TLS
certificates for enabling secure (HTTPS) web connections. Let's Encrypt automates the certificate request
process, making it possible to secure a domain with a single command.

This tool bridges the gap between Vesta's certificate management and the Let's Encrypt client.  Given one
or more Vesta user accounts and, optionally, a list of domain names, it verifies that the domains exist
in Vesta, requests a certificate for each domain and all associated aliases, and (upon successful
validation) installs the certificate on each domain.

The Let's Encrypt client currently requires Python 2.7.

Update
------

Based on feedback, the script has been updated to support adding multiple sites on a single certificate.
As Let's Encrypt is currently enforcing a limit of 5 certificates per week per top-level domain, hopefully
this change will prevent users with manny subdomains from hitting those limites.  Let's Encrypt currently
allows up to 100 domains per certificate. 

Usage
-----

Once installed, certificates can be requested by running letsencrypt-vesta command.  Several options
can be passed to determine which domains will be included in the certificate:

    sudo letsencrypt-vesta [-m email] [-u] user1 [domainlist1] [...-u userN [domainlistN]]

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
* Uses the letsencrypt client to generate a certificate request, validate the request, and download the certificate.
* Uses Vesta's command line tools to install the certificate on each site.

Installation
------------

Installation must be done as root.  If your system doesn't support root logins, append `sudo` to each
of the following commands, or open a root shell with `sudo su -`.

1. Clone both the Let's Encrypt client and this tool into /usr/local.  This will create two new directories, /usr/local/letsencrypt and /usr/local/letsencrypt-vesta.  
    `cd /usr/local  
    git clone https://github.com/letsencrypt/letsencrypt.git  
    git clone https://github.com/interbrite/letsencrypt-vesta.git`
2. Create the "webroot" directory where Let's Encrypt will write the files needed for domain verification.  
    `mkdir -p /etc/letsencrypt/webroot`
3. Choose to implement either the Apache configuration or Nginx configuration (both below) depending on your specific server configuration (the Apache configuration is recommended unless you're running Nginx only).
4. Symlink letsencrypt-auto and letsencrypt-vesta in /usr/local/bin for easier access.  This allows them to be run without needing to know the full path to the programs.  
    `ln -s /usr/local/letsencrypt/letsencrypt-auto /usr/local/bin/letsencrypt-auto  
    ln -s /usr/local/letsencrypt-vesta/letsencrypt-vesta /usr/local/bin/letsencrypt-vesta`
5. Create your first certificate.  
    `letsencrypt-vesta USERNAME DOMAIN`

### Apache Configuration

The Apache configuration is recommended for any server running Apache (with or without Nginx).

1. Symlink the Apache conf file in your Apache conf.d directory (this assumes the RedHat standard /etc/httpd/conf.d, adjust to your system as appropriate). This enables Apache to properly serve the validation files from the webroot directory above.  
    `Depending on OS version:
    ln -s /usr/local/letsencrypt-vesta/letsencrypt.conf /etc/httpd/conf.d/letsencrypt.conf
    ln -s /usr/local/letsencrypt-vesta/letsencrypt.conf /etc/apache2/conf.d/letsencrypt.conf`
2. Restart Apache to pick up the configuration change.  
    `Depending on OS version:
    service httpd restart
    service apache2 restart`

### Nginx Configuration

The Nginx configuration is best suited to servers _not_ running Apache.  On servers running both web servers, the Apache configuration is recommended.

1. Add the following to any of the Nginx virtual host configuration templates you plan to use.  Templates can be found in /usr/local/vesta/data/templates/web/nginx and /usr/local/vesta/data/templates/web/nginx/php5-fpm.  You should add this block along with the other "location" blocks in the file and before the "location @fallback" block, if one exists.  
    `location /.well-known/acme-challenge {
        default_type text/plain;
        root /etc/letsencrypt/webroot;
    }`
2. Reapply the modified template to each existing account with the following command.  This will enable existing sites to use Let's Encrypt certificates.  
    `/usr/local/vesta/bin/v-rebuild-web-domains USERNAME`
3. Restart Nginx to pick up the configuration changes.  
    `service nginx restart`

Updating
--------

To ensure you are using the latest version of letsencrypt-vesta, run the following:

    `cd /usr/local/letsencrypt-vesta  
    git pull origin master`