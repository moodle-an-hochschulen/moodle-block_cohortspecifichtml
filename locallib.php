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
        // Add param for userid.
        $params[] = $userid;
        // Return true if "userid = " . $userid . " AND cohortid IN " . $cohorts.
        return $DB->record_exists_select('cohort_members', "cohortid $in AND userid = ?", $params);
    } else {
        return false;
    }
}


/**
 * Get the boolean if a block should be shown to a user.
 * @param block_cohortspecifichtml $blockinstance
 *
 * @return boolean
 */
function block_cohortspecifichtml_show_block($blockinstance) {
    global $USER;

    // Initialise showblock with false.
    $showblock = false;

    // Initialise variable to check if a user should see the block independently from cohort memberships.
    $viewalways = has_capability('block/cohortspecifichtml:viewalways', context_block::instance($blockinstance->instance->id));

    // Initialise variable for the configured cohorts.
    $configedcohorts = block_cohortspecifichtml_get_configedcohorts($blockinstance);

    // Initialise variable to check if the block content will be shown to a user and don't show the block initially.
    // If the checkbox invertcohortselection is existing.
    $invertselection = block_cohortspecifichtml_get_invertcohortselection($blockinstance);

    // Show the block to users that have the capability to see the block independent from a
    // corresponding cohort membership.
    if ($viewalways == true) {
        $showblock = true;
    } else {
        // Get the existent cohorts.
        $allcohorts = block_cohortspecifichtml_get_all_cohorts();
        // Cohorts exist in the system.
        if (!empty($allcohorts)) {
            // Get the selected cohorts, if any.
            // No cohort is selected.
            if (empty($configedcohorts)) {
                // If checkbox to invert the "selection" is enabled, then show the content to all users.
                if ($invertselection == 1) {
                    $showblock = true;
                }
            } else { // At least one cohort is selected.
                if ($invertselection != 1) {
                    if (block_cohortspecifichtml_cohorts_is_member($USER->id, $configedcohorts)) {
                        $showblock = true;
                    }
                } else {
                    if (!block_cohortspecifichtml_cohorts_is_member($USER->id, $configedcohorts)) {
                        $showblock = true;
                    }
                }
            }
        }
    }
    return $showblock;
}


/**
 * Get the configured cohorts for a block_cohortsspecifichtml instance.
 * @param block_cohortspecifichtml $blockinstance
 *
 * @return array
 */
function block_cohortspecifichtml_get_configedcohorts($blockinstance) {
    // Initialise variable for the configured cohorts.
    if (isset($blockinstance->config->cohorts)) {
        $configedcohorts = $blockinstance->config->cohorts;
    } else {
        $configedcohorts = array();
    }
    return $configedcohorts;
}


/**
 * Get the configured setting for invertcohortselection.
 * @param block_cohortspecifichtml $blockinstance
 *
 * @return boolean
 */
function block_cohortspecifichtml_get_invertcohortselection($blockinstance) {
    // Initialise variable to check if the block content will be shown to a user and don't show the block initially.
    // If the checkbox invertcohortselection is existing.
    if (!empty($blockinstance->config->invertcohortselection)) {
        $invertselection = $blockinstance->config->invertcohortselection;
    } else {
        $invertselection = 0;
    }
    return $invertselection;
}

/**
 * Get the boolean if a user is viewing the page in editing mode and is also allowed to edit the block instance.
 * @param block_cohortspecifichtml $blockinstance
 *
 * @return boolean
 */
function block_cohortspecifichtml_get_caneditandediton($blockinstance) {
    global $PAGE;
    if ($PAGE->user_is_editing() && $blockinstance->user_can_edit()) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get the text for the restriction information.
 * @param block_cohortspecifichtml $blockinstance
 *
 * @return string
 */
function block_cohortspecifichtml_get_restrictioninfo($blockinstance) {
    // Initialise variable to check if a user should see the block independently from cohort memberships.
    $viewalways = has_capability('block/cohortspecifichtml:viewalways',
        context_block::instance($blockinstance->instance->id));

    // Get the configured cohorts.
    $configedcohorts = block_cohortspecifichtml_get_configedcohorts($blockinstance);

    // Get the config for inverted cohort selection.
    $invertselection = block_cohortspecifichtml_get_invertcohortselection($blockinstance);

    $info = '';

    // Users with the capability should see a hint that the visibility is restricted to defined cohort members.
    if ($viewalways == true || block_cohortspecifichtml_get_caneditandediton($blockinstance)) {
        if (!empty($configedcohorts)) {
            if ($invertselection != 1) {
                $info .= html_writer::tag('span', get_string('restricted', 'moodle'),
                    array('class' => 'label label-info'));
                $info .= html_writer::tag('span', get_string('visibletocohorts',
                    'block_cohortspecifichtml'), array('class' => 'small'));
            } else {
                $info .= html_writer::tag('span', get_string('restricted', 'moodle'),
                    array('class' => 'label label-info'));
                $info .= html_writer::tag('span', get_string('notvisibletocohorts',
                    'block_cohortspecifichtml'), array('class' => 'small'));
            }
            $cohorts = block_cohortspecifichtml_get_cohort_names($configedcohorts);
            // Only show the list with restricted cohorts if at least one cohort is selected.
            $info .= html_writer::alist($cohorts, array('class' => 'small'));
            $info .= html_writer::tag('hr', null);
        } else {
            if ($invertselection != 1) {
                $info .= html_writer::tag('span', get_string('restricted', 'moodle'),
                    array('class' => 'label label-info'));
                $info .= html_writer::tag('span', get_string('notvisibletoall',
                    'block_cohortspecifichtml'), array('class' => 'small'));
                $info .= html_writer::tag('hr', null);
            } else {
                $info .= html_writer::tag('span', get_string('unrestricted',
                    'block_cohortspecifichtml'), array('class' => 'label label-info'));
                $info .= html_writer::tag('span', get_string('visibletoall',
                    'block_cohortspecifichtml'), array('class' => 'small'));
                $info .= html_writer::tag('hr', null);
            }
        }
    }
    return $info;
}
