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
 * HTML (on cohorts) - Mobile provider
 *
 * @package    block_cohortspecifichtml
 * @copyright  2021 Andrew Hancox <andrewdchancox@googlemail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_cohortspecifichtml\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Mobile output functions.
 */
class mobile {

    /**
     * Returns the SC document view page for the mobile app.
     *
     * @param array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and otherdata
     */
    public static function mobile_block_view(array $args) : array {
        global $CFG, $DB, $PAGE, $OUTPUT;

        require_once("$CFG->dirroot/lib/blocklib.php");
        require_once("$CFG->dirroot/lib/filelib.php");
        $block = block_instance('cohortspecifichtml',
                $DB->get_record('block_instances', ['id' => $args['blockid']]),
                $PAGE
        );

        $data = new \stdClass();
        if (block_cohortspecifichtml_show_block($block) && $block->config->text) {
            $blockcontext = \context_block::instance($args['blockid']);
            $contenttext = file_rewrite_pluginfile_urls($block->config->text, 'pluginfile.php', $blockcontext->id,
                    'block_cohortspecifichtml', 'content', null);
            // Default to FORMAT_HTML which is what will have been used before the editor was properly
            // implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($block->config->format)) {
                $format = $block->config->format;
            }

            $filteropt = new \stdClass;
            $filteropt->overflowdiv = true;
            if ($block->content_is_trusted()) {
                // Fancy html allowed only on course, category and system blocks.
                $filteropt->noclean = true;
            }

            $data->content = format_text($contenttext, $format, $filteropt);
        }
        $data->blockid = $block->$args['blockid'];
        $data->title = $block->title;
        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('block_cohortspecifichtml/mobile_block_view', $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => [],
            'files' => []
        ];
    }
}
