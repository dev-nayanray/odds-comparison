<?php
/**
 * Demo Data Setup Script
 *
 * Creates sample data for testing the Odds Comparison theme
 * This script simulates the demo data import without requiring WordPress
 */

// Sample data arrays
$demo_data = array(
    'sports' => array(
        array('id' => 1, 'name' => 'Football', 'slug' => 'football'),
        array('id' => 2, 'name' => 'Basketball', 'slug' => 'basketball'),
        array('id' => 3, 'name' => 'Tennis', 'slug' => 'tennis'),
    ),
    'leagues' => array(
        array('id' => 1, 'name' => 'Premier League', 'slug' => 'premier-league', 'sport_id' => 1),
        array('id' => 2, 'name' => 'La Liga', 'slug' => 'la-liga', 'sport_id' => 1),
        array('id' => 3, 'name' => 'NBA', 'slug' => 'nba', 'sport_id' => 2),
        array('id' => 4, 'name' => 'UEFA Champions League', 'slug' => 'champions-league', 'sport_id' => 1),
    ),
    'teams' => array(
        array('id' => 1, 'name' => 'Manchester City', 'slug' => 'man-city', 'short_name' => 'MCI'),
        array('id' => 2, 'name' => 'Liverpool', 'slug' => 'liverpool', 'short_name' => 'LIV'),
        array('id' => 3, 'name' => 'Arsenal', 'slug' => 'arsenal', 'short_name' => 'ARS'),
        array('id' => 4, 'name' => 'Real Madrid', 'slug' => 'real-madrid', 'short_name' => 'RMA'),
        array('id' => 5, 'name' => 'Barcelona', 'slug' => 'barcelona', 'short_name' => 'FCB'),
        array('id' => 6, 'name' => 'Bayern Munich', 'slug' => 'bayern-munich', 'short_name' => 'BAY'),
        array('id' => 7, 'name' => 'PSG', 'slug' => 'psg', 'short_name' => 'PSG'),
    ),
    'operators' => array(
        array('id' => 1, 'name' => 'Bet365', 'rating' => 4.8, 'bonus' => '100% up to €100', 'affiliate_url' => 'https://www.bet365.com/'),
        array('id' => 2, 'name' => 'Betway', 'rating' => 4.5, 'bonus' => '100% up to €200', 'affiliate_url' => 'https://www.betway.com/'),
        array('id' => 3, 'name' => '888sport', 'rating' => 4.3, 'bonus' => '100% up to €150', 'affiliate_url' => 'https://www.888sport.com/'),
        array('id' => 4, 'name' => 'Unibet', 'rating' => 4.4, 'bonus' => '€30 Free Bet', 'affiliate_url' => 'https://www.unibet.com/'),
        array('id' => 5, 'name' => 'Pinnacle', 'rating' => 4.6, 'bonus' => 'Welcome Bonus', 'affiliate_url' => 'https://www.pinnacle.com/'),
        array('id' => 6, 'name' => 'Bwin', 'rating' => 4.2, 'bonus' => '100% up to €100', 'affiliate_url' => 'https://www.bwin.com/'),
    ),
    'matches' => array(
        array(
            'id' => 1,
            'title' => 'Manchester City vs Liverpool',
            'home_team_id' => 1,
            'away_team_id' => 2,
            'league_id' => 1,
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '20:00',
            'is_live' => false,
            'is_featured' => true,
        ),
        array(
            'id' => 2,
            'title' => 'Arsenal vs Real Madrid',
            'home_team_id' => 3,
            'away_team_id' => 4,
            'league_id' => 4,
            'date' => date('Y-m-d', strtotime('+2 days')),
            'time' => '21:00',
            'is_live' => false,
            'is_featured' => false,
        ),
        array(
            'id' => 3,
            'title' => 'Barcelona vs Bayern Munich',
            'home_team_id' => 5,
            'away_team_id' => 6,
            'league_id' => 4,
            'date' => date('Y-m-d', strtotime('+3 days')),
            'time' => '20:45',
            'is_live' => false,
            'is_featured' => true,
        ),
        array(
            'id' => 4,
            'title' => 'PSG vs Manchester City',
            'home_team_id' => 7,
            'away_team_id' => 1,
            'league_id' => 4,
            'date' => date('Y-m-d', strtotime('+5 days')),
            'time' => '20:00',
            'is_live' => false,
            'is_featured' => false,
        ),
    ),
);

// Generate odds data
$odds_data = array();
foreach ($demo_data['matches'] as $match) {
    foreach ($demo_data['operators'] as $operator) {
        $odds_data[] = array(
            'match_id' => $match['id'],
            'bookmaker_id' => $operator['id'],
            'odds_home' => number_format(mt_rand(150, 350) / 100, 2),
            'odds_draw' => number_format(mt_rand(250, 450) / 100, 2),
            'odds_away' => number_format(mt_rand(150, 350) / 100, 2),
            'last_updated' => date('Y-m-d H:i:s'),
        );
    }
}

echo "Demo data structure created successfully!\n";
echo "Sports: " . count($demo_data['sports']) . "\n";
echo "Leagues: " . count($demo_data['leagues']) . "\n";
echo "Teams: " . count($demo_data['teams']) . "\n";
echo "Operators: " . count($demo_data['operators']) . "\n";
echo "Matches: " . count($demo_data['matches']) . "\n";
echo "Odds entries: " . count($odds_data) . "\n\n";

// Save to JSON file for reference
file_put_contents('demo_data.json', json_encode($demo_data, JSON_PRETTY_PRINT));
file_put_contents('demo_odds.json', json_encode($odds_data, JSON_PRETTY_PRINT));

echo "Demo data saved to demo_data.json and demo_odds.json\n";
echo "You can now use this data to populate your WordPress database when the theme is activated.\n";
?>
