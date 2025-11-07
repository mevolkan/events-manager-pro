<?php
/**
 * Single Event Template
 * 
 * This template can be overridden by copying it to:
 * yourtheme/event-manager-pro/single-event.php
 */

get_header();

// Get event meta data
$event_id = get_the_ID();
$start_date = get_post_meta($event_id, '_event_start_date', true);
$end_date = get_post_meta($event_id, '_event_end_date', true);
$location = get_post_meta($event_id, '_event_location', true);
$max_attendees = get_post_meta($event_id, '_event_max_attendees', true);
$price = get_post_meta($event_id, '_event_price', true);
$organizer_email = get_post_meta($event_id, '_event_organizer_email', true);
$registrations = get_post_meta($event_id, '_event_registrations', true) ?: [];

// Check if event is past
$is_past = strtotime($start_date) < current_time('timestamp');
$spots_left = $max_attendees ? $max_attendees - count($registrations) : null;
?>

<style>
    .event-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .event-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 40px;
        border-radius: 12px;
        margin-bottom: 40px;
    }
    
    .event-title {
        font-size: 2.5em;
        margin: 0 0 20px 0;
    }
    
    .event-meta {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        font-size: 1.1em;
        opacity: 0.95;
    }
    
    .event-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .event-content-wrapper {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }
    
    @media (max-width: 768px) {
        .event-content-wrapper {
            grid-template-columns: 1fr;
        }
    }
    
    .event-main-content {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .event-sidebar {
        position: sticky;
        top: 20px;
        height: fit-content;
    }
    
    .sidebar-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .sidebar-card h3 {
        margin: 0 0 20px 0;
        color: #667eea;
        font-size: 1.3em;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #666;
    }
    
    .info-value {
        color: #333;
    }
    
    .register-form {
        margin-top: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 1em;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 1.1em;
        cursor: pointer;
        width: 100%;
        font-weight: 600;
        transition: transform 0.2s;
    }
    
    .btn:hover {
        transform: translateY(-2px);
    }
    
    .btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .alert {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 600;
    }
    
    .badge-past {
        background: #e0e0e0;
        color: #666;
    }
    
    .badge-upcoming {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-sold-out {
        background: #f8d7da;
        color: #721c24;
    }
    
    .featured-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .event-categories {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .category-badge {
        background: #f0f0f0;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.9em;
        color: #667eea;
        text-decoration: none;
        transition: background 0.3s;
    }
    
    .category-badge:hover {
        background: #667eea;
        color: white;
    }
</style>

<div class="event-container">
    <?php while (have_posts()) : the_post(); ?>
        
        <div class="event-header">
            <h1 class="event-title"><?php the_title(); ?></h1>
            
            <div class="event-meta">
                <?php if ($start_date) : ?>
                    <div class="event-meta-item">
                        📅 <?php echo date_i18n('F j, Y @ g:i a', strtotime($start_date)); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($location) : ?>
                    <div class="event-meta-item">
                        📍 <?php echo esc_html($location); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($price) : ?>
                    <div class="event-meta-item">
                        💰 <?php echo esc_html($price); ?>
                    </div>
                <?php else : ?>
                    <div class="event-meta-item">
                        💰 Free
                    </div>
                <?php endif; ?>
                
                <?php if ($is_past) : ?>
                    <span class="badge badge-past">Past Event</span>
                <?php else : ?>
                    <span class="badge badge-upcoming">Upcoming</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="event-content-wrapper">
            <div class="event-main-content">
                <?php if (has_post_thumbnail()) : ?>
                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>" 
                         alt="<?php the_title_attribute(); ?>" 
                         class="featured-image">
                <?php endif; ?>
                
                <div class="event-description">
                    <?php the_content(); ?>
                </div>
                
                <?php 
                $categories = get_the_terms(get_the_ID(), 'event_category');
                if ($categories && !is_wp_error($categories)) : 
                ?>
                    <div class="event-categories">
                        <strong>Categories:</strong>
                        <?php foreach ($categories as $category) : ?>
                            <a href="<?php echo get_term_link($category); ?>" class="category-badge">
                                <?php echo esc_html($category->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <aside class="event-sidebar">
                <div class="sidebar-card">
                    <h3>Event Details</h3>
                    
                    <?php if ($start_date) : ?>
                        <div class="info-row">
                            <span class="info-label">Start Date</span>
                            <span class="info-value">
                                <?php echo date_i18n('M j, Y', strtotime($start_date)); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Start Time</span>
                            <span class="info-value">
                                <?php echo date_i18n('g:i a', strtotime($start_date)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($end_date) : ?>
                        <div class="info-row">
                            <span class="info-label">End Date</span>
                            <span class="info-value">
                                <?php echo date_i18n('M j, Y @ g:i a', strtotime($end_date)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($max_attendees) : ?>
                        <div class="info-row">
                            <span class="info-label">Capacity</span>
                            <span class="info-value"><?php echo esc_html($max_attendees); ?> people</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Spots Left</span>
                            <span class="info-value">
                                <?php echo $spots_left > 0 ? esc_html($spots_left) : 'Sold Out'; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($organizer_email) : ?>
                        <div class="info-row">
                            <span class="info-label">Organizer</span>
                            <span class="info-value">
                                <a href="mailto:<?php echo esc_attr($organizer_email); ?>">
                                    Contact
                                </a>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$is_past && ($spots_left === null || $spots_left > 0)) : ?>
                    <div class="sidebar-card">
                        <h3>Register for Event</h3>
                        
                        <div id="registration-message"></div>
                        
                        <form id="event-registration-form" class="register-form">
                            <div class="form-group">
                                <label for="attendee_name">Full Name *</label>
                                <input type="text" 
                                       id="attendee_name" 
                                       name="attendee_name" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="attendee_email">Email Address *</label>
                                <input type="email" 
                                       id="attendee_email" 
                                       name="attendee_email" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="attendee_phone">Phone Number</label>
                                <input type="tel" 
                                       id="attendee_phone" 
                                       name="attendee_phone">
                            </div>
                            
                            <button type="submit" class="btn">
                                Register Now
                            </button>
                        </form>
                    </div>
                <?php elseif ($is_past) : ?>
                    <div class="sidebar-card">
                        <div class="alert alert-warning">
                            This event has already taken place.
                        </div>
                    </div>
                <?php elseif ($spots_left === 0) : ?>
                    <div class="sidebar-card">
                        <div class="alert alert-error">
                            <strong>Sold Out!</strong><br>
                            This event has reached maximum capacity.
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="sidebar-card">
                    <h3>Share Event</h3>
                    <div style="display: flex; gap: 10px;">
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(get_the_title() . ' - ' . get_permalink()); ?>" 
                           target="_blank" 
                           class="btn" 
                           style="background: #1DA1F2; font-size: 0.9em; padding: 10px;">
                            Twitter
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                           target="_blank" 
                           class="btn" 
                           style="background: #4267B2; font-size: 0.9em; padding: 10px;">
                            Facebook
                        </a>
                    </div>
                </div>
            </aside>
        </div>
        
    <?php endwhile; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('event-registration-form');
    const messageDiv = document.getElementById('registration-message');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registering...';
            
            const formData = {
                name: document.getElementById('attendee_name').value,
                email: document.getElementById('attendee_email').value,
                phone: document.getElementById('attendee_phone').value,
            };
            
            try {
                const response = await fetch('<?php echo rest_url('event-manager/v1/register/' . get_the_ID()); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">' + 
                        result.message + '</div>';
                    form.reset();
                    
                    // Optionally redirect or hide form
                    setTimeout(() => {
                        form.style.display = 'none';
                    }, 3000);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-error">' + 
                        (result.message || 'Registration failed. Please try again.') + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="alert alert-error">' + 
                    'An error occurred. Please try again.' + '</div>';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register Now';
            }
        });
    }
});
</script>

<?php get_footer(); ?>