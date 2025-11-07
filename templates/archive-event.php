<?php
/**
 * Archive Event Template
 */

    get_header();
    ?>
    
    <style>
        .emp-archive-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .emp-archive-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .emp-archive-title {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }
        .emp-archive-description {
            font-size: 1.2em;
            color: #666;
        }
        .emp-filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .emp-filter-group {
            flex: 1;
            min-width: 200px;
        }
        .emp-filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .emp-filter-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        .emp-events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        .emp-archive-event-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .emp-archive-event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .emp-archive-event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .emp-archive-event-body {
            padding: 20px;
        }
        .emp-archive-event-title {
            font-size: 1.3em;
            margin: 0 0 15px 0;
        }
        .emp-archive-event-title a {
            color: #333;
            text-decoration: none;
        }
        .emp-archive-event-title a:hover {
            color: #3fae38;
        }
        .emp-archive-event-date {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .emp-archive-event-location {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        .emp-archive-event-excerpt {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .emp-archive-event-link {
            display: inline-block;
            background: linear-gradient(135deg, #3fae38 0%, #3d003b 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .emp-archive-event-link:hover {
            transform: translateX(5px);
        }
        .emp-pagination {
            text-align: center;
            margin: 40px 0;
        }
        .emp-no-events {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
        }
        .emp-no-events h3 {
            font-size: 1.5em;
            color: #666;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .emp-events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    
    <div class="emp-archive-container">
        <div class="emp-archive-header">
            <h1 class="emp-archive-title">
                <?php 
                if (is_tax()) {
                    single_term_title();
                } else {
                    echo 'Events';
                }
                ?>
            </h1>
            <?php if (is_tax() && term_description()) : ?>
                <p class="emp-archive-description"><?php echo term_description(); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="emp-filter-bar">
            <div class="emp-filter-group">
                <label for="emp-filter-category">Category</label>
                <select id="emp-filter-category">
                    <option value="">All Categories</option>
                    <?php
                    $categories = get_terms(['taxonomy' => 'event_category', 'hide_empty' => true]);
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->slug) . '">' 
                             . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="emp-filter-group">
                <label for="emp-filter-date">When</label>
                <select id="emp-filter-date">
                    <option value="upcoming">Upcoming</option>
                    <option value="this-week">This Week</option>
                    <option value="this-month">This Month</option>
                    <option value="past">Past Events</option>
                </select>
            </div>
        </div>
        
        <?php if (have_posts()) : ?>
            <div class="emp-events-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $event_id = get_the_ID();
                    $start_date = get_post_meta($event_id, '_event_start_date', true);
                    $location = get_post_meta($event_id, '_event_location', true);
                    ?>
                    
                    <article class="emp-archive-event-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php echo get_the_post_thumbnail_url($event_id, 'medium_large'); ?>" 
                                 alt="<?php the_title_attribute(); ?>" 
                                 class="emp-archive-event-image">
                        <?php endif; ?>
                        
                        <div class="emp-archive-event-body">
                            <h2 class="emp-archive-event-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <?php if ($start_date) : ?>
                                <div class="emp-archive-event-date">
                                    📅 <?php echo date_i18n('F j, Y @ g:i a', strtotime($start_date)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($location) : ?>
                                <div class="emp-archive-event-location">
                                    📍 <?php echo esc_html($location); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="emp-archive-event-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="emp-archive-event-link">
                                Learn More →
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <div class="emp-pagination">
                <?php
                the_posts_pagination([
                    'mid_size' => 2,
                    'prev_text' => '← Previous',
                    'next_text' => 'Next →',
                ]);
                ?>
            </div>
        <?php else : ?>
            <div class="emp-no-events">
                <h3>No events found</h3>
                <p>Check back soon for upcoming events!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    get_footer();

