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
 * HTML (on cohorts) - Edit form
 *
 * @package   block_cohortspecifichtml
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *            based on code from 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Form for editing HTML block instances.
 *
 * @copyright 2017 Kathrin Osswald based on code from Tim Hunt 2009
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cohortspecifichtml_edit_form extends block_edit_form {

    /**
     * Create any form fields specific to this type of block.
     * @param object $mform
     *
     * @throws coding_exception
     */
    protected function specific_definition($mform) {
        global $CFG;

        require_once($CFG->dirroot . '/blocks/cohortspecifichtml/locallib.php');

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Block title.
        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_html'));
        $mform->setType('config_title', PARAM_TEXT);

        // Text area.
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_html'),
                null, $editoroptions);
        $mform->addRule('config_text', null, 'required', null, 'client');
        $mform->setType('config_text', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        // Cohort selection.
        // Get all cohorts.
        $allcohorts = block_cohortspecifichtml_get_all_cohorts();
        if (!empty($allcohorts)) {
            // Transform object to array.
            foreach ($allcohorts as $c) {
                $options[$c->id] = format_string($c->name);
            }
            $mform->addElement('select', 'config_cohorts', get_string('cohortselection', 'block_cohortspecifichtml'), $options);
            // Enable multi selection.
            $mform->getElement('config_cohorts')->setMultiple(true);
            $mform->addHelpButton('config_cohorts', 'cohortselection', 'block_cohortspecifichtml');
            $mform->addElement('advcheckbox', 'config_invertcohortselection',
                    get_string('invertcohortselection', 'block_cohortspecifichtml'), '', null, array(0, 1));
            $mform->setType('config_invertcohortselection', PARAM_BOOL);
            $mform->addHelpButton('config_invertcohortselection', 'invertcohortselection', 'block_cohortspecifichtml');
            // Only show the setting to reset a cohort selection if there are cohorts selected.
            if (!empty($this->block->config->cohorts)) {
                $mform->addElement('advcheckbox', 'config_resetcohortselection',
                        get_string('resetcohortselection', 'block_cohortspecifichtml'), '', null, array(0, 1));
                $mform->addHelpButton('config_resetcohortselection', 'resetcohortselection', 'block_cohortspecifichtml');
            }
        } else {
            // Add a static element with a hint that there are no cohorts existing.
            $mform->addElement('static', 'nocohorts', get_string('cohorts', 'core_cohort'),
                    get_string('nocohorts', 'block_cohortspecifichtml', array('url' => $CFG->wwwroot.'/cohort/index.php')));
        }

        if (!empty($CFG->block_cohortspecifichtml_allowcssclasses)) {
            $mform->addElement('text', 'config_classes', get_string('configclasses', 'block_html'));
            $mform->setType('config_classes', PARAM_TEXT);
            $mform->addHelpButton('config_classes', 'configclasses', 'block_html');
        }
    }

    /**
     * Function set_data
     * @param array|stdClass $defaults
     *
     * @throws dml_exception
     */
    public function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->text;
            $draftideditor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
                    'block_cohortspecifichtml', 'content', 0, array('subdirs' => true), $currenttext);
            $defaults->config_text['itemid'] = $draftideditor;
            $defaults->config_text['format'] = $this->block->config->format;
        } else {
            $text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // Have to delete text here, otherwise parent::set_data will empty content of editor.
        unset($this->block->config->text);
        parent::set_data($defaults);
        // Restore $text.
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        $this->block->config->text = $text;
        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }
}
