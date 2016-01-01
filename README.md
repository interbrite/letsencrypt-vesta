Automate Let's Encrypt Certificate Installation for VestaCP
===========================================================

VestaCP is an open-source web hosting control panel permits website owners to manage their sites through
and easy to use web interface.  Vesta supports optional secure web hosting via HTTPS.

Let's Encrypt is a new certificate authority (CA) that issues free domain validated (DV) SSL/TLS
certificates for enabling secure (HTTPS) web connections. Let's Encrypt automates the certificate request
process, making it possible to secure a domain with a single command.

This tool bridges the gap between Vesta's certificate management and the Let's Encrypt client.  Given a
Vesta user account and domain name, it verifies that the domain exists in Vesta, requests a certificate
for the domain and all associated aliases, using the user's email address in Vesta as the contact, and
(upon successful validation) installs the certificate on the domain.

The Let's Encrypt client currently requires Python 2.7.

Usage
-----

Once installed, certificates can be requested with the following command:

    sudo letsencrypt-vesta USERNAME DOMAIN

where USERNAME is a valid Vesta user account and DOMAIN is a domain hosted within that account.

The same command is used to request new certificates and to renew previously installed certificates.
Note that Let's Encrypt certificates expire every 90 days.  It's recommended to renew them after
60 days.

If the site doesn't already have SSL support it will be enabled with public_html as the SSL home.
Otherwise, the existing SSL certificate will be replaced with the one issued by Let's Encrypt. 

How It Works
------------

Given a Vesta-managed username and a domain hosted under that user account, letsencrypt-vesta does the following:

* Looks up the account's email address to use as the contact email with the certificate request.
* Gets the list of domain aliases for the given domain.  The main domain will be used for the certificate's common name and the aliases will be added as subject alternate names (SANs) on the certificate.
* Uses the letsencrypt client to generate a certificate request, validate the request, and download the certificate.
* Uses Vesta's command line tools to install the certificate on the site.

Installation
------------

Installation must be done as root.  If your system doesn't support root logins, append `sudo` to each
of the following commands, or open a root shell with `sudo su -`.

1. Clone both the Let's Encrypt client and this tool into /usr/local.  This will create two new directories, /usr/local/letsencrypt and /usr/local/letsencrypt-vesta.  
    `cd /usr/local`  
    `git clone https://github.com/letsencrypt/letsencrypt.git`  
    `git clone https://github.com/interbrite/letsencrypt-vesta.git`
2. Create the "webroot" directory where Let's Encrypt will write the files needed for domain verification.  
    `mkdir -p /etc/letsencrypt/webroot`
3. Symlink the Apache conf file in your Apache conf.d directory (this assumes the RedHat standard /etc/httpd/conf.d, adjust to your system as appropriate). This enables Apache to properly serve the validation files from the webroot directory above.  
    `ln -s /usr/local/letsencrypt-vesta/letsencrypt.conf /etc/httpd/conf.d/letsencrypt.conf`
4. Restart Apache to pick up the configuration change  
    `service httpd restart`
5. Symlink letsencrypt-auto and letsencrypt-vesta in /usr/local/bin for easier access.  This allows them to be run without needing to know the full path to the programs.  
    `ln -s /usr/local/letsencrypt/letsencrypt-auto /usr/local/bin/letsencrypt-auto`  
    `ln -s /usr/local/letsencrypt-vesta/letsencrypt-vesta /usr/local/bin/letsencrypt-vesta`
6. Create your first certificate.  
    `letsencrypt-vesta USERNAME DOMAIN`

The first time you run letsencrypt-auto (either via letsencrypt-vesta or separately) it will do some initial setup work that could take a few minutes.  Subsequent runs should be faster, as this setup is only needed once per server.
