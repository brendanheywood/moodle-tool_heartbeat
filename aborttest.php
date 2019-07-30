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
 * Performs a request abort test
 *
 * @package    tool_heartbeat
 * @copyright  2019 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('NO_OUTPUT_BUFFERING', true); // progress bar is used here

require(__DIR__ . '/../../../config.php');
require_login();

$stage = optional_param('stage', 1,   PARAM_NUMBER);

$syscontext = context_system::instance();
$url = new moodle_url('/admin/tool/heartbeat/aborttest.php');
$PAGE->set_url($url);
$PAGE->set_context($syscontext);
$PAGE->set_cacheable(false);
$url->params(array('stage' => 2));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testabort', 'tool_heartbeat'));

echo get_string('testaborthelp', 'tool_heartbeat');
echo "<h3>Stage: $stage</h3>";

if ($stage == 2) {
    $progress = $SESSION->abortprogress;
    echo "Abort progress was: " .$progress;

    if ($progress > 20 && $progress < 30) {
        echo $OUTPUT->notification('Yay! the request was aborted', \core\output\notification::NOTIFY_SUCCESS);
    } else {
        echo $OUTPUT->notification('Doh! the request was not aborted', \core\output\notification::NOTIFY_ERROR);
    }

    $SESSION->abortprogress = 'stage 2';

    echo "<p><a href='aborttest.php'>Start again</a></p>";

    echo $OUTPUT->footer();
}



if ($stage == 1) {
?>
<p>This should show a moving progress bar, but after 2 seconds the page should reload and it should NOT get to 100%.</p>
<script>
setTimeout(function(){
    window.stop();
    location.href = '<?php echo $url->out() ?>';
},2000);
</script>
<?php

    $progressbar = new progress_bar();
    $progressbar->create();

    echo $OUTPUT->footer();

    // @codingStandardsIgnoreStart
    error_log("Starting stage 1");
    // @codingStandardsIgnoreEnd
    $SESSION->abortprogress = 0;

    $total = 10;
    $progressbar->update_full(0, '0%');
    for ($c = 1; $c <= 100; $c++) {
        usleep($total * 1000000 / 100);
        $progressbar->update_full($c, $c . '%');
        $SESSION->abortprogress = $c;
        if(connection_status() != CONNECTION_NORMAL) {
            // @codingStandardsIgnoreStart
            error_log("Aborting stage 1 at $c %");
            // @codingStandardsIgnoreEnd
        }
    }
    $SESSION->abortprogress = 100;
    // @codingStandardsIgnoreStart
    error_log("End of stage 1 - ERROR we should NOT have gotten here!!");
    // @codingStandardsIgnoreEnd
}


