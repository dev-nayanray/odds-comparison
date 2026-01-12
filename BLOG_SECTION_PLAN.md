# Blog Section Implementation Plan

## Overview
Add a modern blog post grid section to the homepage (`templates/home.php`) with a clean, responsive design.

## Implementation Steps

### Step 1: Update templates/home.php
- Add a new `oc-blog-section` section after the existing content sections
- Use WP_Query to fetch latest blog posts (6 posts for a 2x3 grid)
- Include post thumbnail, title, excerpt, date, author, and category
- Add "View All Posts" link

### Step 2: Add CSS styles to assets/css/odds-comparison.css
- Add blog section container styles
- Create responsive grid layout (1 col mobile, 2 cols tablet, 3 cols desktop)
- Style blog cards with hover effects
- Add modern typography and spacing
- Include badges for categories and dates

### Step 3: Test the implementation
- Verify responsive design
- Check hover effects
- Validate WordPress loop functionality

## Files to Modify
1. `templates/home.php` - Add blog section HTML/PHP
2. `assets/css/odds-comparison.css` - Add blog grid styles

## Design Specifications
- **Grid layout**: CSS Grid with `repeat(auto-fill, minmax(320px, 1fr))`
- **Card height**: Variable with consistent spacing
- **Hover effect**: Subtle lift with shadow increase
- **Categories**: Color-coded badges
- **Date format**: "d M Y" (e.g., "15 Jan 2024")
- **Excerpt**: 20 words with ellipsis

