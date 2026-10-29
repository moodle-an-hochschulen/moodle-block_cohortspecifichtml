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

namespace block_cohortspecifichtml;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/cohortspecifichtml/locallib.php');

/**
 * Text (on cohorts) - Unit tests for the locallib functions.
 *
 * @package   block_cohortspecifichtml
 * @copyright 2026 Alexander Bias <bias@alexanderbias.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class locallib_test extends \advanced_testcase {
    /**
     * Tests the cohorts which are offered for selection in the block configuration form.
     * @covers ::block_cohortspecifichtml_get_selectable_cohorts
     */
    public function test_get_selectable_cohorts(): void {
        global $DB;
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $othercategory = $this->getDataGenerator()->create_category();
        $categorycontext = \context_coursecat::instance($category->id);
        $course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $coursecontext = \context_course::instance($course->id);
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $systemvisible = $this->getDataGenerator()->create_cohort(['visible' => 1]);
        $systemhidden = $this->getDataGenerator()->create_cohort(['visible' => 0]);
        $categoryhidden = $this->getDataGenerator()->create_cohort(['visible' => 0, 'contextid' => $categorycontext->id]);
        $othercategoryvisible = $this->getDataGenerator()->create_cohort(
            ['visible' => 1, 'contextid' => \context_coursecat::instance($othercategory->id)->id]
        );

        // A teacher is only offered the visible cohorts from the parent contexts.
        $this->setUser($teacher);
        $this->assertEqualsCanonicalizing(
            [$systemvisible->id],
            array_keys(block_cohortspecifichtml_get_selectable_cohorts($coursecontext))
        );

        // An admin is offered the hidden cohorts as well, but not the cohorts from other categories.
        $this->setAdminUser();
        $this->assertEqualsCanonicalizing(
            [$systemvisible->id, $systemhidden->id, $categoryhidden->id],
            array_keys(block_cohortspecifichtml_get_selectable_cohorts($coursecontext))
        );

        // While a block is being added, the page context is passed and its own cohorts are offered as well.
        $this->assertEqualsCanonicalizing(
            [$systemvisible->id, $systemhidden->id],
            array_keys(block_cohortspecifichtml_get_selectable_cohorts(\context_system::instance()))
        );
        $this->assertEqualsCanonicalizing(
            [$systemvisible->id, $systemhidden->id, $categoryhidden->id],
            array_keys(block_cohortspecifichtml_get_selectable_cohorts($categorycontext))
        );

        // A teacher with the selecthiddencohorts capability is offered the hidden cohorts from the parent contexts as well,
        // but still not the cohorts from other categories.
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        assign_capability('block/cohortspecifichtml:selecthiddencohorts', CAP_ALLOW, $teacherrole->id, $coursecontext);
        $this->setUser($teacher);
        $this->assertEqualsCanonicalizing(
            [$systemvisible->id, $systemhidden->id, $categoryhidden->id],
            array_keys(block_cohortspecifichtml_get_selectable_cohorts($coursecontext))
        );
    }

    /**
     * Tests the configured cohorts which are additionally offered in the block configuration form.
     * @covers ::block_cohortspecifichtml_get_unselectable_configedcohorts
     */
    public function test_get_unselectable_configedcohorts(): void {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $visiblecohort = $this->getDataGenerator()->create_cohort(['visible' => 1]);
        $hiddencohort = $this->getDataGenerator()->create_cohort(['visible' => 0]);
        $deletedcohort = $this->getDataGenerator()->create_cohort(['visible' => 0]);
        $othercategory = $this->getDataGenerator()->create_category();
        $othercategorycohort = $this->getDataGenerator()->create_cohort(
            ['contextid' => \context_coursecat::instance($othercategory->id)->id]
        );

        // Create a block which is configured with all cohorts, as it was possible with earlier plugin versions.
        $this->setAdminUser();
        $page = new \moodle_page();
        $page->set_context(\context_course::instance($course->id));
        $page->set_course($course);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->blocks->load_blocks();
        $page->blocks->add_block_at_end_of_default_region('cohortspecifichtml');
        $blockrecord = $this->get_last_block_record();
        $block = block_instance('cohortspecifichtml', $blockrecord);
        $block->config = (object) [
            'cohorts' => [$visiblecohort->id, $hiddencohort->id, $deletedcohort->id, $othercategorycohort->id],
        ];
        cohort_delete_cohort($deletedcohort);

        // The teacher is additionally offered the configured hidden cohort and the cohort from the other category,
        // but not the visible cohort which is selectable anyway and not the deleted cohort.
        $this->setUser($teacher);
        $this->assertEqualsCanonicalizing(
            [$hiddencohort->id, $othercategorycohort->id],
            array_keys(block_cohortspecifichtml_get_unselectable_configedcohorts($block))
        );

        // As soon as the hidden cohort is not configured anymore, it is not offered anymore.
        $block->config->cohorts = [$visiblecohort->id, $othercategorycohort->id];
        $this->assertEquals(
            [$othercategorycohort->id],
            array_keys(block_cohortspecifichtml_get_unselectable_configedcohorts($block))
        );
    }

    /**
     * Get the record of the most recently created block instance.
     *
     * @return \stdClass
     */
    protected function get_last_block_record(): \stdClass {
        global $DB;

        $records = $DB->get_records('block_instances', ['blockname' => 'cohortspecifichtml'], 'id DESC', '*', 0, 1);
        return reset($records);
    }
}
