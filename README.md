Send SMS notifications from within Moodle
=========================================

Extends Moodle to provide several SMS related features:

* Allows teachers to deliver notifications to students of their courses via a
  block plugin.
* [Coming January 2015] Provides a messaging output to allow Moodle
  notifications to be delivered via SMS.

Note: this plugin requires an active PageOne subscription to function.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LukeCarrier/moodle-xxx_pageone/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LukeCarrier/moodle-xxx_pageone/?branch=master)

License
-------

    Copyright (c) The Development Manager Ltd.
    Portions copyright (c) Tim Williams, Auto Train Ltd.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

Requirements
------------

* Moodle 2.4+.
* PHP 5.3+.

Building
--------

1. Clone this repository, and ````cd```` into it
2. Execute ````make```` to generate zip files containing the plugins
3. Upload archives to the ````moodle.org```` plugins site

Understanding locales
---------------------

Locales provide different routines for locating mobile phone numbers within user
profiles. There are four included with the current release:

* *Moodle "Mobile Phone" user profile field*
  Recommended if your database is consistent the mobile number is always within
  this field (referred to as "Phone 1" in earlier Moodle releases).
* *Moodle "Phone" user profile field*
  Uses the number in the 'Phone' user parameter field (referred to as "Phone 1"
  in earlier Moodle releases).
* *First valid phone number*
  Checks both the "Phone" and "Mobile Phone", using the first number identified
  as valid.
* *First valid phone number prefixed with letter "m"*
  Checks both the "Phone" and "Mobile Phone", using the first number identified
  as starting with the letter "m".
