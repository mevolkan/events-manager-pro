# Event Manager Pro

A comprehensive WordPress event management plugin with custom post types, REST API endpoints, and beautiful shortcodes for displaying events.

## Features

**Custom Post Type** - Dedicated "Events" post type with custom fields
**Event Calendar** - Beautiful, interactive calendar view with month navigation
**Frontend Submission** - Allow users to submit events from the frontend
**Custom Taxonomies** - Event categories and tags for organization
**REST API** - Full API support for external integrations
**Email Notifications** - Automatic email confirmations and notifications
 **Beautiful Shortcodes** - Pre-styled components ready to use
 **Third-party Integrations** - n8n webhook support for automation

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Events → Settings** to configure webhook URLs (optional)
4. Create your first event!

## File Structure

```
event-manager-pro/
├── event-manager-pro.php      # Main plugin file
├── assets/
│   ├── css/
│   │   ├── styles.css         # General styles
│   │   └── calendar.css       # Calendar-specific styles
│   └── js/
│       ├── scripts.js         # General JavaScript
│       └── calendar.js        # Calendar functionality
└── templates/
    ├── single-event.php       # Single event template
    └── archive-event.php      # Event archive template
```

## Usage

### Shortcodes

#### 1. Event Submission Form

Allow users to submit events from the frontend:

```
[event_submission_form]
```

**Parameters:**

- `redirect` - URL to redirect after successful submission
- `button_text` - Custom button text (default: "Submit Event")
- `success_message` - Custom success message

**Example:**

```
[event_submission_form redirect="/thank-you" button_text="Submit My Event" success_message="Thanks! We'll review your event soon."]
```

#### 2. Upcoming Events List

Display a list of upcoming events:

```
[upcoming_events]
```

**Parameters:**

- `limit` - Number of events to show (default: 10)
- `category` - Filter by category slug
- `show_image` - Show featured images (default: "yes")
- `show_excerpt` - Show event excerpts (default: "yes")

**Example:**

```
[upcoming_events limit="5" category="conference" show_image="yes"]
```

#### 3. Event Calendar

Display an interactive calendar view:

```
[event_calendar]
```

**Parameters:**

- `height` - Minimum height (default: "auto")
- `default_view` - Starting view: "calendar" or "list" (default: "calendar")

**Example:**

```
[event_calendar height="600px" default_view="list"]
```

### REST API Endpoints

Base URL: `/wp-json/event-manager/v1`

#### Get Upcoming Events

```
GET /upcoming
```

**Response:**

```json
[
  {
    "id": 123,
    "title": "WordPress Meetup",
    "description": "Monthly WordPress meetup",
    "start_date": "2024-12-15 18:00:00",
    "location": "123 Main St",
    "permalink": "https://example.com/events/wordpress-meetup"
  }
]
```

#### Submit Event

```
POST /submit
Content-Type: application/json

{
  "title": "My Event",
  "description": "Event description",
  "start_date": "2024-12-15 18:00:00",
  "end_date": "2024-12-15 20:00:00",
  "location": "123 Main St",
  "email": "organizer@example.com"
}
```

#### Register for Event

```
POST /register/{event_id}
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com"
}
```

## Event Meta Fields

Each event includes the following custom fields:

- **Start Date & Time** - When the event begins
- **End Date & Time** - When the event ends
- **Location** - Physical or virtual location
- **Max Attendees** - Maximum number of participants
- **Price** - Event cost (or "Free")
- **Organizer Email** - Contact email for the organizer

## Integration with n8n

Configure webhook automation in **Events → Settings**:

1. Set up a webhook in your n8n workflow
2. Copy the webhook URL
3. Paste it in the plugin settings
4. The plugin will trigger webhooks for:
   - `event_submitted` - New event submission
   - `event_registration` - User registration for event

**Webhook Payload Example:**

```json
{
  "event_type": "event_submitted",
  "timestamp": "2024-11-07T10:30:00+00:00",
  "site_url": "https://example.com",
  "data": {
    "event_id": 123,
    "title": "WordPress Meetup",
    "email": "organizer@example.com"
  }
}
```

## Customization

### Custom Templates

Override templates by copying them to your theme:

```
your-theme/
└── event-manager-pro/
    ├── single-event.php
    └── archive-event.php
```

### Custom Styling

Override plugin styles by adding CSS to your theme:

```css
/* Override calendar colors */
.emp-calendar-day-header {
    background: linear-gradient(135deg, #your-color 0%, #your-color-2 100%);
}

/* Customize event cards */
.emp-event-card {
    border: 2px solid #your-color;
}
```

## Archive Filtering

The event archive supports URL parameters for filtering:

- `?date_filter=upcoming` - Show upcoming events
- `?date_filter=this-week` - Show events this week
- `?date_filter=this-month` - Show events this month
- `?date_filter=past` - Show past events
- `?event_category=slug` - Filter by category

**Example:**

```
https://example.com/events/?date_filter=this-week&event_category=workshop
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- jQuery (included with WordPress)

## Support

For issues or feature requests, please contact the plugin author.

## License

GPL v2 or later

## Changelog

### Version 1.0.0

- Initial release
- Custom post type for events
- Three beautiful shortcodes
- REST API endpoints
- Interactive calendar view
- Frontend event submission
- Email notifications

### Roadmap

- mailchimp intergrations
- sendgrid intergration

---

**Made with ❤️ for the WordPress community**
