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
 * Block HTML (on cohorts) - Locallib file
 *
 * @package   block_cohortspecifichtml
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();


/**
 * Get all cohorts which exist in Moodle, regardless if there visibility status, context or members.
 *
 * @return array
 */
function block_cohortspecifichtml_get_all_cohorts() {
    global $DB;

    return $DB->get_records_select('cohort', '', [], 'name', 'id, name, component');
}

/**
 * Get the names of the given cohorts.
 * @param array $ids
 *
 * @return array
 */
function block_cohortspecifichtml_get_cohort_names($ids) {
    global $DB;
    $cohortnames = array();

    if (!empty($ids)) {
        $result = $DB->get_records_list('cohort', 'id', $ids, 'name', 'name');
        foreach ($result as $r) {
            $cohortnames[] = $r->name;
        }
    }

    return $cohortnames;
}


/**
 * Check if a user is a member of given cohorts.
 * @param int $userid
 * @param array $cohorts
 *
 * @return bool
 */
function block_cohortspecifichtml_cohorts_is_member($userid, $cohorts) {
    global $DB;

    if (!empty($cohorts)) {
        // Create IN statement for cohorts.
        list($in, $params) = $DB->get_in_or_equal($cohorts);
        // Add param for userid
        $params[] = $userid;
        // Return true if "userid = " . $userid . " AND cohortid IN " . $cohorts;
        return $DB->record_exists_select('cohort_members', "cohortid $in AND userid = ?", $params);
   } else {
       return false;
   }
}
