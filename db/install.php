<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Text (on cohorts) - Install script
 *
 * @package   block_cohortspecifichtml
 * @copyright 2026 Alexander Bias <bias@alexanderbias.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Install steps for this plugin.
 *
 * @return bool
 */
function xmldb_block_cohortspecifichtml_install() {
    // New installations get the current default role definitions in which the block/cohortspecifichtml:myaddinstance
    // capability is not allocated to the authenticated user role anymore. Thus, the upgrade step which removes this
    // capability from existing role definitions must never be processed on this site, even if the site is upgraded to
    // the next Moodle major version later.
    set_config('myaddinstancecleanupdone', 1, 'block_cohortspecifichtml');

    return true;
}
