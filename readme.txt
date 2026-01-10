# Odds Comparison

A comprehensive WordPress plugin for comparing betting odds across multiple bookmakers.

## Description

Odds Comparison is a powerful WordPress plugin that allows you to create a full-featured odds comparison website. The plugin provides all the necessary functionality to display betting odds from multiple bookmakers, manage matches and operators, and provide your users with the best betting experience.

### Features

- **Match Management**: Create and manage sports matches with detailed information
- **Odds Comparison**: Display and compare odds from multiple bookmakers
- **Operator Listings**: Showcase betting operators with ratings, bonuses, and reviews
- **REST API**: Full REST API support for external integrations
- **Shortcodes**: Flexible shortcodes for displaying odds anywhere on your site
- **Widgets**: Custom widgets for displaying odds, matches, and operators
- **Affiliate Tracking**: Track affiliate clicks and conversions
- **Responsive Design**: Clean, responsive design that works on all devices
- **Multi-language Support**: Translation-ready with .pot file included

### Post Types

- **Matches**: Manage sports matches with teams, dates, venues, and odds
- **Operators**: Showcase betting operators with ratings, bonuses, and features

### Taxonomies

- **Sports**: Organize matches by sport (Football, Basketball, Tennis, etc.)
- **Leagues**: Organize matches by league (Premier League, La Liga, etc.)
- **Licenses**: Categorize operators by license (UKGC, MGA, etc.)

### Shortcodes

- `[oc_odds match_id="123"]` - Display odds for a specific match
- `[oc_matches sport="football" limit="6"]` - Display list of matches
- `[oc_operators limit="4"]` - Display list of operators
- `[oc_best_odds match_id="123"]` - Display only best odds

### Widgets

- **Best Odds**: Display best odds for upcoming matches
- **Matches List**: Show upcoming/live matches
- **Betting Operators**: List operators with ratings

## Installation

1. Upload the `odds-comparison` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under "Odds Comparison" in the admin menu

## Configuration

1. Go to Odds Comparison > Settings to configure general settings
2. Create your first match: Matches > Add New
3. Create your first operator: Operators > Add New
4. Add odds for matches under Odds Comparison > Manage Odds
5. Create archive pages for matches and operators

## Frequently Asked Questions

### How do I add odds for a match?

Go to Odds Comparison > Manage Odds and use the form to add odds for any match from any bookmaker.

### Can I use my own templates?

Yes! Copy any template file from `odds-comparison/templates/` to your theme's `odds-comparison/` folder to override the default templates.

### Is the plugin translation ready?

Yes, the plugin includes a `.pot` file in the `languages` folder. You can create your own translation files.

## Changelog

### 1.0.0
- Initial release
- Match and operator post types
- Sports, leagues, and licenses taxonomies
- REST API support
- Shortcodes and widgets
- AJAX functionality
- Responsive design

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please open an issue on GitHub or contact the developer.

