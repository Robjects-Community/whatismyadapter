<?php
$lines = explode("\n", $message);
$output = '';
foreach ($lines as $line) {
    if (strpos($line, 'Stack Trace') !== false) {
        $output .= "<strong>$line</strong><br>";
    } elseif (preg_match('/^#\d+/', $line)) {
        $output .= "<code>$line</code><br>";
    } else {
        $output .= "$line<br>";
    }
}
echo $output;
?>