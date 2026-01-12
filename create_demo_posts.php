<?php
/**
 * Create Demo Blog Posts
 *
 * This script creates sample blog posts for the sidebar pronósticos section
 */

// Include WordPress
define('WP_USE_THEMES', false);
require_once('wp-load.php');

echo "Creating demo blog posts...\n";

// Create some demo posts
$posts = array(
    array(
        'post_title' => 'Pronóstico Real Madrid vs Barcelona - La Liga 2026',
        'post_content' => 'Análisis detallado del partido entre Real Madrid y Barcelona en la jornada de La Liga. Cuotas, estadísticas y predicciones para este clásico español.',
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'post_category' => array(1) // Default category
    ),
    array(
        'post_title' => 'Apuestas Champions League: PSG vs Bayern Munich',
        'post_content' => 'Guía completa para apostar en el partido de Champions League entre PSG y Bayern Munich. Mejores cuotas y estrategias de apuesta.',
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'post_category' => array(1)
    ),
    array(
        'post_title' => 'Predicciones Premier League: Manchester City vs Liverpool',
        'post_content' => 'Análisis del derby inglés entre Manchester City y Liverpool. Estadísticas, forma actual y recomendaciones de apuesta.',
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'post_category' => array(1)
    ),
    array(
        'post_title' => 'Cuotas NBA: Lakers vs Warriors',
        'post_content' => 'Pronóstico para el partido de NBA entre Los Angeles Lakers y Golden State Warriors. Análisis de jugadores y mejores apuestas.',
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s', strtotime('-4 days')),
        'post_category' => array(1)
    ),
    array(
        'post_title' => 'Tenis ATP: Nadal vs Djokovic',
        'post_content' => 'Predicciones para el enfrentamiento entre Rafael Nadal y Novak Djokovic en el torneo ATP. Cuotas y análisis técnico.',
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s', strtotime('-5 days')),
        'post_category' => array(1)
    )
);

$created = 0;
foreach ($posts as $post_data) {
    $post_id = wp_insert_post($post_data);
    if ($post_id) {
        $created++;
        echo 'Created post: ' . $post_data['post_title'] . "\n";
    }
}

echo 'Total demo posts created: ' . $created . "\n";
