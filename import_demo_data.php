<?php
/**
 * Demo Data Import Script
 *
 * This script imports sample data for the Odds Comparison theme
 * including sports, leagues, teams, operators, matches, and odds.
 */

// Include WordPress
define('WP_USE_THEMES', false);
require_once('wp-load.php');

// Include theme functions
require_once('functions.php');
require_once('inc/demo-data.php');

echo "Starting demo data import...\n";

// Create database tables first
echo "Creating database tables...\n";
oc_create_database_tables();

// Import demo data
echo "Importing demo data...\n";
$result = oc_import_demo_data();

if ($result['success']) {
    echo "Demo data imported successfully!\n";
    echo "Imported data:\n";

    foreach ($result['imported'] as $type => $count) {
        echo "- $type: $count\n";
    }

    // Verify the data
    echo "\nVerifying imported data:\n";

    $operators = get_posts(array('post_type' => 'operator', 'posts_per_page' => -1));
    echo "- Operators: " . count($operators) . "\n";

    $matches = get_posts(array('post_type' => 'match', 'posts_per_page' => -1));
    echo "- Matches: " . count($matches) . "\n";

    $sports = get_terms(array('taxonomy' => 'sport', 'hide_empty' => false));
    echo "- Sports: " . count($sports) . "\n";

    $leagues = get_terms(array('taxonomy' => 'league', 'hide_empty' => false));
    echo "- Leagues: " . count($leagues) . "\n";

    $teams = get_terms(array('taxonomy' => 'team', 'hide_empty' => false));
    echo "- Teams: " . count($teams) . "\n";

    // Test the functions used on home page
    echo "\nTesting home page functions:\n";

    $live_matches = oc_get_live_matches(5);
    echo "- Live matches: " . count($live_matches) . "\n";

    $grouped_matches = oc_get_grouped_matches();
    $total_grouped = 0;
    foreach ($grouped_matches as $group) {
        if (isset($group['matches'])) {
            $total_grouped += count($group['matches']);
        }
    }
    echo "- Grouped matches: $total_grouped\n";

    $featured_operators = oc_get_featured_operators(10);
    echo "- Featured operators: " . count($featured_operators) . "\n";

    echo "\nDemo data import completed successfully!\n";
    echo "The home page should now display dynamic content.\n";

} else {
    echo "Failed to import demo data.\n";
    echo "Errors:\n";
    foreach ($result['errors'] as $error) {
        echo "- $error\n";
    }
}
?>
