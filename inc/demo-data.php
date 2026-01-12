<?php
/**
 * Demo Data Import System
 * 
 * Handles importing demo data including matches, operators, teams,
 * leagues, sports, and odds when the theme is first activated.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Main function to import all demo data
 * 
 * @since 1.0.0
 * 
 * @return array Import results with success/failure counts
 */
function oc_import_demo_data() {
    $results = array(
        'success' => true,
        'message' => '',
        'imported' => array(),
        'errors' => array(),
    );
    
    // Create database tables first
    oc_create_database_tables();
    
    // Import taxonomies
    $results['imported']['sports'] = oc_import_demo_sports();
    $results['imported']['leagues'] = oc_import_demo_leagues();
    $results['imported']['teams'] = oc_import_demo_teams();
    
    // Import operators
    $results['imported']['operators'] = oc_import_demo_operators();
    
    // Import matches
    $results['imported']['matches'] = oc_import_demo_matches();
    
    // Import odds
    $results['imported']['odds'] = oc_import_demo_odds();
    
    // Create required pages
    $results['imported']['pages'] = oc_create_required_pages();
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Mark demo data as imported
    update_option( 'oc_demo_data_imported', true );
    update_option( 'oc_demo_data_imported_date', current_time( 'mysql' ) );
    
    return $results;
}

/**
 * Import demo sports
 * 
 * @since 1.0.0
 * 
 * @return int Number of sports imported
 */
function oc_import_demo_sports() {
    $sports = array(
        'football' => array(
            'name' => __( 'Football', 'odds-comparison' ),
            'slug' => 'football',
            'description' => __( 'Soccer/Association Football', 'odds-comparison' ),
        ),
        'basketball' => array(
            'name' => __( 'Basketball', 'odds-comparison' ),
            'slug' => 'basketball',
            'description' => __( 'Basketball matches and games', 'odds-comparison' ),
        ),
        'tennis' => array(
            'name' => __( 'Tennis', 'odds-comparison' ),
            'slug' => 'tennis',
            'description' => __( 'Tennis matches and tournaments', 'odds-comparison' ),
        ),
        'baseball' => array(
            'name' => __( 'Baseball', 'odds-comparison' ),
            'slug' => 'baseball',
            'description' => __( 'Baseball games', 'odds-comparison' ),
        ),
        'hockey' => array(
            'name' => __( 'Ice Hockey', 'odds-comparison' ),
            'slug' => 'ice-hockey',
            'description' => __( 'Ice hockey matches', 'odds-comparison' ),
        ),
    );
    
    $imported = 0;
    
    foreach ( $sports as $sport ) {
        if ( ! term_exists( $sport['slug'], 'sport' ) ) {
            wp_insert_term(
                $sport['name'],
                'sport',
                array(
                    'slug' => $sport['slug'],
                    'description' => $sport['description'],
                )
            );
            $imported++;
        }
    }
    
    return $imported;
}

/**
 * Import demo leagues
 * 
 * @since 1.0.0
 * 
 * @return int Number of leagues imported
 */
function oc_import_demo_leagues() {
    $leagues = array(
        'premier-league' => array(
            'name' => __( 'Premier League', 'odds-comparison' ),
            'slug' => 'premier-league',
            'sport' => 'football',
        ),
        'la-liga' => array(
            'name' => __( 'La Liga', 'odds-comparison' ),
            'slug' => 'la-liga',
            'sport' => 'football',
        ),
        'bundesliga' => array(
            'name' => __( 'Bundesliga', 'odds-comparison' ),
            'slug' => 'bundesliga',
            'sport' => 'football',
        ),
        'serie-a' => array(
            'name' => __( 'Serie A', 'odds-comparison' ),
            'slug' => 'serie-a',
            'sport' => 'football',
        ),
        'ligue-1' => array(
            'name' => __( 'Ligue 1', 'odds-comparison' ),
            'slug' => 'ligue-1',
            'sport' => 'football',
        ),
        'champions-league' => array(
            'name' => __( 'UEFA Champions League', 'odds-comparison' ),
            'slug' => 'champions-league',
            'sport' => 'football',
        ),
        'nba' => array(
            'name' => __( 'NBA', 'odds-comparison' ),
            'slug' => 'nba',
            'sport' => 'basketball',
        ),
    );
    
    $imported = 0;
    
    foreach ( $leagues as $league ) {
        if ( ! term_exists( $league['slug'], 'league' ) ) {
            $sport_term = get_term_by( 'slug', $league['sport'], 'sport' );
            
            wp_insert_term(
                $league['name'],
                'league',
                array(
                    'slug' => $league['slug'],
                )
            );
            
            // Assign to sport
            $league_term = get_term_by( 'slug', $league['slug'], 'league' );
            if ( $league_term && $sport_term ) {
                wp_set_object_terms( $league_term->term_id, $league['sport'], 'sport' );
            }
            
            $imported++;
        }
    }
    
    return $imported;
}

/**
 * Import demo teams
 * 
 * @since 1.0.0
 * 
 * @return int Number of teams imported
 */
function oc_import_demo_teams() {
    $teams = array(
        // Premier League
        'man-city' => array(
            'name' => __( 'Manchester City', 'odds-comparison' ),
            'short_name' => 'MCI',
            'country' => __( 'England', 'odds-comparison' ),
            'stadium' => __( 'Etihad Stadium', 'odds-comparison' ),
        ),
        'liverpool' => array(
            'name' => __( 'Liverpool', 'odds-comparison' ),
            'short_name' => 'LIV',
            'country' => __( 'England', 'odds-comparison' ),
            'stadium' => __( 'Anfield', 'odds-comparison' ),
        ),
        'arsenal' => array(
            'name' => __( 'Arsenal', 'odds-comparison' ),
            'short_name' => 'ARS',
            'country' => __( 'England', 'odds-comparison' ),
            'stadium' => __( 'Emirates Stadium', 'odds-comparison' ),
        ),
        'man-utd' => array(
            'name' => __( 'Manchester United', 'odds-comparison' ),
            'short_name' => 'MUN',
            'country' => __( 'England', 'odds-comparison' ),
            'stadium' => __( 'Old Trafford', 'odds-comparison' ),
        ),
        'chelsea' => array(
            'name' => __( 'Chelsea', 'odds-comparison' ),
            'short_name' => 'CHE',
            'country' => __( 'England', 'odds-comparison' ),
            'stadium' => __( 'Stamford Bridge', 'odds-comparison' ),
        ),
        // La Liga
        'real-madrid' => array(
            'name' => __( 'Real Madrid', 'odds-comparison' ),
            'short_name' => 'RMA',
            'country' => __( 'Spain', 'odds-comparison' ),
            'stadium' => __( 'Santiago Bernabéu', 'odds-comparison' ),
        ),
        'barcelona' => array(
            'name' => __( 'Barcelona', 'odds-comparison' ),
            'short_name' => 'FCB',
            'country' => __( 'Spain', 'odds-comparison' ),
            'stadium' => __( 'Camp Nou', 'odds-comparison' ),
        ),
        'atletico' => array(
            'name' => __( 'Atlético Madrid', 'odds-comparison' ),
            'short_name' => 'ATM',
            'country' => __( 'Spain', 'odds-comparison' ),
            'stadium' => __( 'Wanda Metropolitano', 'odds-comparison' ),
        ),
        // Serie A
        'juventus' => array(
            'name' => __( 'Juventus', 'odds-comparison' ),
            'short_name' => 'JUV',
            'country' => __( 'Italy', 'odds-comparison' ),
            'stadium' => __( 'Allianz Stadium', 'odds-comparison' ),
        ),
        'inter-milan' => array(
            'name' => __( 'Inter Milan', 'odds-comparison' ),
            'short_name' => 'INT',
            'country' => __( 'Italy', 'odds-comparison' ),
            'stadium' => __( 'San Siro', 'odds-comparison' ),
        ),
        // Bundesliga
        'bayern-munich' => array(
            'name' => __( 'Bayern Munich', 'odds-comparison' ),
            'short_name' => 'BAY',
            'country' => __( 'Germany', 'odds-comparison' ),
            'stadium' => __( 'Allianz Arena', 'odds-comparison' ),
        ),
        'dortmund' => array(
            'name' => __( 'Borussia Dortmund', 'odds-comparison' ),
            'short_name' => 'DOR',
            'country' => __( 'Germany', 'odds-comparison' ),
            'stadium' => __( 'Signal Iduna Park', 'odds-comparison' ),
        ),
        // Ligue 1
        'psg' => array(
            'name' => __( 'Paris Saint-Germain', 'odds-comparison' ),
            'short_name' => 'PSG',
            'country' => __( 'France', 'odds-comparison' ),
            'stadium' => __( 'Parc des Princes', 'odds-comparison' ),
        ),
    );
    
    $imported = 0;
    
    foreach ( $teams as $slug => $team ) {
        if ( ! term_exists( $slug, 'team' ) ) {
            $term = wp_insert_term(
                $team['name'],
                'team',
                array(
                    'slug' => $slug,
                )
            );
            
            if ( ! is_wp_error( $term ) ) {
                add_term_meta( $term['term_id'], 'oc_team_short_name', $team['short_name'] );
                add_term_meta( $term['term_id'], 'oc_team_country', $team['country'] );
                add_term_meta( $term['term_id'], 'oc_team_stadium', $team['stadium'] );
                $imported++;
            }
        }
    }
    
    return $imported;
}

/**
 * Import demo operators
 * 
 * @since 1.0.0
 * 
 * @return int Number of operators imported
 */
function oc_import_demo_operators() {
    $operators = array(
        'bet365' => array(
            'title' => 'Bet365',
            'content' => __( 'Bet365 is one of the world\'s leading online gambling companies with over 45 million customers worldwide. They offer competitive odds across all major sports and have an excellent live betting platform.', 'odds-comparison' ),
            'license' => 'Gibraltar',
            'rating' => 4.8,
            'affiliate_url' => 'https://www.bet365.com/',
            'bonus_text' => '100% up to €100',
            'bonus_value' => 100,
            'bonus_type' => 'deposit_match',
            'min_deposit' => '€10',
            'min_bet' => '€1',
            'payment_methods' => 'Visa, Mastercard, PayPal, Skrill, Neteller, Bank Transfer',
        ),
        'betway' => array(
            'title' => 'Betway',
            'content' => __( 'Betway is a global online betting platform offering sports betting, casino games, and esports. They are known for their competitive odds and generous welcome bonuses.', 'odds-comparison' ),
            'license' => 'MGA',
            'rating' => 4.5,
            'affiliate_url' => 'https://www.betway.com/',
            'bonus_text' => '100% up to €200',
            'bonus_value' => 200,
            'bonus_type' => 'deposit_match',
            'min_deposit' => '€10',
            'min_bet' => '€0.10',
            'payment_methods' => 'Visa, Mastercard, PayPal, Skrill, Neteller',
        ),
        '888sport' => array(
            'title' => '888sport',
            'content' => __( '888sport is part of the 888 Holdings group, offering sports betting with competitive odds and a wide range of markets. They are known for their innovative features and promotions.', 'odds-comparison' ),
            'license' => 'Gibraltar',
            'rating' => 4.3,
            'affiliate_url' => 'https://www.888sport.com/',
            'bonus_text' => '100% up to €150',
            'bonus_value' => 150,
            'bonus_type' => 'deposit_match',
            'min_deposit' => '€10',
            'min_bet' => '€1',
            'payment_methods' => 'Visa, Mastercard, PayPal, Skrill, Neteller, Bank Transfer',
        ),
        'unibet' => array(
            'title' => 'Unibet',
            'content' => __( 'Unibet is a trusted name in online betting, offering a comprehensive range of sports markets and competitive odds. They are known for their excellent customer service and live streaming options.', 'odds-comparison' ),
            'license' => 'MGA',
            'rating' => 4.4,
            'affiliate_url' => 'https://www.unibet.com/',
            'bonus_text' => '€30 Free Bet',
            'bonus_value' => 30,
            'bonus_type' => 'free_bet',
            'min_deposit' => '€10',
            'min_bet' => '€5',
            'payment_methods' => 'Visa, Mastercard, PayPal, Skrill, Neteller, Trustly',
        ),
        'pinnacle' => array(
            'title' => 'Pinnacle',
            'content' => __( 'Pinnacle is known for offering the best odds in the industry with low margins. They cater to professional bettors and offer high betting limits.', 'odds-comparison' ),
            'license' => 'Curacao',
            'rating' => 4.6,
            'affiliate_url' => 'https://www.pinnacle.com/',
            'bonus_text' => 'Welcome Bonus',
            'bonus_value' => 0,
            'bonus_type' => 'other',
            'min_deposit' => '€10',
            'min_bet' => '€1',
            'payment_methods' => 'Visa, Mastercard, Skrill, Neteller, Bitcoin',
        ),
        'bwin' => array(
            'title' => 'Bwin',
            'content' => __( 'Bwin is a leading European sports betting brand with a strong presence in football betting. They offer live betting, streaming, and competitive odds across all major sports.', 'odds-comparison' ),
            'license' => 'Gibraltar',
            'rating' => 4.2,
            'affiliate_url' => 'https://www.bwin.com/',
            'bonus_text' => '100% up to €100',
            'bonus_value' => 100,
            'bonus_type' => 'deposit_match',
            'min_deposit' => '€10',
            'min_bet' => '€0.50',
            'payment_methods' => 'Visa, Mastercard, PayPal, Skrill, Neteller, Bank Transfer',
        ),
    );
    
    $imported = 0;
    
    foreach ( $operators as $slug => $operator ) {
        $post_exists = get_page_by_path( $slug, OBJECT, 'operator' );
        
        if ( ! $post_exists ) {
            $post_data = array(
                'post_title'   => $operator['title'],
                'post_name'    => $slug,
                'post_content' => $operator['content'],
                'post_status'  => 'publish',
                'post_type'    => 'operator',
                'post_author'  => 1,
            );
            
            $post_id = wp_insert_post( $post_data );
            
            if ( $post_id ) {
                update_post_meta( $post_id, 'oc_operator_license', $operator['license'] );
                update_post_meta( $post_id, 'oc_operator_rating', $operator['rating'] );
                update_post_meta( $post_id, 'oc_operator_affiliate_url', $operator['affiliate_url'] );
                update_post_meta( $post_id, 'oc_operator_bonus_text', $operator['bonus_text'] );
                update_post_meta( $post_id, 'oc_operator_bonus_value', $operator['bonus_value'] );
                update_post_meta( $post_id, 'oc_operator_bonus_type', $operator['bonus_type'] );
                update_post_meta( $post_id, 'oc_operator_min_deposit', $operator['min_deposit'] );
                update_post_meta( $post_id, 'oc_operator_min_bet', $operator['min_bet'] );
                update_post_meta( $post_id, 'oc_operator_payment_methods', $operator['payment_methods'] );
                
                // Add pros and cons
                $pros = array(
                    __( 'Competitive odds', 'odds-comparison' ),
                    __( 'Wide range of sports markets', 'odds-comparison' ),
                    __( 'Excellent live betting platform', 'odds-comparison' ),
                );
                $cons = array(
                    __( 'Limited casino offerings', 'odds-comparison' ),
                );
                update_post_meta( $post_id, 'oc_operator_pros', $pros );
                update_post_meta( $post_id, 'oc_operator_cons', $cons );
                
                $imported++;
            }
        }
    }
    
    return $imported;
}

/**
 * Import demo matches
 * 
 * @since 1.0.0
 * 
 * @return int Number of matches imported
 */
function oc_import_demo_matches() {
    // Get team IDs with null checks
    $teams = array(
        'man-city' => get_term_by( 'slug', 'man-city', 'team' ),
        'liverpool' => get_term_by( 'slug', 'liverpool', 'team' ),
        'arsenal' => get_term_by( 'slug', 'arsenal', 'team' ),
        'man-utd' => get_term_by( 'slug', 'man-utd', 'team' ),
        'chelsea' => get_term_by( 'slug', 'chelsea', 'team' ),
        'real-madrid' => get_term_by( 'slug', 'real-madrid', 'team' ),
        'barcelona' => get_term_by( 'slug', 'barcelona', 'team' ),
        'atletico' => get_term_by( 'slug', 'atletico', 'team' ),
        'juventus' => get_term_by( 'slug', 'juventus', 'team' ),
        'inter-milan' => get_term_by( 'slug', 'inter-milan', 'team' ),
        'bayern-munich' => get_term_by( 'slug', 'bayern-munich', 'team' ),
        'dortmund' => get_term_by( 'slug', 'dortmund', 'team' ),
        'psg' => get_term_by( 'slug', 'psg', 'team' ),
    );
    
    // Get league IDs
    $premier_league = get_term_by( 'slug', 'premier-league', 'league' );
    $la_liga = get_term_by( 'slug', 'la-liga', 'league' );
    $champions_league = get_term_by( 'slug', 'champions-league', 'league' );
    $bundesliga = get_term_by( 'slug', 'bundesliga', 'league' );
    
    // Get sport ID
    $football = get_term_by( 'slug', 'football', 'sport' );
    
    // Helper function to get term name safely
    $get_term_name = function( $term ) {
        return ( $term && isset( $term->name ) ) ? $term->name : '';
    };
    
    // Helper function to get term ID safely
    $get_term_id = function( $term ) {
        return ( $term && isset( $term->term_id ) ) ? $term->term_id : 0;
    };
    
    // Helper function to get term slug safely
    $get_term_slug = function( $term ) {
        return ( $term && isset( $term->slug ) ) ? $term->slug : '';
    };
    
    $matches = array(
        array(
            'title' => sprintf( '%s vs %s', $get_term_name( $teams['man-city'] ), $get_term_name( $teams['liverpool'] ) ),
            'home_team' => $teams['man-city'],
            'away_team' => $teams['liverpool'],
            'league' => $premier_league,
            'sport' => $football,
            'date' => date( 'Y-m-d', strtotime( '+3 days' ) ),
            'time' => '20:00',
            'status' => 'upcoming',
            'stadium' => 'Etihad Stadium, Manchester',
        ),
        array(
            'title' => sprintf( '%s vs %s', $get_term_name( $teams['arsenal'] ), $get_term_name( $teams['man-utd'] ) ),
            'home_team' => $teams['arsenal'],
            'away_team' => $teams['man-utd'],
            'league' => $premier_league,
            'sport' => $football,
            'date' => date( 'Y-m-d', strtotime( '+5 days' ) ),
            'time' => '17:30',
            'status' => 'upcoming',
            'stadium' => 'Emirates Stadium, London',
        ),
        array(
            'title' => sprintf( '%s vs %s', $get_term_name( $teams['real-madrid'] ), $get_term_name( $teams['barcelona'] ) ),
            'home_team' => $teams['real-madrid'],
            'away_team' => $teams['barcelona'],
            'league' => $champions_league,
            'sport' => $football,
            'date' => date( 'Y-m-d', strtotime( '+7 days' ) ),
            'time' => '20:00',
            'status' => 'upcoming',
            'stadium' => 'Santiago Bernabéu, Madrid',
        ),
        array(
            'title' => sprintf( '%s vs %s', $get_term_name( $teams['bayern-munich'] ), $get_term_name( $teams['dortmund'] ) ),
            'home_team' => $teams['bayern-munich'],
            'away_team' => $teams['dortmund'],
            'league' => $bundesliga,
            'sport' => $football,
            'date' => date( 'Y-m-d', strtotime( '+4 days' ) ),
            'time' => '18:30',
            'status' => 'upcoming',
            'stadium' => 'Allianz Arena, Munich',
        ),
        array(
            'title' => sprintf( '%s vs %s', $get_term_name( $teams['chelsea'] ), $get_term_name( $teams['psg'] ) ),
            'home_team' => $teams['chelsea'],
            'away_team' => $teams['psg'],
            'league' => $champions_league,
            'sport' => $football,
            'date' => date( 'Y-m-d', strtotime( '+10 days' ) ),
            'time' => '20:00',
            'status' => 'upcoming',
            'stadium' => 'Stamford Bridge, London',
        ),
    );
    
    $imported = 0;
    
    foreach ( $matches as $match ) {
        // Skip if teams don't exist
        if ( ! $match['home_team'] || ! $match['away_team'] ) {
            continue;
        }
        
        $slug = sanitize_title( $match['title'] );
        $post_exists = get_page_by_path( $slug, OBJECT, 'match' );
        
        if ( ! $post_exists ) {
            $home_team_name = $get_term_name( $match['home_team'] );
            $away_team_name = $get_term_name( $match['away_team'] );
            $league_name = $get_term_name( $match['league'] );
            
            $post_data = array(
                'post_title'   => $match['title'],
                'post_name'    => $slug,
                'post_content' => sprintf(
                    __( '%s vs %s - Upcoming match in %s. Don\'t miss the opportunity to compare odds from the best bookmakers.', 'odds-comparison' ),
                    $home_team_name,
                    $away_team_name,
                    $league_name
                ),
                'post_status'  => 'publish',
                'post_type'    => 'match',
                'post_author'  => 1,
            );
            
            $post_id = wp_insert_post( $post_data );
            
            if ( $post_id ) {
                // Set taxonomies safely
                if ( $match['sport'] ) {
                    wp_set_object_terms( $post_id, $get_term_id( $match['sport'] ), 'sport' );
                }
                if ( $match['league'] ) {
                    wp_set_object_terms( $post_id, $get_term_id( $match['league'] ), 'league' );
                }
                wp_set_object_terms( $post_id, array( $get_term_id( $match['home_team'] ), $get_term_id( $match['away_team'] ) ), 'team' );
                
                // Set meta with safe values
                update_post_meta( $post_id, 'oc_match_date', $match['date'] );
                update_post_meta( $post_id, 'oc_match_time', $match['time'] );
                update_post_meta( $post_id, 'oc_match_status', $match['status'] );
                update_post_meta( $post_id, 'oc_match_league', $league_name );
                update_post_meta( $post_id, 'oc_match_stadium', $match['stadium'] );
                update_post_meta( $post_id, 'oc_match_home_team', $get_term_id( $match['home_team'] ) );
                update_post_meta( $post_id, 'oc_match_away_team', $get_term_id( $match['away_team'] ) );
                
                $imported++;
            }
        }
    }
    
    return $imported;
}

/**
 * Import demo odds
 * 
 * @since 1.0.0
 * 
 * @return int Number of odds entries imported
 */
function oc_import_demo_odds() {
    global $wpdb;
    
    $operators = get_posts( array( 'post_type' => 'operator', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
    $matches = get_posts( array( 'post_type' => 'match', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
    
    if ( empty( $operators ) || empty( $matches ) ) {
        return 0;
    }
    
    $imported = 0;
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    foreach ( $matches as $match ) {
        foreach ( $operators as $operator ) {
            // Check if odds already exist
            $existing = $wpdb->get_row( $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE match_id = %d AND bookmaker_id = %d",
                $match->ID,
                $operator->ID
            ) );
            
            if ( ! $existing ) {
                // Generate random but realistic odds
                $odds_home = number_format( rand( 15, 35 ) / 10 + 1, 2 );
                $odds_draw = number_format( rand( 25, 45 ) / 10 + 1, 2 );
                $odds_away = number_format( rand( 15, 35 ) / 10 + 1, 2 );
                
                $wpdb->insert(
                    $table_name,
                    array(
                        'match_id'      => $match->ID,
                        'bookmaker_id'  => $operator->ID,
                        'odds_home'     => $odds_home,
                        'odds_draw'     => $odds_draw,
                        'odds_away'     => $odds_away,
                        'last_updated'  => current_time( 'mysql' ),
                    ),
                    array( '%d', '%d', '%f', '%f', '%f', '%s' )
                );
                
                $imported++;
            }
        }
    }
    
    return $imported;
}

/**
 * Create required pages on theme activation
 * 
 * @since 1.0.0
 * 
 * @return int Number of pages created
 */
function oc_create_required_pages() {
    $pages = array(
        'home' => array(
            'title' => __( 'Home', 'odds-comparison' ),
            'content' => '',
            'template' => 'templates/home.php',
        ),
        'matches' => array(
            'title' => __( 'All Matches', 'odds-comparison' ),
            'content' => '',
            'template' => 'templates/archive-match.php',
        ),
        'operators' => array(
            'title' => __( 'Betting Operators', 'odds-comparison' ),
            'content' => '',
            'template' => 'templates/archive-operator.php',
        ),
        'odds-comparison' => array(
            'title' => __( 'Odds Comparison', 'odds-comparison' ),
            'content' => __( 'Compare the best betting odds from top bookmakers across all upcoming matches.', 'odds-comparison' ),
            'template' => 'templates/page-odds-comparison.php',
        ),
        'bonuses' => array(
            'title' => __( 'Betting Bonuses', 'odds-comparison' ),
            'content' => __( 'Discover the best betting bonuses and promotional offers from top bookmakers.', 'odds-comparison' ),
            'template' => '',
        ),
        'about' => array(
            'title' => __( 'About Us', 'odds-comparison' ),
            'content' => __( 'We provide the best odds comparison service for sports betting enthusiasts. Compare odds from all major bookmakers and find the best value for your bets.', 'odds-comparison' ),
            'template' => '',
        ),
        'contact' => array(
            'title' => __( 'Contact', 'odds-comparison' ),
            'content' => __( 'Get in touch with us for any questions or inquiries.', 'odds-comparison' ),
            'template' => '',
        ),
    );
    
    $imported = 0;
    
    foreach ( $pages as $slug => $page ) {
        $page_exists = get_page_by_path( $slug );
        
        if ( ! $page_exists ) {
            $post_data = array(
                'post_title'   => $page['title'],
                'post_name'    => $slug,
                'post_content' => $page['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
            );
            
            $post_id = wp_insert_post( $post_data );
            
            if ( $post_id && ! empty( $page['template'] ) ) {
                update_post_meta( $post_id, '_wp_page_template', $page['template'] );
            }
            
            $imported++;
        }
    }
    
    // Set homepage and posts page
    $homepage = get_page_by_path( 'home' );
    $blog_page = get_page_by_path( 'odds-comparison' );
    
    if ( $homepage ) {
        update_option( 'page_on_front', $homepage->ID );
        update_option( 'show_on_front', 'page' );
    }
    
    if ( $blog_page ) {
        update_option( 'page_for_posts', $blog_page->ID );
    }
    
    return $imported;
}

/**
 * Check if demo data has been imported
 * 
 * @since 1.0.0
 * 
 * @return bool
 */
function oc_is_demo_data_imported() {
    return (bool) get_option( 'oc_demo_data_imported', false );
}

/**
 * Get demo data import date
 * 
 * @since 1.0.0
 * 
 * @return string|false
 */
function oc_get_demo_data_import_date() {
    return get_option( 'oc_demo_data_imported_date', false );
}

/**
 * Reset demo data (for testing)
 * 
 * @since 1.0.0
 */
function oc_reset_demo_data() {
    // Delete all matches
    $matches = get_posts( array( 'post_type' => 'match', 'post_status' => 'any', 'posts_per_page' => -1 ) );
    foreach ( $matches as $match ) {
        wp_delete_post( $match->ID, true );
    }
    
    // Delete all operators
    $operators = get_posts( array( 'post_type' => 'operator', 'post_status' => 'any', 'posts_per_page' => -1 ) );
    foreach ( $operators as $operator ) {
        wp_delete_post( $operator->ID, true );
    }
    
    // Delete team taxonomy terms
    $teams = get_terms( array( 'taxonomy' => 'team', 'hide_empty' => false ) );
    foreach ( $teams as $team ) {
        wp_delete_term( $team->term_id, 'team' );
    }
    
    // Delete league taxonomy terms
    $leagues = get_terms( array( 'taxonomy' => 'league', 'hide_empty' => false ) );
    foreach ( $leagues as $league ) {
        wp_delete_term( $league->term_id, 'league' );
    }
    
    // Delete sport taxonomy terms
    $sports = get_terms( array( 'taxonomy' => 'sport', 'hide_empty' => false ) );
    foreach ( $sports as $sport ) {
        wp_delete_term( $sport->term_id, 'sport' );
    }
    
    // Clear database tables
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->prefix}oc_match_odds" );
    $wpdb->query( "DELETE FROM {$wpdb->prefix}oc_affiliate_clicks" );
    
    // Delete options
    delete_option( 'oc_demo_data_imported' );
    delete_option( 'oc_demo_data_imported_date' );
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * AJAX handler for importing demo data
 * 
 * @since 1.0.0
 */
function oc_ajax_import_demo_data() {
    check_ajax_referer( 'oc_demo_import_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'odds-comparison' ) ) );
    }
    
    if ( oc_is_demo_data_imported() ) {
        wp_send_json_error( array( 'message' => __( 'Demo data already imported.', 'odds-comparison' ) ) );
    }
    
    $results = oc_import_demo_data();
    
    if ( $results['success'] ) {
        wp_send_json_success( array(
            'message' => __( 'Demo data imported successfully!', 'odds-comparison' ),
            'results' => $results['imported'],
        ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Error importing demo data.', 'odds-comparison' ) ) );
    }
}
add_action( 'wp_ajax_oc_import_demo_data', 'oc_ajax_import_demo_data' );

/**
 * AJAX handler for resetting demo data
 * 
 * @since 1.0.0
 */
function oc_ajax_reset_demo_data() {
    check_ajax_referer( 'oc_demo_import_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'odds-comparison' ) ) );
    }
    
    oc_reset_demo_data();
    
    wp_send_json_success( array( 'message' => __( 'Demo data reset successfully.', 'odds-comparison' ) ) );
}
add_action( 'wp_ajax_oc_reset_demo_data', 'oc_ajax_reset_demo_data' );

