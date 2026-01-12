<?php
// Simple script to check if demo data has been imported
require_once('functions.php');

// Check if demo data has been imported
if (oc_is_demo_data_imported()) {
    echo "Demo data has been imported.\n";
    echo "Import date: " . oc_get_demo_data_import_date() . "\n";

    // Check if we have some demo data
    $operators = get_posts(array('post_type' => 'operator', 'posts_per_page' => 5));
    echo "Found " . count($operators) . " operators.\n";

    $matches = get_posts(array('post_type' => 'match', 'posts_per_page' => 5));
    echo "Found " . count($matches) . " matches.\n";

    $sports = get_terms(array('taxonomy' => 'sport', 'hide_empty' => false));
    echo "Found " . count($sports) . " sports.\n";

    $teams = get_terms(array('taxonomy' => 'team', 'hide_empty' => false));
    echo "Found " . count($teams) . " teams.\n";

    $leagues = get_terms(array('taxonomy' => 'league', 'hide_empty' => false));
    echo "Found " . count($leagues) . " leagues.\n";
} else {
    echo "Demo data has not been imported yet.\n";
    echo "Attempting to import demo data...\n";

    $result = oc_import_demo_data();

    if ($result['success']) {
        echo "Demo data imported successfully!\n";
        echo "Imported: " . json_encode($result['imported']) . "\n";
    } else {
        echo "Failed to import demo data.\n";
        echo "Errors: " . json_encode($result['errors']) . "\n";
    }
}
?>
