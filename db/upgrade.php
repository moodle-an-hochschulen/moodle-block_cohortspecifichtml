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
 * Text (on cohorts) - Upgrade script
 *
 * @package   block_cohortspecifichtml
 * @copyright 2026 Alexander Bias <bias@alexanderbias.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for this plugin.
 *
 * @param int $oldversion the version we are upgrading from.
 * @return bool
 */
function xmldb_block_cohortspecifichtml_upgrade($oldversion) {
    global $DB, $OUTPUT;

    if ($oldversion < 2026100501) {
        // The block/cohortspecifichtml:myaddinstance capability is not allocated to the authenticated user role by default
        // anymore. Remove it from the role definition of all roles with the user archetype as well, but only if no user
        // has added the block to their Dashboard yet. Otherwise, we assume that the capability is used deliberately.
        // Overrides in other contexts than the system context are not touched as they have been set deliberately by an admin.
        $sql = 'SELECT 1
                  FROM {block_instances} bi
                  JOIN {context} ctx ON ctx.id = bi.parentcontextid
                 WHERE bi.blockname = :blockname
                       AND ctx.contextlevel = :contextlevel
                       AND ' . $DB->sql_like('bi.pagetypepattern', ':pagetypepattern');
        $params = [
            'blockname' => 'cohortspecifichtml',
            'contextlevel' => CONTEXT_USER,
            'pagetypepattern' => 'my-%',
        ];
        if (!$DB->record_exists_sql($sql, $params)) {
            $systemcontext = context_system::instance();
            foreach (get_archetype_roles('user') as $role) {
                unassign_capability('block/cohortspecifichtml:myaddinstance', $role->id, $systemcontext->id);
            }

            // And show a notification about that fact (this also looks fine in the CLI installer).
            $notification = new \core\output\notification(
                get_string('updater_2026100501_removemyaddinstance', 'block_cohortspecifichtml'),
                \core\output\notification::NOTIFY_INFO
            );
            $notification->set_show_closebutton(false);
            echo $OUTPUT->render($notification);
        }

        // Cohortspecifichtml savepoint reached.
        upgrade_block_savepoint(true, 2026100501, 'cohortspecifichtml');
    }

    return true;
}
