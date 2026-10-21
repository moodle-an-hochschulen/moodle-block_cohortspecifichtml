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
 * Text (on cohorts) - Behat steps
 *
 * @package    block_cohortspecifichtml
 * @category   test
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Text (on cohorts) - Behat steps
 *
 * @package    block_cohortspecifichtml
 * @category   test
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_cohortspecifichtml extends behat_base {
    /**
     * Checks that a file of a Text (on cohorts) block is served to the current user if it is requested directly.
     *
     * @Then /^the file "((?:[^"]|\\")*)" of the "((?:[^"]|\\")*)" Text \(on cohorts\) block should be served$/
     * @param string $filename The name of the file in the block's content filearea.
     * @param string $blocktitle The title of the block.
     * @throws ExpectationException
     */
    public function the_file_of_the_block_should_be_served($filename, $blocktitle) {
        if (!$this->request_block_file($filename, $blocktitle)) {
            throw new ExpectationException(
                'The file "' . $filename . '" of the "' . $blocktitle . '" block was not served',
                $this->getSession()
            );
        }
    }

    /**
     * Checks that a file of a Text (on cohorts) block is not served to the current user if it is requested directly.
     *
     * @Then /^the file "((?:[^"]|\\")*)" of the "((?:[^"]|\\")*)" Text \(on cohorts\) block should not be served$/
     * @param string $filename The name of the file in the block's content filearea.
     * @param string $blocktitle The title of the block.
     * @throws ExpectationException
     */
    public function the_file_of_the_block_should_not_be_served($filename, $blocktitle) {
        if ($this->request_block_file($filename, $blocktitle)) {
            throw new ExpectationException(
                'The file "' . $filename . '" of the "' . $blocktitle . '" block was served',
                $this->getSession()
            );
        }
    }

    /**
     * Requests a file of a Text (on cohorts) block directly with the session of the current user.
     *
     * @param string $filename The name of the file in the block's content filearea.
     * @param string $blocktitle The title of the block.
     * @return bool True if the file was served with its content, false otherwise.
     * @throws ExpectationException
     */
    protected function request_block_file($filename, $blocktitle) {
        global $DB;

        // Find the block instance by its title.
        $blockcontext = null;
        foreach ($DB->get_records('block_instances', ['blockname' => 'cohortspecifichtml']) as $blockinstance) {
            $config = unserialize_object(base64_decode($blockinstance->configdata ?? ''));
            if (!empty($config->title) && $config->title == $blocktitle) {
                $blockcontext = context_block::instance($blockinstance->id);
                break;
            }
        }
        if ($blockcontext === null) {
            throw new ExpectationException('The "' . $blocktitle . '" block does not exist', $this->getSession());
        }

        // Get the file from the block's content filearea.
        $file = get_file_storage()->get_file($blockcontext->id, 'block_cohortspecifichtml', 'content', 0, '/', $filename);
        if ($file === false) {
            throw new ExpectationException(
                'The file "' . $filename . '" does not exist in the "' . $blocktitle . '" block',
                $this->getSession()
            );
        }

        // Request the file with the session of the current user.
        $url = $this->locate_path('/pluginfile.php/' . $blockcontext->id . '/block_cohortspecifichtml/content/' .
            rawurlencode($filename));
        $session = $this->getSession()->getCookie('MoodleSession');
        $headers = !empty($session) ? ['Cookie' => 'MoodleSession=' . $session] : null;
        $response = download_file_content($url, $headers, null, true);

        // The file was only served if it was delivered with its content (and not, for example, the login page).
        return $response->status == '200' && $response->results === $file->get_content();
    }
}
