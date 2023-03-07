<?php

namespace availability_adler;

use core_availability\frontend as availability_frontend;

class frontend extends availability_frontend {
    protected function get_javascript_strings() {
        // You can return a list of names within your language file and the
        // system will include them here.
        // Should you need strings from another language file, you can also
        // call $PAGE->requires->strings_for_js manually from here.)
        return [];
    }

    protected function allow_add(
        $course,
        \cm_info $cm = null,
        \section_info $section = null
    ) {
        // This function lets you control whether the 'add' button for your
        // plugin appears. For example, the grouping plugin does not appear
        // if there are no groupings on the course. This helps to simplify
        // the user interface. If you don't include this function, it will
        // appear.
        return false;
    }
}
