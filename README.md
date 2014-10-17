Log CPD activities within Moodle
================================

Allows Moodle users to log their CPD via their user profiles.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LukeCarrier/moodle-local_cpd/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LukeCarrier/moodle-local_cpd/?branch=master)

License
-------

    Copyright (c) The Development Manager Ltd

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

Understand locales
------------------

* Moodle 'Mobile Phone' user parameter: The reccomended option if your user database is consistent and the mobile phone number is never placed in the other field. (Note this was refered to as 'Phone 2' in some older versions of Moodle).
* Moodle 'Phone' user parameter: Uses the number in the 'Phone' user parameter field. (Note this was refered to as 'Phone 1' in some older versions of Moodle)
* First valid phone number: This will check both the 'Phone' and 'Mobile Phone' fields. If 'Phone' contains a valid number, then this will be used. If not, the content of 'Mobile phone' will be used.
* Second valid phone number: This will check both the 'Mobile Phone' and 'Phone' fields. If 'Mobile Phone' contains a valid number, then this will be used. If not, the content of 'Phone' will be used.
* Automatically identify UK mobile phone number: This method checks all phone numbers stored in the users profile to see if they start with the normal UK mobile phone number prefix, 07.
* Mobile phone number prefixed with letter m: Use this option if the mobile phone number is stored prefixed with an m (eg m07123 456789) in either phone number field 
