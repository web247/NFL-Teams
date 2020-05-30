CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

NFL Teams module is listing the teams using the Json API from here:
http://delivery.chalk247.com/team_list/NFL.JSON?api_key=74db8efa2a6db279393b433d97c2bc843f8e32b0

* The teams are display in a sortable table on a custom page:
  /nfl-teams

* To make sure there are no performance issues, the page is cached.

* On execution of the cron or on clear cache, the system is loading the
  data again from the Json API feed.

* Everytime the page is loaded, the system logs the source of the data for the table:
  * if the data is loaded from cache or
  * if the data is loaded from the API feed
  Review the logs here: /admin/reports/dblog?type%5B%5D=nfl_teams


REQUIREMENTS
------------

No special requirements.


RECOMMENDED MODULES
-------------------

No recommended modules.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   See: https://www.drupal.org/node/895232 for further information.


CONFIGURATION
-------------

 * The module comes with default values for the API key and the number of items
   per page.
   You can override them on the config page: /admin/config/nfl-teams/settings


MAINTAINERS
-----------

Current maintainers:
 * Adrian Ababei (web247) - https://www.drupal.org/u/web247
