moodle-block_cohortspecifichtml
===============================

Changes
-------

### v3.10-r3

* 2021-12-25 - Bugfix: Safer unserializing during block restore (see MDL-70823 for details)

### v3.10-r2

* 2021-09-29 - Make Moodle Codechecker happy again
* 2021-02-05 - Move Moodle Plugin CI from Travis CI to Github actions

### v3.10-r1

* 2021-01-09 - Change Bootstrap labels to badges to comply with Bootstrap 4 standards
* 2021-01-09 - Prepare compatibility for Moodle 3.10.
* 2021-01-06 - Change in Moodle release support:
               For the time being, this plugin is maintained for the most recent LTS release of Moodle as well as the most recent major release of Moodle.
               Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.
* 2021-01-06 - Improvement: Declare which major stable version of Moodle this plugin supports (see MDL-59562 for details).

### Release v3.9-r1

* 2020-07-16 - Prepare compatibility for Moodle 3.9.

### Release v3.8-r1

* 2020-02-21 - Added new functions due to upstream changes in block HTML and Moodle core.
* 2020-02-18 - Prepare compatibility for Moodle 3.8.
* 2019-06-16 - Removed additional tags for dev purposes from behat tests.

### Release v3.7-r1

* 2019-06-17 - Adjusted lib.php due to upstream changes in block HTML.
* 2019-06-17 - Prepare compatibility for Moodle 3.7.

### Release v3.6-r1

* 2019-01-15 - Added newly introduced core function "get_content_for_external".
* 2019-01-15 - Check compatibility for Moodle 3.6, no functionality change.
* 2018-05-12 - Changed travis.yml due to upstream changes.

### Release v3.5-r1

* 2018-05-28 Fixed PHPDoc errors.
* 2018-05-28 Added hr to all possible cases to separate content from cohort restriction info.
* 2018-05-25 Improved Behat tests slighty and added new scenarios.
* 2018-05-25 Changed Behat tests due to changes in the parent block_html.
* 2018-05-25 Check compatibility for Moodle 3.5, no functionality change.

### Release v3.4-r2

* 2018-05-16 Implement Privacy API.
* 2018-04-11 Updated README.md.

### Release v3.4-r1

* 2018-04-10 Prepare compatibility for Moodle 3.4, no functionality change.

### Release v3.3-r1

* 2018-04-06 Prepare compatibility for Moodle 3.3, no functionality change.

### Release v3.2-r4

* 2018-03-29 Added cohorts check for file serving.
* 2018-03-21 Improved displaying of blocks depending on editing mode and ability to edit the block instance.
* 2018-03-20 Improved SQL statement for function block_cohortspecifichtml_cohorts_is_member.
* 2018-03-20 Minor improvements to language file and behat tests.

### Release v3.2-r1 - v3.2-r3

* 2017-12-19 Initial version.
