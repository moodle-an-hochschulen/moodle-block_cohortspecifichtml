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
 * Block Text (on cohorts) - Locallib file
 *
 * @package   block_cohortspecifichtml
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\html_writer;

/**
 * Get the cohorts which the current user is allowed to select in the configuration of a block instance.
 *
 * These are the cohorts which the user is allowed to view in the given context, just like in the cohort selectors
 * of Moodle core. Cohorts from other course categories are never offered. Hidden cohorts are offered if the user has the
 * moodle/cohort:view capability in the context where the cohort was created (the system context or a course category)
 * or the block/cohortspecifichtml:selecthiddencohorts capability in the given context.
 *
 * @param context $context The block context or, while the block is being added, the context of the page.
 *
 * @return array
 */
function block_cohortspecifichtml_get_selectable_cohorts($context) {
    global $DB;

    // This follows the logic of cohort_get_available_cohorts(). However, we can't use this function directly as it only
    // considers the parent contexts. While the block is being added, Moodle core passes the context of the page instead of
    // the block context and this page context would be missing then (for example the system context on the default Dashboard).
    $contextids = $context->get_parent_context_ids($context->contextlevel != CONTEXT_BLOCK);

    // Find the contexts where the user is able to see any cohorts and the contexts where the user is able to see visible cohorts.
    if (has_capability('block/cohortspecifichtml:selecthiddencohorts', $context)) {
        $contextsany = $contextids;
    } else {
        $contextsany = array_filter($contextids, function ($contextid) {
            return has_capability('moodle/cohort:view', context::instance_by_id($contextid));
        });
    }
    $contextsvisible = array_diff($contextids, $contextsany);

    $subqueries = [];
    $params = [];
    if (!empty($contextsany)) {
        [$insql, $inparams] = $DB->get_in_or_equal($contextsany, SQL_PARAMS_NAMED, 'ctxa');
        $subqueries[] = "contextid $insql";
        $params = array_merge($params, $inparams);
    }
    if (!empty($contextsvisible)) {
        [$insql, $inparams] = $DB->get_in_or_equal($contextsvisible, SQL_PARAMS_NAMED, 'ctxv');
        $subqueries[] = "(visible = 1 AND contextid $insql)";
        $params = array_merge($params, $inparams);
    }
    if (empty($subqueries)) {
        return [];
    }

    return $DB->get_records_select('cohort', implode(' OR ', $subqueries), $params, 'name', 'id, name, component');
}

/**
 * Get the cohorts which are configured in a block instance, but which the current user is not allowed to select.
 * These cohorts are still offered in the block configuration form as long as they are configured, so that the user can keep
 * or remove them. Once they have been removed from the configuration, they are not offered anymore.
 *
 * @param block_cohortspecifichtml $blockinstance
 *
 * @return array The unselectable cohorts which still exist.
 */
function block_cohortspecifichtml_get_unselectable_configedcohorts($blockinstance) {
    global $DB;

    $configedcohorts = block_cohortspecifichtml_get_configedcohorts($blockinstance);
    if (empty($configedcohorts)) {
        return [];
    }

    $selectablecohorts = block_cohortspecifichtml_get_selectable_cohorts($blockinstance->context);
    $unselectablecohorts = array_diff($configedcohorts, array_keys($selectablecohorts));
    if (empty($unselectablecohorts)) {
        return [];
    }

    // Cohorts which have been deleted in the meantime are not returned.
    return $DB->get_records_list('cohort', 'id', $unselectablecohorts, 'name', 'id, name, component');
}

/**
 * Check if any cohort exists in Moodle, regardless of its visibility status, context or members.
 *
 * @return bool
 */
function block_cohortspecifichtml_cohorts_exist() {
    global $DB;

    return $DB->record_exists('cohort', []);
}

/**
 * Get the names of the given cohorts.
 * @param array $ids
 *
 * @return array
 */
function block_cohortspecifichtml_get_cohort_names($ids) {
    global $DB;
    $cohortnames = [];

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
        [$in, $params] = $DB->get_in_or_equal($cohorts);
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
        // Cohorts exist in the system.
        if (block_cohortspecifichtml_cohorts_exist()) {
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
        $configedcohorts = [];
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
    if ($blockinstance->page->user_is_editing() && $blockinstance->user_can_edit()) {
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
    $viewalways = has_capability(
        'block/cohortspecifichtml:viewalways',
        context_block::instance($blockinstance->instance->id)
    );

    // Get the configured cohorts.
    $configedcohorts = block_cohortspecifichtml_get_configedcohorts($blockinstance);

    // Get the config for inverted cohort selection.
    $invertselection = block_cohortspecifichtml_get_invertcohortselection($blockinstance);

    $info = '';

    // Users with the capability should see a hint that the visibility is restricted to defined cohort members.
    if ($viewalways == true || block_cohortspecifichtml_get_caneditandediton($blockinstance)) {
        if (!empty($configedcohorts)) {
            if ($invertselection != 1) {
                $info .= html_writer::tag(
                    'span',
                    get_string('restricted', 'moodle'),
                    ['class' => 'badge bg-info text-light']
                );
                $info .= html_writer::tag('span', get_string(
                    'visibletocohorts',
                    'block_cohortspecifichtml'
                ), ['class' => 'small']);
            } else {
                $info .= html_writer::tag(
                    'span',
                    get_string('restricted', 'moodle'),
                    ['class' => 'badge bg-info text-light']
                );
                $info .= html_writer::tag('span', get_string(
                    'notvisibletocohorts',
                    'block_cohortspecifichtml'
                ), ['class' => 'small']);
            }
            // Escape the cohort names as the restriction info is not passed through format_text().
            $cohorts = [];
            foreach (block_cohortspecifichtml_get_cohort_names($configedcohorts) as $cohortname) {
                $cohorts[] = format_string($cohortname, true, ['context' => $blockinstance->context]);
            }
            // Only show the list with restricted cohorts if at least one cohort is selected.
            $info .= html_writer::alist($cohorts, ['class' => 'small']);
            $info .= html_writer::tag('hr', null);
        } else {
            if ($invertselection != 1) {
                $info .= html_writer::tag(
                    'span',
                    get_string('restricted', 'moodle'),
                    ['class' => 'badge bg-info text-light']
                );
                $info .= html_writer::tag('span', get_string(
                    'notvisibletoall',
                    'block_cohortspecifichtml'
                ), ['class' => 'small']);
                $info .= html_writer::tag('hr', null);
            } else {
                $info .= html_writer::tag('span', get_string(
                    'unrestricted',
                    'block_cohortspecifichtml'
                ), ['class' => 'badge bg-info text-light']);
                $info .= html_writer::tag('span', get_string(
                    'visibletoall',
                    'block_cohortspecifichtml'
                ), ['class' => 'small']);
                $info .= html_writer::tag('hr', null);
            }
        }
    }
    return $info;
}
