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
 * Specialised backup task for the html block
 * @package    block_cohortspecifichtml
 * @subpackage backup-moodle2
 * @copyright  2018 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *             based on code from 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised backup task for the html block (requires encode_content_links in some configdata attrs)
 * @package    block_cohortspecifichtml
 * @subpackage backup-moodle2
 * @copyright  2018 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *             based on code from 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_cohortspecifichtml_block_task extends backup_block_task {

    /**
     * Define (add) particular settings this block can have
     */
    protected function define_my_settings() {
    }

    /**
     * Define backup steps.
     */
    protected function define_my_steps() {
    }

    /**
     * Define the associated file areas.
     *
     * @return array
     */
    public function get_fileareas() {
        return ['content'];
    }

    /**
     * Define special handling of configdata.
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return ['text']; // We need to encode some attrs in configdata.
    }

    /**
     * Content encoding.
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the same content with no changes
     */
    public static function encode_content_links($content) {
        return $content; // No special encoding of links.
    }
}

