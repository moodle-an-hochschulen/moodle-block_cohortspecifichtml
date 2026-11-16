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

/**
 * Text (on cohorts) - Unit tests for the backup and restore of the configured cohorts.
 *
 * @package   block_cohortspecifichtml
 * @copyright 2026 Alexander Bias <bias@alexanderbias.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \restore_cohortspecifichtml_block_task::after_restore
 */
final class restore_test extends \advanced_testcase {
    /** @var string[] The warnings which were added to the log of the last restore */
    protected $restorewarnings = [];

    /**
     * Load required libraries.
     */
    public function setUp(): void {
        global $CFG;

        $this->resetAfterTest();

        // We need the backup and cohort libraries.
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        require_once($CFG->dirroot . '/cohort/lib.php');

        parent::setUp();
    }

    /**
     * Data provider which runs each test once with a normal and once with an inverted cohort selection.
     *
     * @return array[]
     */
    public static function invert_provider(): array {
        return [
            'Selection not inverted' => [0],
            'Selection inverted' => [1],
        ];
    }

    /**
     * Test that the configured cohorts are kept when restoring on the same site.
     *
     * @dataProvider invert_provider
     * @param int $invert Whether the cohort selection is inverted.
     */
    public function test_cohorts_are_kept_on_same_site(int $invert): void {
        $this->setAdminUser();

        $cohort = $this->getDataGenerator()->create_cohort();
        $course = $this->create_course_with_block([$cohort->id], $invert);

        $config = $this->backup_and_restore($course);

        $this->assertEquals([$cohort->id], $config->cohorts);
        $this->assertEquals($invert, $config->invertcohortselection);
        $this->assertEquals('Block text', $config->text);
        $this->assertEquals(0, $this->count_restore_warnings('the cohort selection was removed'));
    }

    /**
     * Test that a hidden cohort in an unrelated category is kept when a teacher
     * who is not allowed to select this cohort restores the course on the same site.
     *
     * @dataProvider invert_provider
     * @param int $invert Whether the cohort selection is inverted.
     */
    public function test_hidden_cohort_is_kept_when_teacher_restores(int $invert): void {
        $generator = $this->getDataGenerator();

        $category = $generator->create_category();
        $cohort = $generator->create_cohort([
            'contextid' => \context_coursecat::instance($category->id)->id,
            'visible' => 0,
        ]);
        $course = $this->create_course_with_block([$cohort->id], $invert);
        $targetcourse = $generator->create_course();

        $teacher = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $generator->enrol_user($teacher->id, $targetcourse->id, 'editingteacher');
        $this->setUser($teacher);

        $config = $this->backup_and_restore($course, false, null, $targetcourse);

        $this->assertEquals([$cohort->id], $config->cohorts);
        $this->assertEquals($invert, $config->invertcohortselection);
        $this->assertEquals(0, $this->count_restore_warnings('the cohort selection was removed'));
    }

    /**
     * Test that a cohort which was deleted between backup and restore is kept when restoring on the same site.
     *
     * @dataProvider invert_provider
     * @param int $invert Whether the cohort selection is inverted.
     */
    public function test_deleted_cohort_is_kept_on_same_site(int $invert): void {
        $this->setAdminUser();

        $cohort = $this->getDataGenerator()->create_cohort();
        $course = $this->create_course_with_block([$cohort->id], $invert);

        $config = $this->backup_and_restore($course, false, function () use ($cohort) {
            cohort_delete_cohort($cohort);
        });

        $this->assertEquals([$cohort->id], $config->cohorts);
        $this->assertEquals($invert, $config->invertcohortselection);
        $this->assertEquals(0, $this->count_restore_warnings('the cohort selection was removed'));
    }

    /**
     * Test that the cohort selection and its inversion are removed when restoring on another site.
     *
     * @dataProvider invert_provider
     * @param int $invert Whether the cohort selection is inverted.
     */
    public function test_cohorts_are_removed_on_other_site(int $invert): void {
        $this->setAdminUser();

        // The cohort exists on this site, but must not be used as the backup is treated as coming from another site.
        $cohort = $this->getDataGenerator()->create_cohort();
        $course = $this->create_course_with_block([$cohort->id], $invert);

        $config = $this->backup_and_restore($course, true);

        // The block is not shown to anybody anymore, the rest of the configuration is kept and a warning is logged.
        $this->assertEquals([], $config->cohorts);
        $this->assertEquals(0, $config->invertcohortselection);
        $this->assertEquals('Block text', $config->text);
        $this->assertEquals(1, $this->count_restore_warnings('the cohort selection was removed'));
    }

    /**
     * Test that a block without any configured cohorts is not changed when restoring on another site.
     *
     * @dataProvider invert_provider
     * @param int $invert Whether the cohort selection is inverted.
     */
    public function test_block_without_cohorts_is_kept_on_other_site(int $invert): void {
        $this->setAdminUser();

        $course = $this->create_course_with_block([], $invert);

        $config = $this->backup_and_restore($course, true);

        $this->assertEquals([], $config->cohorts);
        $this->assertEquals($invert, $config->invertcohortselection);
        $this->assertEquals(0, $this->count_restore_warnings('the cohort selection was removed'));
    }

    /**
     * Create a course with a Text (on cohorts) block.
     *
     * @param array $cohortids The ids of the configured cohorts.
     * @param int $invert Whether the cohort selection is inverted.
     * @return \stdClass The course.
     */
    protected function create_course_with_block(array $cohortids, int $invert): \stdClass {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $config = (object) [
            'title' => 'Block title',
            'text' => 'Block text',
            'format' => FORMAT_HTML,
            'cohorts' => $cohortids,
            'invertcohortselection' => $invert,
        ];
        $generator->create_block('cohortspecifichtml', [
            'parentcontextid' => \context_course::instance($course->id)->id,
            'pagetypepattern' => 'course-view-*',
            'configdata' => base64_encode(serialize($config)),
        ]);
        return $course;
    }

    /**
     * Back up the given course, restore it into another course and return the configuration of the restored block.
     *
     * @param \stdClass $course The course to back up.
     * @param bool $othersite Whether the backup should be restored as if it came from another site.
     * @param callable|null $beforerestore Callback which is executed between backup and restore.
     * @param \stdClass|null $targetcourse The course to restore into (a new course is created if not given).
     * @return \stdClass The configuration of the restored block.
     */
    protected function backup_and_restore(
        \stdClass $course,
        bool $othersite = false,
        ?callable $beforerestore = null,
        ?\stdClass $targetcourse = null
    ): \stdClass {
        global $DB, $USER;

        if ($targetcourse === null) {
            $targetcourse = $this->getDataGenerator()->create_course();
        }

        // Back up the course.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        if ($beforerestore !== null) {
            $beforerestore();
        }

        // Simulate another site by changing the site identifier which the backup is compared against.
        if ($othersite) {
            set_config('siteidentifier', random_string(32) . 'othersite');
        }

        // Restore the course.
        $rc = new \restore_controller(
            $backupid,
            $targetcourse->id,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id,
            \backup::TARGET_EXISTING_ADDING
        );
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $restoreid = $rc->get_restoreid();
        $rc->destroy();

        // Remember the warnings of the restore log.
        $this->restorewarnings = $DB->get_fieldset(
            'backup_logs',
            'message',
            ['backupid' => $restoreid, 'loglevel' => \backup::LOG_WARNING]
        );

        // Get the configuration of the restored block.
        $configdata = $DB->get_field(
            'block_instances',
            'configdata',
            [
                'blockname' => 'cohortspecifichtml',
                'parentcontextid' => \context_course::instance($targetcourse->id)->id,
            ],
            MUST_EXIST
        );
        return unserialize_object(base64_decode($configdata));
    }

    /**
     * Count the warnings of the last restore log which contain the given text.
     *
     * @param string $text The text to look for.
     * @return int The number of matching warnings.
     */
    protected function count_restore_warnings(string $text): int {
        return count(array_filter($this->restorewarnings, fn($message) => str_contains($message, $text)));
    }
}
