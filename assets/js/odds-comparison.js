/**
 * Main JavaScript
 *
 * Front-end JavaScript for Odds Comparison plugin.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initOddsFilters();
        initMatchCards();
        initOperatorCards();
        initAjaxLoaders();
        initTopBarSlider();
        initQuotaDropdown();
        initMobileMenu();
    });

    /**
     * Initialize top bar slider
     */
    function initTopBarSlider() {
        const sliderContainer = document.querySelector('.oc-slider-container');
        const sliderTrack = document.querySelector('.oc-slider-track');
        const prevBtn = document.querySelector('.oc-slider-prev');
        const nextBtn = document.querySelector('.oc-slider-next');
        const sliderItems = document.querySelectorAll('.oc-slider-item');

        if (!sliderContainer || !sliderTrack || sliderItems.length === 0) return;

        let currentIndex = 0;
        let itemsPerView = getItemsPerView();
        let totalSlides = sliderItems.length;

        function getItemsPerView() {
            const width = window.innerWidth;
            if (width <= 480) return 1;
            if (width <= 768) return 2;
            if (width <= 992) return 3;
            return 4;
        }

        function updateSlider() {
            const itemWidth = sliderItems[0].offsetWidth + parseInt(getComputedStyle(sliderTrack).gap || 16);
            const containerWidth = sliderContainer.offsetWidth;
            const maxIndex = Math.max(0, totalSlides - itemsPerView);

            // Clamp current index
            currentIndex = Math.min(currentIndex, maxIndex);
            currentIndex = Math.max(currentIndex, 0);

            // Calculate offset
            const offset = currentIndex * itemWidth;
            sliderTrack.style.transform = `translateX(-${offset}px)`;

            // Update button states
            if (prevBtn) {
                prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
                prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
            }
            if (nextBtn) {
                nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
                nextBtn.style.pointerEvents = currentIndex >= maxIndex ? 'none' : 'auto';
            }
        }

        function goToNext() {
            const maxIndex = Math.max(0, totalSlides - itemsPerView);
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSlider();
            }
        }

        function goToPrev() {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        }

        // Event listeners
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goToNext();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goToPrev();
            });
        }

        // Auto-slide animation
        let autoSlideInterval;
        const autoSlideDelay = 4000;

        function startAutoSlide() {
            autoSlideInterval = setInterval(function() {
                const maxIndex = Math.max(0, totalSlides - itemsPerView);
                if (currentIndex >= maxIndex) {
                    currentIndex = 0;
                } else {
                    currentIndex++;
                }
                updateSlider();
            }, autoSlideDelay);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        // Start auto-slide
        startAutoSlide();

        // Pause on hover
        const topBar = document.querySelector('.oc-top-bar');
        if (topBar) {
            topBar.addEventListener('mouseenter', stopAutoSlide);
            topBar.addEventListener('mouseleave', startAutoSlide);
        }

        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                const newItemsPerView = getItemsPerView();
                if (newItemsPerView !== itemsPerView) {
                    itemsPerView = newItemsPerView;
                    currentIndex = 0;
                    updateSlider();
                } else {
                    updateSlider();
                }
            }, 100);
        });

        // Initial update
        updateSlider();

        // Pause auto-slide when tab is not visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoSlide();
            } else {
                startAutoSlide();
            }
        });
    }

    /**
     * Initialize quota format dropdown
     */
    function initQuotaDropdown() {
        const quotaDropdown = document.querySelector('.oc-quota-dropdown');

        if (!quotaDropdown) return;

        const quotaBtn = quotaDropdown.querySelector('.oc-quota-btn');
        const quotaMenu = quotaDropdown.querySelector('.oc-quota-menu');
        const quotaOptions = quotaDropdown.querySelectorAll('.oc-quota-option');

        if (!quotaBtn || !quotaMenu) return;

        // Toggle dropdown
        quotaBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            quotaDropdown.classList.toggle('open');
            quotaBtn.setAttribute('aria-expanded', quotaDropdown.classList.contains('open'));
        });

        // Handle option selection
        quotaOptions.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();

                const format = this.dataset.format;

                // Update active state
                quotaOptions.forEach(function(opt) {
                    opt.classList.remove('active');
                });
                this.classList.add('active');

                // Update button value
                const quotaValue = quotaDropdown.querySelector('.oc-quota-value');
                if (quotaValue) {
                    quotaValue.textContent = format.toUpperCase();
                }

                // Close dropdown
                quotaDropdown.classList.remove('open');
                quotaBtn.setAttribute('aria-expanded', 'false');

                // Save preference via AJAX
                saveQuotaPreference(format);

                // Trigger custom event for other components
                document.dispatchEvent(new CustomEvent('ocQuotaFormatChanged', {
                    detail: { format: format }
                }));
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!quotaDropdown.contains(e.target)) {
                quotaDropdown.classList.remove('open');
                quotaBtn.setAttribute('aria-expanded', 'false');
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && quotaDropdown.classList.contains('open')) {
                quotaDropdown.classList.remove('open');
                quotaBtn.setAttribute('aria-expanded', 'false');
                quotaBtn.focus();
            }
        });
    }

    /**
     * Save quota format preference
     */
    function saveQuotaPreference(format) {
        const data = {
            action: 'oc_save_quota_format',
            nonce: ocAjax.nonce,
            format: format
        };

        fetch(ocAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        }).catch(function() {
            console.log('Failed to save quota format preference');
        });
    }

    /**
     * Initialize mobile menu toggle
     */
    function initMobileMenu() {
        const mobileToggle = document.querySelector('.oc-mobile-toggle');
        const mainNavigation = document.querySelector('.main-navigation');

        if (!mobileToggle || !mainNavigation) return;

        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            mainNavigation.classList.toggle('active');
            this.setAttribute('aria-expanded', this.classList.contains('active'));
        });

        // Close menu when clicking on a link
        const navLinks = mainNavigation.querySelectorAll('a');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                mobileToggle.classList.remove('active');
                mainNavigation.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mainNavigation.contains(e.target) && !mobileToggle.contains(e.target)) {
                mobileToggle.classList.remove('active');
                mainNavigation.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mainNavigation.classList.contains('active')) {
                mobileToggle.classList.remove('active');
                mainNavigation.classList.remove('active');
                mobileToggle.setAttribute('aria-expanded', 'false');
                mobileToggle.focus();
            }
        });
    }
    
    /**
     * Initialize odds filters functionality
     */
    function initOddsFilters() {
        const sortSelects = document.querySelectorAll('#oc-sort-odds, .oc-sort-select');
        const marketSelects = document.querySelectorAll('#oc-filter-market');
        
        sortSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                const container = this.closest('.oc-odds-comparison, .oc-odds-shortcode');
                if (container) {
                    sortOdds(container, this.value);
                }
            });
        });
        
        marketSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                const container = this.closest('.oc-odds-comparison, .oc-odds-shortcode');
                if (container) {
                    filterMarket(container, this.value);
                }
            });
        });
    }
    
    /**
     * Sort odds in container
     */
    function sortOdds(container, sortBy) {
        const oddsList = container.querySelector('.oc-odds-list, .oc-odds-container');
        if (!oddsList) return;
        
        const rows = Array.from(oddsList.querySelectorAll('.oc-odd-row, .oc-odds-row'));
        
        rows.forEach(function(row) {
            if (row.classList.contains('oc-header-row')) return;
            
            let sortValue;
            switch (sortBy) {
                case 'rating':
                    sortValue = parseFloat(row.dataset.rating) || 0;
                    break;
                case 'odds_high':
                    const highValues = row.querySelectorAll('.oc-odd-value, .oc-odds-btn');
                    sortValue = 0;
                    highValues.forEach(function(v) {
                        const num = parseFloat(v.textContent);
                        if (!isNaN(num) && num > sortValue) sortValue = num;
                    });
                    break;
                case 'odds_low':
                    const lowValues = row.querySelectorAll('.oc-odd-value, .oc-odds-btn');
                    sortValue = Infinity;
                    lowValues.forEach(function(v) {
                        const num = parseFloat(v.textContent);
                        if (!isNaN(num) && num < sortValue) sortValue = num;
                    });
                    if (sortValue === Infinity) sortValue = 0;
                    break;
                case 'bonus':
                    const bonusEl = row.querySelector('.oc-bookmaker-bonus');
                    sortValue = bonusEl ? bonusEl.textContent.length : 0;
                    break;
                default:
                    sortValue = 0;
            }
            row.dataset.sortValue = sortValue;
        });
        
        rows.sort(function(a, b) {
            const headerA = a.classList.contains('oc-header-row');
            const headerB = b.classList.contains('oc-header-row');
            if (headerA) return -1;
            if (headerB) return 1;
            
            const valA = parseFloat(a.dataset.sortValue) || 0;
            const valB = parseFloat(b.dataset.sortValue) || 0;
            return valB - valA;
        });
        
        const headerRow = oddsList.querySelector('.oc-header-row, .oc-header-row');
        if (headerRow) {
            oddsList.innerHTML = '';
            oddsList.appendChild(headerRow);
            rows.forEach(function(row) {
                if (!row.classList.contains('oc-header-row')) {
                    oddsList.appendChild(row);
                }
            });
        }
    }
    
    /**
     * Filter odds by market type
     */
    function filterMarket(container, marketType) {
        const rows = container.querySelectorAll('.oc-odd-row, .oc-odds-row');
        
        rows.forEach(function(row) {
            if (row.classList.contains('oc-header-row')) return;
            
            const oddCells = row.querySelectorAll('.oc-odd-cell');
            let showRow = true;
            
            if (marketType !== 'all') {
                showRow = false;
                oddCells.forEach(function(cell, index) {
                    if ((marketType === 'home' && index === 0) ||
                        (marketType === 'draw' && index === 1) ||
                        (marketType === 'away' && index === 2)) {
                        const value = cell.querySelector('.oc-odd-value, .oc-odds-btn');
                        if (value && !cell.classList.contains('oc-na')) {
                            showRow = true;
                        }
                    }
                });
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    /**
     * Initialize match card interactions
     */
    function initMatchCards() {
        const matchCards = document.querySelectorAll('.oc-match-card');
        
        matchCards.forEach(function(card) {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.oc-view-btn, .oc-link')) return;
                
                const link = this.querySelector('.oc-view-btn, .oc-link');
                if (link) {
                    window.location.href = link.href;
                }
            });
        });
    }
    
    /**
     * Initialize operator card interactions
     */
    function initOperatorCards() {
        const operatorCards = document.querySelectorAll('.oc-operator-card');
        
        operatorCards.forEach(function(card) {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.oc-visit-btn, .oc-visit, .oc-review-btn')) return;
                
                const link = this.querySelector('.oc-operator-name a, .oc-review-btn');
                if (link) {
                    window.location.href = link.href;
                }
            });
        });
    }
    
    /**
     * Initialize AJAX-based content loaders
     */
    function initAjaxLoaders() {
        const loadMoreBtns = document.querySelectorAll('.oc-load-more');
        const filterSelects = document.querySelectorAll('.oc-archive-filters select');
        
        loadMoreBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                loadMoreMatches(this);
            });
        });
        
        filterSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                const container = this.closest('.oc-matches-archive, .oc-operators-archive');
                if (container) {
                    filterArchiveContent(container);
                }
            });
        });
    }
    
    /**
     * Load more matches via AJAX
     */
    function loadMoreMatches(button) {
        const container = button.closest('.oc-matches-list, .oc-matches-grid');
        const page = parseInt(button.dataset.page) || 1;
        const perPage = parseInt(button.dataset.perPage) || 10;
        const sport = button.dataset.sport || '';
        const league = button.dataset.league || '';
        
        button.textContent = ocAjax.loading;
        button.disabled = true;
        
        const data = {
            action: 'oc_load_more_matches',
            nonce: ocAjax.nonce,
            page: page + 1,
            per_page: perPage,
            sport: sport,
            league: league
        };
        
        fetch(ocAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                const matchesContainer = container.querySelector('.oc-matches-grid') || container;
                
                data.data.matches.forEach(function(match) {
                    const matchCard = createMatchCard(match);
                    matchesContainer.appendChild(matchCard);
                });
                
                button.dataset.page = data.data.current_page;
                
                if (!data.data.has_more) {
                    button.style.display = 'none';
                }
            } else {
                button.textContent = ocAjax.error;
            }
        })
        .catch(function() {
            button.textContent = ocAjax.error;
        })
        .finally(function() {
            button.disabled = false;
        });
    }
    
    /**
     * Create match card HTML
     */
    function createMatchCard(match) {
        const article = document.createElement('article');
        article.className = 'oc-match-card' + (match.is_featured ? ' featured' : '') + (match.is_live ? ' live' : '');
        article.innerHTML = 
            '<div class="oc-teams">' +
                '<div class="oc-team oc-home">' +
                    '<span class="oc-team-name">' + escapeHtml(match.home_team) + '</span>' +
                '</div>' +
                '<div class="oc-match-vs">' +
                    '<span class="vs">vs</span>' +
                    '<span class="oc-match-date">' + escapeHtml(match.match_date) + '</span>' +
                '</div>' +
                '<div class="oc-team oc-away">' +
                    '<span class="oc-team-name">' + escapeHtml(match.away_team) + '</span>' +
                '</div>' +
            '</div>' +
            '<a href="' + match.url + '" class="oc-view-btn">Compare Odds</a>';
        return article;
    }
    
    /**
     * Filter archive content
     */
    function filterArchiveContent(container) {
        const sport = container.querySelector('#oc-filter-sport')?.value || '';
        const league = container.querySelector('#oc-filter-league')?.value || '';
        const rating = container.querySelector('#oc-filter-rating')?.value || '0';
        const sort = container.querySelector('#oc-filter-sort')?.value || 'date';
        
        const contentContainer = container.querySelector('#oc-matches-list, #oc-operators-list');
        if (!contentContainer) return;
        
        contentContainer.innerHTML = '<div class="oc-loading">' + ocAjax.loading + '</div>';
        
        const data = {
            action: 'oc_filter_content',
            nonce: ocAjax.nonce,
            sport: sport,
            league: league,
            min_rating: rating,
            sort: sort,
            post_type: container.classList.contains('oc-matches-archive') ? 'match' : 'operator'
        };
        
        fetch(ocAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                contentContainer.innerHTML = data.data.html || '<p class="oc-no-results">' + ocAjax.no_results + '</p>';
                initMatchCards();
                initOperatorCards();
            } else {
                contentContainer.innerHTML = '<p class="oc-error">' + ocAjax.error + '</p>';
            }
        })
        .catch(function() {
            contentContainer.innerHTML = '<p class="oc-error">' + ocAjax.error + '</p>';
        });
    }
    
    /**
     * Track affiliate clicks
     */
    function trackClick(operatorId, matchId, betType) {
        const data = {
            action: 'oc_track_click',
            nonce: ocAjax.nonce,
            operator_id: operatorId,
            match_id: matchId,
            bet_type: betType || ''
        };
        
        fetch(ocAjax.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        });
    }
    
    // Attach click handlers to affiliate links
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.oc-odd-button, .oc-visit-btn, .oc-visit');
        if (link) {
            const container = link.closest('.oc-odds-list, .oc-odds-container, .oc-odds-shortcode');
            const matchId = container?.dataset?.matchId || 0;
            const operatorId = link.closest('[data-bookmaker]')?.dataset?.bookmaker || 0;
            
            if (operatorId) {
                trackClick(operatorId, matchId, '');
            }
        }
    });
    
    /**
     * Utility: Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Expose functions globally
    window.ocSortOdds = sortOdds;
    window.ocFilterMarket = filterMarket;
    window.ocTrackClick = trackClick;
    
    // Initialize Live Matches functionality
    initLiveMatchesList();
    
    // Initialize Back to Top button
    initBackToTop();
    
})();

/**
 * Back to Top Button Functionality
 * Shows/hides a scroll-to-top button based on scroll position
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

/**
 * Initialize back to top button
 */
function initBackToTop() {
    const backToTopBtn = document.querySelector('.back-to-top, #back-to-top, .oc-back-to-top');
    
    if (!backToTopBtn) return;
    
    // Show/hide button based on scroll position
    function toggleBackToTop() {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    }
    
    // Scroll to top on button click
    backToTopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Smooth scroll to top
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Listen for scroll events
    window.addEventListener('scroll', function() {
        toggleBackToTop();
    }, { passive: true });
    
    // Initial state check
    toggleBackToTop();
}

/**
 * Live Matches List JavaScript
 * Handles AJAX loading, filtering, and auto-refresh for live matches
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

/**
 * Initialize live matches functionality
 */
function initLiveMatchesList() {
    const containers = document.querySelectorAll('.oc-live-matches-list');
    
    containers.forEach(function(container) {
        setupLiveMatchesFilters(container);
        setupRefreshButton(container);
        loadLiveMatches(container);
        startAutoRefresh(container);
    });
}

/**
 * Setup filter change handlers
 */
function setupLiveMatchesFilters(container) {
    const dateFilter = container.querySelector('.oc-filter-date');
    const sportFilter = container.querySelector('.oc-filter-sport');
    const leagueFilter = container.querySelector('.oc-filter-league');
    
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            filterLiveMatches(container);
        });
    }
    
    if (sportFilter) {
        sportFilter.addEventListener('change', function() {
            // Update league dropdown based on sport
            updateLeagueDropdown(container, this.value);
            filterLiveMatches(container);
        });
    }
    
    if (leagueFilter) {
        leagueFilter.addEventListener('change', function() {
            filterLiveMatches(container);
        });
    }
}

/**
 * Setup refresh button handler
 */
function setupRefreshButton(container) {
    const refreshBtn = container.querySelector('.oc-refresh-btn');
    
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            refreshLiveMatchesOdds(container);
        });
    }
}

/**
 * Load live matches via AJAX
 */
function loadLiveMatches(container, showLoading) {
    showLoading = showLoading !== false;
    
    const sport = container.querySelector('.oc-filter-sport')?.value || '';
    const league = container.querySelector('.oc-filter-league')?.value || '';
    const limit = parseInt(container.dataset.limit) || 50;
    
    if (showLoading) {
        const matchesContainer = container.querySelector('.oc-matches-container');
        if (matchesContainer) {
            matchesContainer.innerHTML = '<div class="oc-loading"><div class="spinner"></div><p>Loading matches...</p></div>';
        }
    }
    
    const data = {
        action: 'oc_load_live_matches',
        nonce: ocAjax.nonce,
        sport: sport,
        league: league,
        limit: limit
    };
    
    fetch(ocAjax.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(response) {
        if (response.success) {
            const matchesContainer = container.querySelector('.oc-matches-container');
            if (matchesContainer) {
                matchesContainer.innerHTML = response.data.html;
            }
            
            // Update last updated time
            updateLastUpdated(container, response.data.last_updated);
            
            // Store match IDs for refresh
            storeMatchIds(container, response.data.date_groups);
        } else {
            showError(container, ocAjax.error || 'Failed to load matches');
        }
    })
    .catch(function() {
        showError(container, ocAjax.error || 'An error occurred');
    });
}

/**
 * Filter live matches based on selected criteria
 */
function filterLiveMatches(container) {
    const dateFilter = container.querySelector('.oc-filter-date')?.value || 'all';
    const sport = container.querySelector('.oc-filter-sport')?.value || '';
    const league = container.querySelector('.oc-filter-league')?.value || '';
    const limit = parseInt(container.dataset.limit) || 50;
    
    const matchesContainer = container.querySelector('.oc-matches-container');
    if (matchesContainer) {
        matchesContainer.innerHTML = '<div class="oc-loading"><div class="spinner"></div><p>Loading matches...</p></div>';
    }
    
    const data = {
        action: 'oc_filter_matches',
        nonce: ocAjax.nonce,
        date_filter: dateFilter,
        sport: sport,
        league: league,
        limit: limit
    };
    
    fetch(ocAjax.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(response) {
        if (response.success) {
            if (matchesContainer) {
                matchesContainer.innerHTML = response.data.html;
            }
            
            updateLastUpdated(container, response.data.last_updated);
            storeMatchIds(container, response.data.date_groups);
        } else {
            showError(container, ocAjax.error || 'Failed to filter matches');
        }
    })
    .catch(function() {
        showError(container, ocAjax.error || 'An error occurred');
    });
}

/**
 * Refresh odds for visible matches
 */
function refreshLiveMatchesOdds(container) {
    const refreshBtn = container.querySelector('.oc-refresh-btn');
    if (refreshBtn) {
        refreshBtn.classList.add('refreshing');
        refreshBtn.querySelector('.dashicons')?.classList.add('spin');
    }
    
    const matchIds = getVisibleMatchIds(container);
    
    if (matchIds.length === 0) {
        if (refreshBtn) {
            refreshBtn.classList.remove('refreshing');
            refreshBtn.querySelector('.dashicons')?.classList.remove('spin');
        }
        return;
    }
    
    const data = {
        action: 'oc_refresh_odds',
        nonce: ocAjax.nonce,
        match_ids: matchIds
    };
    
    fetch(ocAjax.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(response) {
        if (response.success) {
            updateOddsInView(container, response.data.odds);
            updateLastUpdated(container, response.data.last_updated);
        }
    })
    .catch(function() {
        console.log('Failed to refresh odds');
    })
    .finally(function() {
        if (refreshBtn) {
            refreshBtn.classList.remove('refreshing');
            refreshBtn.querySelector('.dashicons')?.classList.remove('spin');
        }
    });
}

/**
 * Start auto-refresh interval
 */
function startAutoRefresh(container) {
    const autoRefresh = container.dataset.autoRefresh;
    
    if (!autoRefresh || autoRefresh === 'false') {
        return;
    }
    
    const interval = parseInt(autoRefresh) * 1000; // Convert to milliseconds
    
    setInterval(function() {
        // Only refresh if page is visible
        if (document.visibilityState === 'visible') {
            refreshLiveMatchesOdds(container);
        }
    }, interval);
}

/**
 * Update league dropdown based on selected sport
 */
function updateLeagueDropdown(container, sport) {
    const leagueSelect = container.querySelector('.oc-filter-league');
    
    if (!leagueSelect) return;
    
    // Get current value to restore after update
    const currentValue = leagueSelect.value;
    
    // Clear options except first
    while (leagueSelect.options.length > 1) {
        leagueSelect.remove(1);
    }
    
    if (!sport) {
        leagueSelect.value = '';
        return;
    }
    
    // Add leagues for selected sport (this would typically come from AJAX)
    const leagues = getLeaguesForSport(sport);
    
    leagues.forEach(function(league) {
        const option = document.createElement('option');
        option.value = league.slug;
        option.textContent = league.name;
        leagueSelect.appendChild(option);
    });
    
    // Restore value if still exists
    if (currentValue) {
        leagueSelect.value = currentValue;
    }
}

/**
 * Get leagues for a sport (mock data - would typically come from server)
 */
function getLeaguesForSport(sport) {
    const leagues = {
        football: [
            { slug: 'premier-league', name: 'Premier League' },
            { slug: 'la-liga', name: 'La Liga' },
            { slug: 'bundesliga', name: 'Bundesliga' },
            { slug: 'serie-a', name: 'Serie A' },
            { slug: 'ligue-1', name: 'Ligue 1' }
        ],
        basketball: [
            { slug: 'nba', name: 'NBA' },
            { slug: 'euroleague', name: 'EuroLeague' }
        ],
        tennis: [
            { slug: 'atp', name: 'ATP Tour' },
            { slug: 'wta', name: 'WTA Tour' }
        ]
    };
    
    return leagues[sport] || [];
}

/**
 * Store match IDs from date groups
 */
function storeMatchIds(container, dateGroups) {
    const matchIds = [];
    
    dateGroups.forEach(function(group) {
        group.leagues.forEach(function(league) {
            league.matches.forEach(function(match) {
                matchIds.push(match.id);
            });
        });
    });
    
    container.dataset.matchIds = JSON.stringify(matchIds);
}

/**
 * Get visible match IDs
 */
function getVisibleMatchIds(container) {
    try {
        return JSON.parse(container.dataset.matchIds || '[]');
    } catch (e) {
        return [];
    }
}

/**
 * Update odds in the view
 */
function updateOddsInView(container, oddsData) {
    Object.keys(oddsData).forEach(function(matchId) {
        const odds = oddsData[matchId];
        const matchRow = container.querySelector('.oc-match-row[data-match-id="' + matchId + '"]');
        
        if (!matchRow) return;
        
        // Update home odds
        const homeBtn = matchRow.querySelector('.oc-odd-home');
        if (homeBtn && odds.home > 0) {
            homeBtn.textContent = formatOdds(odds.home);
        }
        
        // Update draw odds
        const drawBtn = matchRow.querySelector('.oc-odd-draw');
        if (drawBtn && odds.draw > 0) {
            drawBtn.textContent = formatOdds(odds.draw);
        }
        
        // Update away odds
        const awayBtn = matchRow.querySelector('.oc-odd-away');
        if (awayBtn && odds.away > 0) {
            awayBtn.textContent = formatOdds(odds.away);
        }
    });
}

/**
 * Format odds value
 */
function formatOdds(value) {
    const num = parseFloat(value);
    if (isNaN(num) || num <= 0) {
        return '—';
    }
    return num.toFixed(2);
}

/**
 * Update last updated timestamp
 */
function updateLastUpdated(container, timestamp) {
    const lastUpdatedEl = container.querySelector('.oc-last-updated');
    
    if (lastUpdatedEl && timestamp) {
        const date = new Date(timestamp);
        const formattedTime = date.toLocaleTimeString();
        lastUpdatedEl.textContent = 'Last updated: ' + formattedTime;
    }
}

/**
 * Show error message
 */
function showError(container, message) {
    const matchesContainer = container.querySelector('.oc-matches-container');
    if (matchesContainer) {
        matchesContainer.innerHTML = '<div class="oc-matches-empty"><p>' + escapeHtml(message) + '</p></div>';
    }
}

/**
 * ============================================
 * HOMEPAGE BANNER SLIDER
 * ============================================
 */
function initBannerSlider() {
    const slider = document.querySelector('.oc-banner-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.oc-banner-slide');
    const dotsContainer = slider.querySelector('.oc-slider-controls');
    const dots = dotsContainer?.querySelectorAll('.oc-slider-dot');

    if (slides.length === 0) return;

    let currentSlide = 0;
    const totalSlides = slides.length;

    // Create dots if they don't exist
    if (!dotsContainer && slides.length > 1) {
        const controls = document.createElement('div');
        controls.className = 'oc-slider-controls';
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('button');
            dot.className = 'oc-slider-dot' + (i === 0 ? ' active' : '');
            dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
            dot.dataset.slide = i;
            controls.appendChild(dot);
        }
        slider.appendChild(controls);
    }

    const allDots = slider.querySelectorAll('.oc-slider-dot');

    function showSlide(index) {
        // Hide all slides
        slides.forEach(function(slide) {
            slide.classList.remove('active');
        });

        // Remove active from all dots
        allDots.forEach(function(dot) {
            dot.classList.remove('active');
        });

        // Show target slide
        if (slides[index]) {
            slides[index].classList.add('active');
        }

        // Activate target dot
        if (allDots[index]) {
            allDots[index].classList.add('active');
        }

        currentSlide = index;
    }

    function nextSlide() {
        const next = (currentSlide + 1) % totalSlides;
        showSlide(next);
    }

    function prevSlide() {
        const prev = (currentSlide - 1 + totalSlides) % totalSlides;
        showSlide(prev);
    }

    // Dot click handlers
    allDots.forEach(function(dot, index) {
        dot.addEventListener('click', function() {
            showSlide(index);
        });
    });

    // Auto-advance
    let slideInterval = setInterval(nextSlide, 5000);

    // Pause on hover
    const bannerSection = document.querySelector('.oc-banner-section');
    if (bannerSection) {
        bannerSection.addEventListener('mouseenter', function() {
            clearInterval(slideInterval);
        });
        bannerSection.addEventListener('mouseleave', function() {
            slideInterval = setInterval(nextSlide, 5000);
        });
    }

    // Expose navigation functions
    window.ocBannerNext = nextSlide;
    window.ocBannerPrev = prevSlide;
    window.ocBannerGoTo = showSlide;
}

/**
 * ============================================
 * COUPON / BET SLIP FUNCTIONALITY
 * ============================================
 */

// Initialize coupon functionality
function initCoupon() {
    // Coupon button in header
    const couponHeaderBtn = document.querySelector('.oc-coupon-header-btn');
    if (couponHeaderBtn) {
        couponHeaderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openCouponPopup();
        });
    }

    // Close popup handlers
    const popupClose = document.querySelector('.oc-popup-close');
    const popupOverlay = document.querySelector('.oc-coupon-popup-overlay');

    if (popupClose) {
        popupClose.addEventListener('click', closeCouponPopup);
    }

    if (popupOverlay) {
        popupOverlay.addEventListener('click', function(e) {
            if (e.target === popupOverlay) {
                closeCouponPopup();
            }
        });
    }

    // Escape key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCouponPopup();
        }
    });

    // Clear all button
    const clearAllBtn = document.querySelector('.oc-clear-all-btn');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', clearCoupon);
    }

    // Place bet button
    const placeBetBtn = document.querySelector('.oc-place-bet-btn');
    if (placeBetBtn) {
        placeBetBtn.addEventListener('click', placeBet);
    }

    // Initialize odds buttons with click handlers
    initOddsButtons();
}

// Initialize odds selection buttons
function initOddsButtons() {
    const oddButtons = document.querySelectorAll('.oc-odd-btn-compact');

    oddButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const matchCard = this.closest('.oc-match-card-horizontal');
            if (!matchCard) return;

            const matchId = matchCard.dataset.matchId || 0;
            const selection = this.dataset.selection || '';
            const odds = this.dataset.odds || '';
            const homeTeam = matchCard.querySelector('.oc-team-home .oc-team-name-compact')?.textContent || '';
            const awayTeam = matchCard.querySelector('.oc-team-away .oc-team-name-compact')?.textContent || '';
            const bookmakerId = this.dataset.bookmakerId || 0;
            const bookmakerName = this.dataset.bookmakerName || '';

            // Toggle selection
            if (this.classList.contains('selected')) {
                // Deselect
                this.classList.remove('selected');
                removeFromCoupon(matchId, selection);
            } else {
                // Select - remove selection from sibling buttons
                const siblings = matchCard.querySelectorAll('.oc-odd-btn-compact');
                siblings.forEach(function(sib) {
                    sib.classList.remove('selected');
                });

                this.classList.add('selected');
                addToCoupon({
                    matchId: matchId,
                    matchName: homeTeam + ' vs ' + awayTeam,
                    selection: selection,
                    odds: odds,
                    bookmakerId: bookmakerId,
                    bookmakerName: bookmakerName
                });
            }

            updateCouponUI();
        });
    });
}

// Coupon state
var couponItems = [];

function addToCoupon(item) {
    // Check if same match already exists
    const existingIndex = couponItems.findIndex(function(i) {
        return i.matchId === item.matchId;
    });

    if (existingIndex >= 0) {
        // Replace existing selection
        couponItems[existingIndex] = item;
    } else {
        couponItems.push(item);
    }

    saveCouponState();
    showToast('Selection added to coupon');
}

function removeFromCoupon(matchId, selection) {
    couponItems = couponItems.filter(function(item) {
        return !(item.matchId === matchId && item.selection === selection);
    });

    saveCouponState();
}

function clearCoupon() {
    couponItems = [];

    // Remove all selections
    document.querySelectorAll('.oc-odd-btn-compact.selected').forEach(function(btn) {
        btn.classList.remove('selected');
    });

    saveCouponState();
    updateCouponUI();
    showToast('Coupon cleared');
}

function updateCouponUI() {
    const emptyState = document.querySelector('.oc-coupon-empty');
    const betItems = document.querySelector('.oc-bet-items');
    const badge = document.querySelector('.oc-popup-badge, .oc-coupon-badge');
    const headerCount = document.querySelector('.oc-coupon-count');
    const count = couponItems.length;

    // Update badge count
    if (badge) {
        badge.textContent = count;
    }

    // Update header count
    if (headerCount) {
        headerCount.textContent = count;
        if (count > 0) {
            headerCount.style.display = 'inline-block';
        } else {
            headerCount.style.display = 'none';
        }
    }

    // Show empty state or items
    if (count === 0) {
        if (emptyState) emptyState.style.display = 'block';
        if (betItems) betItems.style.display = 'none';
    } else {
        if (emptyState) emptyState.style.display = 'none';
        if (betItems) {
            betItems.style.display = 'flex';
            betItems.innerHTML = renderCouponItems();
        }
    }
}

function renderCouponItems() {
    return couponItems.map(function(item, index) {
        return '<div class="oc-bet-item" data-match-id="' + item.matchId + '" data-selection="' + item.selection + '">' +
            '<div class="oc-bet-item-content">' +
                '<div class="oc-bet-item-header">' +
                    '<span class="oc-bet-type-badge">' + item.selection + '</span>' +
                    '<button class="oc-bet-delete" data-index="' + index + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
                    '</button>' +
                '</div>' +
                '<span class="oc-bet-match-name">' + escapeHtml(item.matchName) + '</span>' +
                '<span class="oc-bet-selection">' + escapeHtml(item.bookmakerName || 'Best odds') + '</span>' +
            '</div>' +
            '<div class="oc-bet-odds-value">' +
                '<span class="odds-label">Odds</span>' +
                '<span class="odds-number">' + item.odds + '</span>' +
            '</div>' +
        '</div>';
    }).join('');
}

function openCouponPopup() {
    const overlay = document.querySelector('.oc-coupon-popup-overlay');
    if (overlay) {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateCouponUI();
    }
}

function closeCouponPopup() {
    const overlay = document.querySelector('.oc-coupon-popup-overlay');
    if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function placeBet() {
    if (couponItems.length === 0) {
        showToast('Please add selections to your coupon');
        return;
    }

    // Get selected bookmaker
    const selectedBookmaker = document.querySelector('.oc-bookmaker-option-card.selected');
    const bookmakerId = selectedBookmaker ? selectedBookmaker.dataset.bookmakerId : 0;

    // In a real implementation, this would redirect to the bookmaker
    showToast('Redirecting to place your bet...');

    // Simulate redirect
    setTimeout(function() {
        if (bookmakerId) {
            console.log('Placing bet with bookmaker:', bookmakerId);
        }
        // For demo purposes, just show the selections
        console.log('Bets:', couponItems);
    }, 1000);
}

// Save coupon state to localStorage
function saveCouponState() {
    try {
        localStorage.setItem('oc_coupon', JSON.stringify(couponItems));
    } catch (e) {
        console.log('Failed to save coupon state');
    }
}

// Load coupon state from localStorage
function loadCouponState() {
    try {
        var saved = localStorage.getItem('oc_coupon');
        if (saved) {
            couponItems = JSON.parse(saved);
            updateCouponUI();
            restoreSelections();
        }
    } catch (e) {
        console.log('Failed to load coupon state');
    }
}

// Restore visual selection state
function restoreSelections() {
    couponItems.forEach(function(item) {
        const card = document.querySelector('.oc-match-card-horizontal[data-match-id="' + item.matchId + '"]');
        if (card) {
            const btn = card.querySelector('.oc-odd-btn-compact[data-selection="' + item.selection + '"]');
            if (btn) {
                btn.classList.add('selected');
            }
        }
    });
}

// Toast notification
function showToast(message) {
    // Remove existing toast
    var existingToast = document.querySelector('.oc-toast');
    if (existingToast) {
        existingToast.remove();
    }

    // Create toast
    var toast = document.createElement('div');
    toast.className = 'oc-toast';
    toast.innerHTML =
        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
        '<span class="oc-toast-message">' + escapeHtml(message) + '</span>';

    document.body.appendChild(toast);

    // Show
    setTimeout(function() {
        toast.classList.add('show');
    }, 10);

    // Hide after 3 seconds
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

/**
 * ============================================
 * INITIALIZE ALL HOMEPAGE FUNCTIONS
 * ============================================
 */
document.addEventListener('DOMContentLoaded', function() {
    initBannerSlider();
    initCoupon();
    loadCouponState();
});

