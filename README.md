moodle-block_cohortspecifichtml
===============================

[![Moodle Plugin CI](https://github.com/moodle-an-hochschulen/moodle-block_cohortspecifichtml/workflows/Moodle%20Plugin%20CI/badge.svg?branch=master)](https://github.com/moodle-an-hochschulen/moodle-block_cohortspecifichtml/actions?query=workflow%3A%22Moodle+Plugin+CI%22+branch%3Amaster)

This HTML block provides the possibility to selectively display the block to members of specific cohorts.


Requirements
------------

This plugin requires Moodle 3.11+


Motivation for this plugin
--------------------------

In Moodle core, there is the HTML block which allows you to add HTML content to courses. The content of these block instances is shown to all users that are allowed to access the page it is placed on. However, in some scenarios, you need to restrict the visibility of the HTML block to members of specific cohorts. This plugin adds this functionality to the HTML block. This means that the instances of this block behave generally like any other HTML block instance, only with the feature that the visibility of the block is restricted to defined cohorts.


Installation
------------

Install the plugin like any other plugin to folder
/blocks/cohortspecifichtml

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it does not do anything to Moodle yet.

To configure the plugin and its behaviour, please visit:
Site administration -> Plugins -> Blocks -> HTML (on cohort)

This plugin has the following additional settings for block instances:

### Cohorts

With this setting you can select the cohorts for which the block should be displayed. A prerequisite is of course that cohorts exist in your Moodle instance.

Example: If a user matches at least one of the selected cohorts the block will be displayed to him.

### Invert the selection (hide it for the selected cohorts)

With this setting you can invert your selection. This means that the block will not be shown to members of the selected cohort but to everyone else.

Examples:
* If a user matches at least one of the selected cohorts the block will not be displayed to him, even if he is also member of a cohort that is not selected.
* If you have selected none of the cohorts and invert that, the block will be visible for all users.


Capabilities
-------------

This plugin also introduces these additional capabilities:

### block/cohortspecifichtml:viewalways

HTML (on cohorts) blocks are always visible to the capability owners independently from any membership in defined cohorts. By default, this capability is not allocated to any standard roles.
As an admin, you might want to add this capability to the teacher role to let teachers see all the HTML (on cohort) block contents independently from the editing state of the course.


Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
http://moodle.org/plugins/view/block_cohortspecifichtml

The latest development version can be found on Github:
https://github.com/moodle-an-hochschulen/moodle-block_cohortspecifichtml


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/moodle-an-hochschulen/moodle-block_cohortspecifichtml/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Due to limited resources, the functionality of this plugin is primarily implemented for our own local needs and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/moodle-an-hochschulen/moodle-block_cohortspecifichtml/issues

Please create pull requests on Github:
https://github.com/moodle-an-hochschulen/moodle-block_cohortspecifichtml/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

Due to limited resources, this plugin is only maintained for the most recent major release of Moodle as well as the most recent LTS release of Moodle. Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.

Apart from these maintained releases, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that this plugin still works with a new major release - please let us know on Github.

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.

As the plugin creator, we manage the translation into german for our own local needs on AMOS. Please contribute your translation into all other languages in AMOS where they will be reviewed by the official language pack maintainers for Moodle.


Right-to-left support
---------------------

This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send us a pull request on Github with modifications.


Maintainers
-----------

The plugin is maintained by\
Moodle an Hochschulen e.V.


Copyright
---------

The copyright of this plugin is held by\
Moodle an Hochschulen e.V.

Individual copyrights of individual developers are tracked in PHPDoc comments and Git commits.


Initial copyright
-----------------

This plugin was initially built, maintained and published by\
Ulm University\
Communication and Information Centre (kiz)\
Kathrin Osswald

It was contributed to the Moodle an Hochschulen e.V. plugin catalogue in 2022.
