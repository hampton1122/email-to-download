<?php
require_once(ABSPATH . 'wp-load.php');

global $wpdb;
$table_name = $wpdb->prefix.$table;

if(isset($_GET['action']) && $_GET['action'] == 'export')
{
        $csv = generate_csv($table_name);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"report.csv\";" );
        header("Content-Transfer-Encoding: binary");

        echo $csv;
        exit;
}

function generate_csv($table)
{
    global $wpdb;
    $csv_output = '';

    $result = $wpdb->get_results("SHOW COLUMNS FROM ".$table);

    $i = 0;
    if (count($result) > 0) {
        foreach($result as $row)
        {
            $csv_output = $csv_output . $row->Field.",";
            $i++;
        }
    }
    $csv_output .= "\n";

    $values = $wpdb->get_results("SELECT * FROM ".$table_name);
    foreach($values as $rowr)
    {
        for ($j=0;$j<$i;$j++) {
            $csv_output .= $rowr->$j.",";
        }
        $csv_output .= "\n";
    }

    return $csv_output;
}