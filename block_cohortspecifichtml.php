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
 * Form for editing HTML block (on cohorts) instances.
 *
 * @package   block_cohortspecifichtml
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Form for editing HTML block (on cohorts) instances.
 * @package   block_cohortspecifichtml
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cohortspecifichtml extends block_base {

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_cohortspecifichtml');
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Core function, specifies where the block can be used.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Special method acting on instance data just after it's loaded to set block title.
     * @throws coding_exception
     */
    public function specialization() {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/cohortspecifichtml/locallib.php');

        // If a block title is set.
        if (isset($this->config->title)) {
            // Show this title to all users that should see the block or who are allowed to edit the block and are in editing mode.
            if (block_cohortspecifichtml_show_block($this) ||
                    block_cohortspecifichtml_get_caneditandediton($this)) {
                $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
            } else {
                // Do not show a title.
                $this->title = '';
            }
        } else {
            if (block_cohortspecifichtml_show_block($this) ||
                block_cohortspecifichtml_get_caneditandediton($this)) {
                // Show the default title.
                $this->title = get_string('newhtmlcohortblock', 'block_cohortspecifichtml');
            } else {
                // Do not show a title.
                $this->title = '';
            }
        }
    }

    /**
     * Allows the block to be added multiple times to a single page
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Used to generate the content for the block.
     *
     * @return string
     */
    public function get_content() {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot . '/cohort/lib.php');
        require_once($CFG->dirroot . '/blocks/cohortspecifichtml/locallib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // Fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        if (isset($this->config->text)) {

            // Show the block to the users that should see the block.
            if (block_cohortspecifichtml_show_block($this) == true ||
                block_cohortspecifichtml_get_caneditandediton($this) == true) {

                // Rewrite url.
                $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id,
                    'block_cohortspecifichtml', 'content', null);
                // Default to FORMAT_HTML which is what will have been used before the editor was properly
                // implemented for the block.
                $format = FORMAT_HTML;
                // Check to see if the format has been properly set on the config.
                if (isset($this->config->format)) {
                    $format = $this->config->format;
                }

                $info = block_cohortspecifichtml_get_restrictioninfo($this);

                $this->content->text = format_text($info . $this->config->text, $format, $filteropt);
            }
        } else { // No text is entered, set an empty string.
            $this->content->text = '';
        }

        unset($filteropt); // Memory footprint.

        return $this->content;
    }

    /**
     * Return an object containing all the block content to be returned by external functions.
     *
     * @param  core_renderer $output the rendered used for output
     * @return stdClass      object containing the block title, central content, footer and linked files (if any).
     * @since  Moodle 3.6
     */
    public function get_content_for_external($output) {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot . '/cohort/lib.php');
        require_once($CFG->dirroot . '/blocks/cohortspecifichtml/locallib.php');

        $bc = new stdClass;
        $bc->title = null;
        $bc->content = '';
        $bc->contenformat = FORMAT_MOODLE;
        $bc->footer = '';
        $bc->files = [];

        // Show the block to the users that should see the block.
        if (block_cohortspecifichtml_show_block($this) == true ||
            block_cohortspecifichtml_get_caneditandediton($this) == true) {

            if (!$this->hide_header()) {
                $bc->title = $this->title;
            }

            if (isset($this->config->text)) {
                $filteropt = new stdClass;
                if ($this->content_is_trusted()) {
                    // Fancy html allowed only on course, category and system blocks.
                    $filteropt->noclean = true;
                }

                $format = FORMAT_HTML;
                // Check to see if the format has been properly set on the config.
                if (isset($this->config->format)) {
                    $format = $this->config->format;
                }
                list($bc->content, $bc->contentformat) =
                    external_format_text($this->config->text, $format, $this->context, 'block_cohortspecifichtml',
                        'content', null, $filteropt);
                $bc->files = external_util::get_area_files($this->context->id, 'block_cohortspecifichtml',
                    'content', false, false);
            }
        }
        return $bc;
    }

    /**
     * Serialize and store config data
     * @param object $data
     * @param bool $nolongerused
     */
    public function instance_config_save($data, $nolongerused = false) {
        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match.
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_cohortspecifichtml',
            'content', 0, array('subdirs' => true), $data->text['text']);
        $config->format = $data->text['format'];

        // We need this, as empty form selections (unselect all cohorts) won't be passed as a value to the server and therefore
        // we could not reset the selection. See also MDL-61334
        // If setting to reset the selection is enabled.
        if (!empty($config->resetcohortselection) && $config->resetcohortselection != 0) {
            // Replace the saved config with an empty array.
            $config->cohorts = array();
            // Reset the setting again.
            $config->resetcohortselection = 0;
        }

        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Delete a block, and associated data.
     *
     * @return bool
     */
    public function instance_delete() {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_cohortspecifichtml');
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     *
     * @return boolean
     */
    public function instance_copy($fromid) {
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();
        // This extra check if file area is empty adds one query if it is not empty but saves several if it is.
        if (!$fs->is_area_empty($fromcontext->id, 'block_cohortspecifichtml', 'content', 0, false)) {
            $draftitemid = 0;
            file_prepare_draft_area($draftitemid, $fromcontext->id, 'block_cohortspecifichtml',
                'content', 0, array('subdirs' => true));
            file_save_draft_area_files($draftitemid, $this->context->id, 'block_cohortspecifichtml',
                'content', 0, array('subdirs' => true));
        }
        return true;
    }

    /**
     * content_is_trusted method
     *
     * @return bool
     * @throws coding_exception
     */
    public function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // This is exception - page is completely private, nobody else may see content there ...
                // ... that is why we allow JS here.
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }

    /**
     * Add custom html attributes to aid with theming and styling
     *
     * @return array
     */
    public function html_attributes() {
        global $CFG;

        $attributes = parent::html_attributes();

        if (!empty($CFG->block_cohortspecifichtml_allowcssclasses)) {
            if (!empty($this->config->classes)) {
                $attributes['class'] .= ' '.$this->config->classes;
            }
        }

        return $attributes;
    }
}
