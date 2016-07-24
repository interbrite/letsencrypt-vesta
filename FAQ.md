Frequently Asked Questions
==========================

What is letsencrypt-vesta?
--------------------------

letsencrypt-vesta is a shell script that automates the provisioning and installation of free TLS
certificates issued by the Let's Encrypt project in the Vesta web control panel.

For more information about Let's Encrypt, please see https://www.letsencrypt.org.

For more information about Vesta, please see https://vestacp.com


Is letsencrypt-vesta an official sub-project of the Let's Encrypt or Vesta projects?
------------------------------------------------------------------------------------

No, letsencrypt-vesta is published by Interbrite Communications, a small web development agency.
We wrote letsencrypt-vesta because there was no easy way to automatically install Let's Encrypt
certificates on Vesta-managed sites.  Since we released it to the community, we've seen thousands
of downloads and heard lots of great ideas from many loyal users, many of which we've added to the tool.


What will happen to letsencrypt-vesta now that the Vesta project has added support for Let's Encrypt?
-----------------------------------------------------------------------------------------------------

In Vesta's 0.9.8-16 release, the Vesta project added experimental support for Let's Encrypt certificates.
While we are happy to see the Vesta project embracing Let's Encrypt, the new tool currently does not
support all of the features that letsencrypt-vesta users have requested and come to love; namely it
doesn't support multiple sites per certificate or automatic renewals.

When Vesta's Let's Encrypt support includes all of the features of letsencrypt-vesta and it is fully
integrated into the web interface, we'll probably discontinue letsencrypt-vesta, but for the
foreseeable future, we'll be keeping this project active.


I get an error that certbot-auto can't be found.
------------------------------------------------

Make sure that you've installed the Certbot client.  Let's Encrypt decoupled the ACME client from the
certificate authority, renaming the client Certbot.  You're probably seeing this error because you
upgraded letsencrypt-vesta but did not also install Certbot.

To install Certbot, run the following commands (as root):

        cd /usr/local
        git clone https://github.com/certbot/certbot.git
        ln -s /usr/local/certbot/certbot-auto bin/certbot-auto


I get an error that v-list-web-domains-alias can't be found.
------------------------------------------------------------

Make sure you're running the latest version of letsencrypt-vesta. v-list-web-domains-alias was removed
from Vesta in the 0.9.8-16 release.

To upgrade letsencrypt-vesta, run the following commands (as root):

        cd /usr/local/letsencrypt-vesta
        git pull origin master


I get an error followed by "Let's Encrypt returned an error status.  Aborting." when I run letsencrypt-vesta.  Where should I report this?
-------------------------------------------------------------------------------------------------------------------------------------------

The "Let's Encrypt returned an error status.  Aborting." error message is returned when
letsencrypt-vesta detects that the ACME client (Certbot) does not exit successfully.  This generally
means that some kind of error condition that occurred in Certbot, or that the Let's Encrypt certificate
authority was not able to process your request.

Some things that might cause this error are an missing or improperly configured webroot directory, a request for
too many domain names on a single certificate (Let's Encrypt currently limits this to 100), a request with
unsupported TLD's (top level domains), or a domain and/or server limit on the number of certificates issued in
a given time period being reached.

Try to understand what went wrong, make any necessary adjustments, and submit your request again.  If you can't
figure out the problem, you should address it in the [Let's Encrypt support forums](https://community.letsencrypt.org/).

If you think the reason you are getting the error is due to a bug in letsencrypt-vesta, please open an issue in the GitHub project.


Can I use letsencrypt-vesta to manage certificates for the Vesta web interface (on port 8083)?
----------------------------------------------------------------------------------------------

Yes, with a bit of initial setup, this is possible.

1. First, you you don't already have one, create a site in Vesta that matches the name of your server.
2. Use letsencrypt-vesta to request a certificate for this site.
3. Verify that the certificate works by going to https://YOUR_SERVER_DOMAIN.
4. Open /usr/local/vesta/nginx/conf/nginx.conf in your favorite editor and change the lines starting "ssl_certificate:" and "ssl_certificate_key" as follows:
        ssl_certificate: /etc/letsencrypt/live/YOUR_SERVER_DOMAIN/fullchain.pem;
        ssl_certificate_key: /etc/letsencrypt/live/YOUR_SERVER_DOMAIN/privkey.pem;
5. Reload the vesta service so it will pick up the new certificate.
        service vesta reload

 Of course, you'll want to replace YOUR_SERVER_DOMAIN in the examples above with your server's fully-qualified domain name.  You may also need to reload the vesta service whenever the certificate is renewed to get the server to notice the change.


I really think letsencrypt-vesta should do X; or I think I found a bug in Y.
----------------------------------------------------------------------------

Please open an issue in the GitHub project.

https://github.com/interbrite/letsencrypt-vesta/issues/


I've implemented a great new feature.  Will you add it?
-------------------------------------------------------

Send us a pull request and we'll consider.


How can I be notified of updates to letsencrypt-vesta?
------------------------------------------------------

At this time, we don't have an mailing list or any other channel for notifications.  The best way
to keep abreast of updates is to "watch" the GitHub repositiory by clicking the eye icon at the top
of most of the project's pages in GitHub.  You'll then receive email notifications of activity within
the project.