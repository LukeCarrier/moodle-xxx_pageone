MoodleMobile block
------------------

This block has been written for use with the Moodle VLE. If you are not running Moodle,
then this download is not for you.

Licence
-------

This code is licenced under the GPL v3 licence (see gpl.txt) and is copyright to PageOne. If you have
any further questions on the licencing of this block, please contact us on info@pageone.co.uk.


Requirements
------------

Moodle 1.9+ or 2.0+
PHP 5
DOMXML and Curl extensions

The extensions should be included with most binary builds of PHP on windows systems, linux users might
need to install additional package(s). eg for CentOS 5, use the php-xml package.

If you are installing PHP from source, make sure you use the --with-dom --with-zlib --with-curl build
options.

Installation or Upgrade
-----------------------

1) Copy the directory containing this readme file (or unzip the original download) into the moodle/blocks
   directory of your moodle installation.
2) Open your web browser and go to /admin on your moodle server or click the 'notifications' link in the
   site admin block, if necessary login using an account with admin privileges. The block should now
   automatically install or update itself.
3) Assuming there were no error messages from step 2, go to modules>blocks from the site administration
   menu. Find 'MoodleMobile' and click associated the settings link. If upgrading skip straight to step 8.
4) Choose a method for identifying the mobile phone number from the Moodle user database and fill in your
   PageOne account details.
5) Ignore the MSISDN option for now.
6) Click Save changes.
7) Return to the MoodleMobile settings page
8) Check that the PageOne server login status box at the bottom of the page is "OK". If this has failed,
   try reloading the settings page (the Moodle settings page sometimes reloads before the previously
   entered details have been properly saved). If the login still fails, please check your user name and
   password.
9) Choose a system default MSISDN (remember to save the change) and check that that SMS Callback service
   is listed as registered.
10)You can now add MoodleMobile blocks to courses in the normal way and tutors should be able to send 
   text messages.

Callbacks for Incoming messages
--------------------------------

Automatic registration of callbacks for incoming messages is no longer supported by PageOne. If you
are upgrading from a previous MoodleMobile release or making a new installation, you should contact
PageOne and get them to check that the Callback URL settings are correct for your server. The correct
callback URL is shown on the MoodleMobile block settings page.


Patches for enhanced functionality
----------------------------------


These are not included with the version supplied via the Moodle website, please visit the PageOne MoodleMobile download page to find out more.
