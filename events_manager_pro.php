<?php
/**
 * Plugin Name: Event Manager Pro
 * Plugin URI: https://example.com/event-manager
 * Description: A comprehensive event management system demonstrating CPTs, custom endpoints, and third-party integrations
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: event-manager-pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
    }

// Define plugin constants
define('EMP_VERSION', '1.0.0');
define('EMP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMP_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Event_Manager_Pro
    {

    private static $instance = null;

    public static function get_instance()
        {
        if (null === self::$instance) {
            self::$instance = new self();
            }
        return self::$instance;
        }

    private function __construct()
        {
        add_action('init', [$this, 'register_event_post_type']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('rest_api_init', [$this, 'register_api_endpoints']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('add_meta_boxes', [$this, 'add_event_meta_boxes']);
        add_action('save_post_event', [$this, 'save_event_meta'], 10, 2);
        add_filter('template_include', [$this, 'event_template']);

        // AJAX handlers
        add_action('wp_ajax_submit_event', [$this, 'handle_event_submission']);
        add_action('wp_ajax_nopriv_submit_event', [$this, 'handle_event_submission']);
        add_action('pre_get_posts', [$this, 'filter_event_archive']);

        //shortcodes
        add_shortcode('event_submission_form', [$this, 'emp_submission_form_shortcode']);
        add_shortcode('upcoming_events', [$this, 'emp_upcoming_events_shortcode']);
        add_shortcode('event_calendar', [$this, 'emp_event_calendar_shortcode']);


        }

    /**
     * Register Custom Post Type
     */
    public function register_event_post_type()
        {
        $labels = [
            'name' => __('Events', 'event-manager-pro'),
            'singular_name' => __('Event', 'event-manager-pro'),
            'add_new' => __('Add New Event', 'event-manager-pro'),
            'add_new_item' => __('Add New Event', 'event-manager-pro'),
            'edit_item' => __('Edit Event', 'event-manager-pro'),
            'new_item' => __('New Event', 'event-manager-pro'),
            'view_item' => __('View Event', 'event-manager-pro'),
            'search_items' => __('Search Events', 'event-manager-pro'),
            'not_found' => __('No events found', 'event-manager-pro'),
            'menu_name' => __('Events', 'event-manager-pro'),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'events'],
            'show_in_rest' => true,
            'rest_base' => 'events',
        ];

        register_post_type('event', $args);
        }

    /**
     * Register Custom Taxonomies
     */
    public function register_taxonomies()
        {
        // Event Category
        register_taxonomy('event_category', 'event', [
            'label' => __('Event Categories', 'event-manager-pro'),
            'rewrite' => ['slug' => 'event-category'],
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);

        // Event Tags
        register_taxonomy('event_tag', 'event', [
            'label' => __('Event Tags', 'event-manager-pro'),
            'rewrite' => ['slug' => 'event-tag'],
            'hierarchical' => false,
            'show_in_rest' => true,
        ]);
        }

    /**
     * Add Meta Boxes
     */
    public function add_event_meta_boxes()
        {
        add_meta_box(
            'event_details',
            __('Event Details', 'event-manager-pro'),
            [$this, 'render_event_meta_box'],
            'event',
            'normal',
            'high'
        );
        }

    /**
     * Render Event Meta Box
     */
    public function render_event_meta_box($post)
        {
        wp_nonce_field('event_meta_box', 'event_meta_nonce');

        $start_date = get_post_meta($post->ID, '_event_start_date', true);
        $end_date = get_post_meta($post->ID, '_event_end_date', true);
        $location = get_post_meta($post->ID, '_event_location', true);
        $max_attendees = get_post_meta($post->ID, '_event_max_attendees', true);
        $price = get_post_meta($post->ID, '_event_price', true);
        $organizer_email = get_post_meta($post->ID, '_event_organizer_email', true);
        ?>
        <div style="display: grid; gap: 15px;">
            <div>
                <label><strong><?php _e('Start Date & Time:', 'event-manager-pro'); ?></strong></label><br>
                <input type="datetime-local" name="event_start_date" value="<?php echo esc_attr($start_date); ?>"
                    style="width: 100%;">
            </div>

            <div>
                <label><strong><?php _e('End Date & Time:', 'event-manager-pro'); ?></strong></label><br>
                <input type="datetime-local" name="event_end_date" value="<?php echo esc_attr($end_date); ?>"
                    style="width: 100%;">
            </div>

            <div>
                <label><strong><?php _e('Location:', 'event-manager-pro'); ?></strong></label><br>
                <input type="text" name="event_location" value="<?php echo esc_attr($location); ?>" style="width: 100%;">
            </div>

            <div>
                <label><strong><?php _e('Max Attendees:', 'event-manager-pro'); ?></strong></label><br>
                <input type="number" name="event_max_attendees" value="<?php echo esc_attr($max_attendees); ?>"
                    style="width: 100%;">
            </div>

            <div>
                <label><strong><?php _e('Price:', 'event-manager-pro'); ?></strong></label><br>
                <input type="text" name="event_price" value="<?php echo esc_attr($price); ?>" placeholder="Free"
                    style="width: 100%;">
            </div>

            <div>
                <label><strong><?php _e('Organizer Email:', 'event-manager-pro'); ?></strong></label><br>
                <input type="email" name="event_organizer_email" value="<?php echo esc_attr($organizer_email); ?>"
                    style="width: 100%;">
            </div>
        </div>
        <?php
        }

    /**
     * Save Event Meta
     */
    public function save_event_meta($post_id, $post)
        {
        if (!isset($_POST['event_meta_nonce']) || !wp_verify_nonce($_POST['event_meta_nonce'], 'event_meta_box')) {
            return;
            }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
            }

        if (!current_user_can('edit_post', $post_id)) {
            return;
            }

        $fields = [
            'event_start_date',
            'event_end_date',
            'event_location',
            'event_max_attendees',
            'event_price',
            'event_organizer_email',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
                }
            }
        }

    /**
     * Register REST API Endpoints
     */
    public function register_api_endpoints()
        {
        // Submit event endpoint
        register_rest_route('event-manager/v1', '/submit', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_api_submission'],
            'permission_callback' => '__return_true',
        ]);

        // Get upcoming events
        register_rest_route('event-manager/v1', '/upcoming', [
            'methods' => 'GET',
            'callback' => [$this, 'get_upcoming_events'],
            'permission_callback' => '__return_true',
        ]);

        // Register for event
        register_rest_route('event-manager/v1', '/register/(?P<id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'register_for_event'],
            'permission_callback' => '__return_true',
        ]);
        }

    /**
     * Handle API Event Submission
     */
    public function handle_api_submission($request)
        {
        $params = $request->get_json_params();

        // Validate required fields
        if (empty($params['title']) || empty($params['start_date'])) {
            return new WP_Error('missing_fields', 'Title and start date are required', ['status' => 400]);
            }

        // Create event
        $event_data = [
            'post_title' => sanitize_text_field($params['title']),
            'post_content' => wp_kses_post($params['description'] ?? ''),
            'post_status' => 'pending',
            'post_type' => 'event',
        ];

        $event_id = wp_insert_post($event_data);

        if (is_wp_error($event_id)) {
            return $event_id;
            }

        // Save meta data
        update_post_meta($event_id, '_event_start_date', sanitize_text_field($params['start_date']));
        update_post_meta($event_id, '_event_end_date', sanitize_text_field($params['end_date'] ?? ''));
        update_post_meta($event_id, '_event_location', sanitize_text_field($params['location'] ?? ''));
        update_post_meta($event_id, '_event_organizer_email', sanitize_email($params['email'] ?? ''));

        // Send notification email
        $this->send_submission_notification($event_id, $params);

        // Trigger n8n webhook
        $this->trigger_n8n_webhook('event_submitted', [
            'event_id' => $event_id,
            'title' => $params['title'],
            'email' => $params['email'] ?? '',
        ]);

        return [
            'success' => true,
            'event_id' => $event_id,
            'message' => 'Event submitted successfully and is pending review',
        ];
        }

    /**
     * Get Upcoming Events
     */
    public function get_upcoming_events($request)
        {
        $args = [
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'meta_key' => '_event_start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => [
                [
                    'key' => '_event_start_date',
                    'value' => current_time('Y-m-d H:i:s'),
                    'compare' => '>=',
                    'type' => 'DATETIME',
                ],
            ],
        ];

        $events = get_posts($args);
        $result = [];

        foreach ($events as $event) {
            $result[] = [
                'id' => $event->ID,
                'title' => $event->post_title,
                'description' => $event->post_excerpt,
                'start_date' => get_post_meta($event->ID, '_event_start_date', true),
                'location' => get_post_meta($event->ID, '_event_location', true),
                'permalink' => get_permalink($event->ID),
            ];
            }

        return $result;
        }

    /**
     * Register for Event
     */
    public function register_for_event($request)
        {
        $event_id = $request['id'];
        $params = $request->get_json_params();

        if (!get_post($event_id) || get_post_type($event_id) !== 'event') {
            return new WP_Error('invalid_event', 'Event not found', ['status' => 404]);
            }

        $email = sanitize_email($params['email'] ?? '');
        $name = sanitize_text_field($params['name'] ?? '');

        if (empty($email) || empty($name)) {
            return new WP_Error('missing_fields', 'Name and email are required', ['status' => 400]);
            }

        // Store registration (in production, use a proper database table)
        $registrations = get_post_meta($event_id, '_event_registrations', true) ?: [];
        $registrations[] = [
            'name' => $name,
            'email' => $email,
            'registered_at' => current_time('mysql'),
        ];
        update_post_meta($event_id, '_event_registrations', $registrations);

        // Send confirmation email
        $this->send_registration_confirmation($event_id, $name, $email);

        // Trigger n8n webhook
        $this->trigger_n8n_webhook('event_registration', [
            'event_id' => $event_id,
            'event_title' => get_the_title($event_id),
            'attendee_name' => $name,
            'attendee_email' => $email,
        ]);

        return [
            'success' => true,
            'message' => 'Registration successful! Check your email for confirmation.',
        ];
        }

    /**
     * Send Submission Notification
     */
    private function send_submission_notification($event_id, $params)
        {
        $admin_email = get_option('admin_email');
        $subject = 'New Event Submission: ' . $params['title'];
        $message = "A new event has been submitted:\n\n";
        $message .= "Title: " . $params['title'] . "\n";
        $message .= "Start Date: " . $params['start_date'] . "\n";
        $message .= "Location: " . ($params['location'] ?? 'N/A') . "\n\n";
        $message .= "Review it here: " . admin_url('post.php?post=' . $event_id . '&action=edit');

        wp_mail($admin_email, $subject, $message);
        }

    /**
     * Send Registration Confirmation
     */
    private function send_registration_confirmation($event_id, $name, $email)
        {
        $event_title = get_the_title($event_id);
        $start_date = get_post_meta($event_id, '_event_start_date', true);
        $location = get_post_meta($event_id, '_event_location', true);

        $subject = 'Event Registration Confirmation: ' . $event_title;
        $message = "Hi $name,\n\n";
        $message .= "Thank you for registering for: $event_title\n\n";
        $message .= "Event Details:\n";
        $message .= "Date: $start_date\n";
        $message .= "Location: $location\n\n";
        $message .= "We look forward to seeing you there!\n";

        wp_mail($email, $subject, $message);
        }

    /**
     * Trigger n8n Webhook
     */
    private function trigger_n8n_webhook($event_type, $data)
        {
        $webhook_url = get_option('emp_n8n_webhook_url');

        if (empty($webhook_url)) {
            return;
            }

        $payload = [
            'event_type' => $event_type,
            'timestamp' => current_time('c'),
            'site_url' => get_site_url(),
            'data' => $data,
        ];

        wp_remote_post($webhook_url, [
            'body' => json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 5,
        ]);
        }

    /**
     * Enqueue Scripts
     */
    public function enqueue_scripts()
        {
        if (is_singular('event') || is_post_type_archive('event')) {
            wp_enqueue_style('emp-styles', EMP_PLUGIN_URL . 'assets/css/styles.css', [], EMP_VERSION);
            wp_enqueue_script('emp-scripts', EMP_PLUGIN_URL . 'assets/js/scripts.js', ['jquery'], EMP_VERSION, true);

            wp_localize_script('emp-scripts', 'empData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'restUrl' => rest_url('event-manager/v1'),
                'nonce' => wp_create_nonce('emp_nonce'),
            ]);
            }
        }

    /**
     * Custom Template - Handle both single and archive templates
     */
    public function event_template($template)
        {
        global $post;
        // Single event template
        if (is_singular('event')) {
            $custom_template = EMP_PLUGIN_DIR . 'templates/single-event.php';
            if (file_exists($custom_template)) {
                return $custom_template;
                }
            }

        // Archive template (for post type archive and taxonomy archives)
        if (is_post_type_archive('event') || is_tax('event_category') || is_tax('event_tag')) {
            $custom_template = EMP_PLUGIN_DIR . 'templates/archive-event.php';
            if (file_exists($custom_template)) {
                return $custom_template;
                }
            }

        return $template;
        }

    /**
     * AJAX Handler
     */
    public function handle_event_submission()
        {
        check_ajax_referer('emp_nonce', 'nonce');

        // Process similar to API endpoint
        $response = $this->handle_api_submission(new WP_REST_Request('POST'));

        wp_send_json($response);
        }

    /**
     * Filter Event Archive Query
     */
    public function filter_event_archive($query)
        {
        if (!is_admin() && $query->is_main_query() && (is_post_type_archive('event') || is_tax(['event_category', 'event_tag']))) {

            // Date filtering
            if (isset($_GET['date_filter'])) {
                $date_filter = sanitize_text_field($_GET['date_filter']);
                $today = current_time('Y-m-d H:i:s');

                switch ($date_filter) {
                    case 'upcoming':
                        $query->set('meta_key', '_event_start_date');
                        $query->set('orderby', 'meta_value');
                        $query->set('order', 'ASC');
                        $query->set('meta_query', [
                            [
                                'key' => '_event_start_date',
                                'value' => $today,
                                'compare' => '>=',
                                'type' => 'DATETIME',
                            ],
                        ]);
                        break;

                    case 'this-week':
                        $week_start = date('Y-m-d 00:00:00', strtotime('this week'));
                        $week_end = date('Y-m-d 23:59:59', strtotime('this week +6 days'));
                        $query->set('meta_query', [
                            [
                                'key' => '_event_start_date',
                                'value' => [$week_start, $week_end],
                                'compare' => 'BETWEEN',
                                'type' => 'DATETIME',
                            ],
                        ]);
                        break;

                    case 'this-month':
                        $month_start = date('Y-m-01 00:00:00');
                        $month_end = date('Y-m-t 23:59:59');
                        $query->set('meta_query', [
                            [
                                'key' => '_event_start_date',
                                'value' => [$month_start, $month_end],
                                'compare' => 'BETWEEN',
                                'type' => 'DATETIME',
                            ],
                        ]);
                        break;

                    case 'past':
                        $query->set('meta_key', '_event_start_date');
                        $query->set('orderby', 'meta_value');
                        $query->set('order', 'DESC');
                        $query->set('meta_query', [
                            [
                                'key' => '_event_start_date',
                                'value' => $today,
                                'compare' => '<',
                                'type' => 'DATETIME',
                            ],
                        ]);
                        break;
                    }
                } else {
                // Default: show upcoming events
                $query->set('meta_key', '_event_start_date');
                $query->set('orderby', 'meta_value');
                $query->set('order', 'ASC');
                }

            // Category filtering (if on main archive, not on category page)
            if (is_post_type_archive('event') && isset($_GET['event_category'])) {
                $category = sanitize_text_field($_GET['event_category']);
                if (!empty($category)) {
                    $query->set('tax_query', [
                        [
                            'taxonomy' => 'event_category',
                            'field' => 'slug',
                            'terms' => $category,
                        ],
                    ]);
                    }
                }
            }
        }

    // ============================================
    // SHORTCODES
    // ============================================

    /**
     * Event Submission Form Shortcode
     * Usage: [event_submission_form]
     */
    public function emp_submission_form_shortcode($atts)
        {
        $atts = shortcode_atts([
            'redirect' => '',
            'button_text' => 'Submit Event',
            'success_message' => 'Thank you! Your event has been submitted for review.',
        ], $atts);

        ob_start();
        ?>
        <div class="emp-submission-form-wrapper">
            <style>
                .emp-submission-form-wrapper {
                    max-width: 800px;
                    margin: 40px auto;
                    padding: 40px;
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }

                .emp-form-group {
                    margin-bottom: 20px;
                }

                .emp-form-group label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 600;
                    color: #333;
                }

                .emp-form-group input,
                .emp-form-group textarea,
                .emp-form-group select {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #e0e0e0;
                    border-radius: 6px;
                    font-size: 16px;
                    transition: border-color 0.3s;
                }

                .emp-form-group input:focus,
                .emp-form-group textarea:focus,
                .emp-form-group select:focus {
                    outline: none;
                    border-color: #667eea;
                }

                .emp-form-group textarea {
                    min-height: 120px;
                    resize: vertical;
                }

                .emp-submit-btn {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    padding: 15px 40px;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: transform 0.2s;
                }

                .emp-submit-btn:hover {
                    transform: translateY(-2px);
                }

                .emp-submit-btn:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                    transform: none;
                }

                .emp-alert {
                    padding: 15px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                }

                .emp-alert-success {
                    background: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }

                .emp-alert-error {
                    background: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }

                .emp-required {
                    color: #e74c3c;
                }

                .emp-form-row {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                }

                @media (max-width: 768px) {
                    .emp-form-row {
                        grid-template-columns: 1fr;
                    }

                    .emp-submission-form-wrapper {
                        padding: 20px;
                    }
                }
            </style>

            <form id="emp-event-submission-form" class="emp-submission-form">
                <div id="emp-form-message"></div>

                <div class="emp-form-group">
                    <label for="event_title">
                        Event Title <span class="emp-required">*</span>
                    </label>
                    <input type="text" id="event_title" name="title" required>
                </div>

                <div class="emp-form-group">
                    <label for="event_description">
                        Event Description <span class="emp-required">*</span>
                    </label>
                    <textarea id="event_description" name="description" required></textarea>
                </div>

                <div class="emp-form-row">
                    <div class="emp-form-group">
                        <label for="event_start_date">
                            Start Date & Time <span class="emp-required">*</span>
                        </label>
                        <input type="datetime-local" id="event_start_date" name="start_date" required>
                    </div>

                    <div class="emp-form-group">
                        <label for="event_end_date">End Date & Time</label>
                        <input type="datetime-local" id="event_end_date" name="end_date">
                    </div>
                </div>

                <div class="emp-form-group">
                    <label for="event_location">
                        Location <span class="emp-required">*</span>
                    </label>
                    <input type="text" id="event_location" name="location" required
                        placeholder="e.g., 123 Main St, City, Country">
                </div>

                <div class="emp-form-row">
                    <div class="emp-form-group">
                        <label for="event_category">Event Category</label>
                        <select id="event_category" name="category">
                            <option value="">Select a category...</option>
                            <?php
                            $categories = get_terms(['taxonomy' => 'event_category', 'hide_empty' => false]);
                            foreach ($categories as $category) {
                                echo '<option value="' . esc_attr($category->term_id) . '">'
                                    . esc_html($category->name) . '</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <div class="emp-form-group">
                        <label for="event_price">Price</label>
                        <input type="text" id="event_price" name="price" placeholder="Free or $20">
                    </div>
                </div>

                <div class="emp-form-row">
                    <div class="emp-form-group">
                        <label for="event_max_attendees">Maximum Attendees</label>
                        <input type="number" id="event_max_attendees" name="max_attendees" min="1"
                            placeholder="Leave empty for unlimited">
                    </div>

                    <div class="emp-form-group">
                        <label for="organizer_email">
                            Your Email <span class="emp-required">*</span>
                        </label>
                        <input type="email" id="organizer_email" name="email" required>
                    </div>
                </div>

                <div class="emp-form-group">
                    <button type="submit" class="emp-submit-btn">
                        <?php echo esc_html($atts['button_text']); ?>
                    </button>
                </div>
            </form>

            <script>
                (function () {
                    const form = document.getElementById('emp-event-submission-form');
                    const messageDiv = document.getElementById('emp-form-message');
                    const submitBtn = form.querySelector('.emp-submit-btn');

                    form.addEventListener('submit', async function (e) {
                        e.preventDefault();

                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Submitting...';
                        messageDiv.innerHTML = '';

                        const formData = new FormData(form);
                        const data = Object.fromEntries(formData.entries());

                        try {
                            const response = await fetch('<?php echo rest_url('event-manager/v1/submit'); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(data)
                            });

                            const result = await response.json();

                            if (result.success) {
                                messageDiv.innerHTML = '<div class="emp-alert emp-alert-success">' +
                                    '<?php echo esc_js($atts['success_message']); ?>' +
                                    '</div>';
                                form.reset();

                                <?php if (!empty($atts['redirect'])): ?>
                                    setTimeout(() => {
                                        window.location.href = '<?php echo esc_url($atts['redirect']); ?>';
                                    }, 2000);
                                <?php endif; ?>
                            } else {
                                messageDiv.innerHTML = '<div class="emp-alert emp-alert-error">' +
                                    (result.message || 'Submission failed. Please try again.') +
                                    '</div>';
                            }
                        } catch (error) {
                            messageDiv.innerHTML = '<div class="emp-alert emp-alert-error">' +
                                'An error occurred. Please try again.' +
                                '</div>';
                        } finally {
                            submitBtn.disabled = false;
                            submitBtn.textContent = '<?php echo esc_js($atts['button_text']); ?>';
                        }
                    });
                })();
            </script>
        </div>
        <?php
        return ob_get_clean();
        }

    /**
     * Upcoming Events List Shortcode
     * Usage: [upcoming_events limit="5" category="conference"]
     */
    public function emp_upcoming_events_shortcode($atts)
        {
        $atts = shortcode_atts([
            'limit' => 10,
            'category' => '',
            'show_image' => 'yes',
            'show_excerpt' => 'yes',
        ], $atts);

        $args = [
            'post_type' => 'event',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['limit']),
            'meta_key' => '_event_start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => [
                [
                    'key' => '_event_start_date',
                    'value' => current_time('Y-m-d H:i:s'),
                    'compare' => '>=',
                    'type' => 'DATETIME',
                ],
            ],
        ];

        if (!empty($atts['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'event_category',
                    'field' => 'slug',
                    'terms' => $atts['category'],
                ],
            ];
            }

        $events = new WP_Query($args);

        if (!$events->have_posts()) {
            return '<p>No upcoming events found.</p>';
            }

        ob_start();
        ?>
        <div class="emp-upcoming-events">
            <style>
                .emp-upcoming-events {
                    display: grid;
                    gap: 30px;
                    margin: 40px 0;
                }

                .emp-event-card {
                    display: grid;
                    grid-template-columns: 200px 1fr;
                    gap: 20px;
                    background: white;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s;
                }

                .emp-event-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
                }

                .emp-event-image {
                    width: 200px;
                    height: 200px;
                    object-fit: cover;
                }

                .emp-event-content {
                    padding: 20px 20px 20px 0;
                }

                .emp-event-title {
                    margin: 0 0 10px 0;
                    font-size: 1.5em;
                    color: #333;
                }

                .emp-event-title a {
                    color: #667eea;
                    text-decoration: none;
                }

                .emp-event-title a:hover {
                    text-decoration: underline;
                }

                .emp-event-meta {
                    display: flex;
                    gap: 20px;
                    flex-wrap: wrap;
                    margin: 10px 0;
                    font-size: 0.9em;
                    color: #666;
                }

                .emp-event-meta-item {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }

                .emp-event-excerpt {
                    margin: 15px 0;
                    color: #555;
                    line-height: 1.6;
                }

                .emp-event-link {
                    display: inline-block;
                    background: #667eea;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 600;
                    transition: background 0.3s;
                }

                .emp-event-link:hover {
                    background: #764ba2;
                }

                @media (max-width: 768px) {
                    .emp-event-card {
                        grid-template-columns: 1fr;
                    }

                    .emp-event-image {
                        width: 100%;
                    }

                    .emp-event-content {
                        padding: 20px;
                    }
                }
            </style>

            <?php while ($events->have_posts()):
                $events->the_post(); ?>
                <?php
                $event_id = get_the_ID();
                $start_date = get_post_meta($event_id, '_event_start_date', true);
                $location = get_post_meta($event_id, '_event_location', true);
                $price = get_post_meta($event_id, '_event_price', true);
                ?>

                <div class="emp-event-card">
                    <?php if ($atts['show_image'] === 'yes' && has_post_thumbnail()): ?>
                        <img src="<?php echo get_the_post_thumbnail_url($event_id, 'medium'); ?>" alt="<?php the_title_attribute(); ?>"
                            class="emp-event-image">
                    <?php endif; ?>

                    <div class="emp-event-content">
                        <h3 class="emp-event-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <div class="emp-event-meta">
                            <?php if ($start_date): ?>
                                <div class="emp-event-meta-item">
                                    📅 <?php echo date_i18n('M j, Y @ g:i a', strtotime($start_date)); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($location): ?>
                                <div class="emp-event-meta-item">
                                    📍 <?php echo esc_html($location); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($price): ?>
                                <div class="emp-event-meta-item">
                                    💰 <?php echo esc_html($price); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($atts['show_excerpt'] === 'yes'): ?>
                            <div class="emp-event-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 30); ?>
                            </div>
                        <?php endif; ?>

                        <a href="<?php the_permalink(); ?>" class="emp-event-link">
                            View Details →
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
        }

    /**
     * Event Calendar Shortcode
     * Usage: [event_calendar]
     */
    public function emp_event_calendar_shortcode($atts)
        {
        $atts = shortcode_atts([
            'height' => 'auto',
            'default_view' => 'calendar', // 'calendar' or 'list'
        ], $atts);

        // Enqueue calendar assets
        wp_enqueue_style('emp-calendar', EMP_PLUGIN_URL . 'assets/css/calendar.css', [], EMP_VERSION);
        wp_enqueue_script('emp-calendar', EMP_PLUGIN_URL . 'assets/js/calendar.js', ['jquery'], EMP_VERSION, true);

        wp_localize_script('emp-calendar', 'empCalendar', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('emp_calendar'),
            'restUrl' => rest_url('event-manager/v1'),
            'defaultView' => $atts['default_view'],
        ]);

        $style = '';
        if ($atts['height'] !== 'auto') {
            $style = 'style="min-height: ' . esc_attr($atts['height']) . ';"';
            }

        return '<div id="emp-event-calendar" class="emp-calendar-wrapper" ' . $style . '></div>';
        }

    }

// Initialize plugin
function emp_init()
    {
    return Event_Manager_Pro::get_instance();
    }
add_action('plugins_loaded', 'emp_init');

// Activation hook
register_activation_hook(__FILE__, function () {
    emp_init();
    flush_rewrite_rules();
    });

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
    });

/**
 * Settings Page
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=event',
        'Settings',
        'Settings',
        'manage_options',
        'event-manager-settings',
        'emp_settings_page'
    );
    });

function emp_settings_page()
    {
    if (isset($_POST['emp_save_settings'])) {
        check_admin_referer('emp_settings');
        update_option('emp_n8n_webhook_url', sanitize_url($_POST['n8n_webhook_url']));
        update_option('emp_sendgrid_api_key', sanitize_text_field($_POST['sendgrid_api_key']));
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }

    $webhook_url = get_option('emp_n8n_webhook_url', '');
    $sendgrid_key = get_option('emp_sendgrid_api_key', '');
    ?>
    <div class="wrap">
        <h1>Event Manager Settings</h1>
        <form method="post">
            <?php wp_nonce_field('emp_settings'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="n8n_webhook_url">n8n Webhook URL</label></th>
                    <td>
                        <input type="url" name="n8n_webhook_url" id="n8n_webhook_url"
                            value="<?php echo esc_attr($webhook_url); ?>" class="regular-text">
                        <p class="description">Enter your n8n webhook URL for automation triggers</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="sendgrid_api_key">SendGrid API Key</label></th>
                    <td>
                        <input type="text" name="sendgrid_api_key" id="sendgrid_api_key"
                            value="<?php echo esc_attr($sendgrid_key); ?>" class="regular-text">
                        <p class="description">Optional: SendGrid API key for enhanced email delivery</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="emp_save_settings" class="button button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
    }