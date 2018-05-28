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
 * Specialised restore task for the cohortspecifichtml block
 * @package    block_cohortspecifichtml
 * @subpackage backup-moodle2
 * @copyright  2018 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *             based on code from 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Specialised restore task for the cohortspecifichtml block (requires encode_content_links in some configdata attrs)
 * @package    block_cohortspecifichtml
 * @subpackage backup-moodle2
 * @copyright  2018 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *             based on code from 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_cohortspecifichtml_block_task extends restore_block_task {

    /**
     * Define (add) particular settings this block can have.
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
        return array('content');
    }

    /**
     * Define special handling of configdata.
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return array('text'); // We need to encode some attrs in configdata.
    }

    /**
     * Return the contents of this block to be processed by the content decoder.
     *
     * @return array
     */
    static public function define_decode_contents() {

        $contents = array();

        $contents[] = new restore_cohortspecifichtml_block_decode_content('block_instances', 'configdata', 'block_instance');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging to the block to be executed by the link decoder.
     *
     * @return array
     */
    static public function define_decode_rules() {
        return array();
    }
}

/* Specialised restore_decode_content provider that unserializes the configdata
 * field, to serve the configdata->text content to the restore_decode_processor
 * packaging it back to its serialized form after process
 */

/**
 * Class restore_cohortspecifichtml_block_decode_content
 * @copyright  2018 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *             based on code from 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_cohortspecifichtml_block_decode_content extends restore_decode_content {

    /**
     * @var object $configdata
     */
    protected $configdata; // Temp storage for unserialized configdata.

    /**
     * For sql or table datasources, this will iterate over the $DB connection.
     *
     * @return moodle_recordset A moodle_recordset instance.
     */
    protected function get_iterator() {
        global $DB;

        // Build the SQL dynamically here.
        $fieldslist = 't.' . implode(', t.', $this->fields);
        $sql = "SELECT t.id, $fieldslist
                  FROM {" . $this->tablename . "} t
                  JOIN {backup_ids_temp} b ON b.newitemid = t.id
                 WHERE b.backupid = ?
                   AND b.itemname = ?
                   AND t.blockname = 'cohortspecifichtml'";
        $params = array($this->restoreid, $this->mapping);
        return ($DB->get_recordset_sql($sql, $params));
    }

    /**
     * Return a readable string from a Base64 encoded database field.
     * @param string $field
     *
     * @return string
     */
    protected function preprocess_field($field) {
        $this->configdata = unserialize(base64_decode($field));
        return isset($this->configdata->text) ? $this->configdata->text : '';
    }

    /**
     * Return the Base64 encoded config equivalent.
     * @param string $field
     *
     * @return string
     */
    protected function postprocess_field($field) {
        $this->configdata->text = $field;
        return base64_encode(serialize($this->configdata));
    }
}
