(function ($) {
    'use strict';

    class EventCalendar {
        constructor(container) {
            this.container = $(container);
            this.currentDate = new Date();
            this.events = [];
            this.viewMode = 'calendar'; // 'calendar' or 'list'
            this.init();
        }

        init() {
            this.loadEvents();
            this.render();
            this.attachEvents();
        }

        async loadEvents() {
            try {
                this.showLoading();
                const response = await fetch(empCalendar.restUrl + '/upcoming');
                const events = await response.json();
                this.events = this.processEvents(events);
                this.render();
            } catch (error) {
                console.error('Error loading events:', error);
                this.container.html('<p>Error loading events. Please try again later.</p>');
            }
        }

        processEvents(events) {
            return events.map(event => ({
                ...event,
                startDate: new Date(event.start_date),
                endDate: event.end_date ? new Date(event.end_date) : null
            }));
        }

        showLoading() {
            this.container.html(`
                <div class="emp-calendar-loading">
                    <div class="emp-loading-spinner"></div>
                    <p>Loading events...</p>
                </div>
            `);
        }

        render() {
            const html = `
                <div class="emp-calendar-header">
                    <div class="emp-calendar-nav">
                        <button class="emp-prev-month">← Previous</button>
                        <h2 class="emp-calendar-title">${this.getMonthYearString()}</h2>
                        <button class="emp-next-month">Next →</button>
                    </div>
                    <div class="emp-calendar-view-toggle">
                        <button class="emp-view-btn ${this.viewMode === 'calendar' ? 'active' : ''}" data-view="calendar">
                            📅 Calendar
                        </button>
                        <button class="emp-view-btn ${this.viewMode === 'list' ? 'active' : ''}" data-view="list">
                            📋 List
                        </button>
                    </div>
                </div>
                ${this.viewMode === 'calendar' ? this.renderCalendarView() : this.renderListView()}
                ${this.renderModal()}
            `;
            this.container.html(html);
        }

        renderCalendarView() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());
            
            const days = [];
            const currentDate = new Date(startDate);
            
            while (currentDate <= lastDay || days.length % 7 !== 0) {
                days.push(new Date(currentDate));
                currentDate.setDate(currentDate.getDate() + 1);
            }

            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            let html = '<div class="emp-calendar-grid">';
            
            // Day headers
            dayNames.forEach(day => {
                html += `<div class="emp-calendar-day-header">${day}</div>`;
            });
            
            // Calendar days
            days.forEach(day => {
                const isCurrentMonth = day.getMonth() === month;
                const isToday = this.isToday(day);
                const dayEvents = this.getEventsForDate(day);
                const hasEvents = dayEvents.length > 0;
                
                let classes = 'emp-calendar-day';
                if (!isCurrentMonth) classes += ' other-month';
                if (isToday) classes += ' today';
                if (hasEvents) classes += ' has-events';
                
                html += `
                    <div class="${classes}" data-date="${day.toISOString()}">
                        <div class="emp-calendar-day-number">${day.getDate()}</div>
                        <div class="emp-calendar-events">
                            ${this.renderDayEvents(dayEvents)}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            return html;
        }

        renderDayEvents(events) {
            const maxVisible = 3;
            let html = '';
            
            events.slice(0, maxVisible).forEach(event => {
                html += `
                    <div class="emp-calendar-event" data-event-id="${event.id}">
                        ${this.truncate(event.title, 20)}
                    </div>
                `;
            });
            
            if (events.length > maxVisible) {
                html += `
                    <div class="emp-calendar-event-more">
                        +${events.length - maxVisible} more
                    </div>
                `;
            }
            
            return html;
        }

        renderListView() {
            const upcomingEvents = this.events
                .filter(event => event.startDate >= new Date())
                .sort((a, b) => a.startDate - b.startDate);

            if (upcomingEvents.length === 0) {
                return '<div class="emp-calendar-list-view active"><p>No upcoming events found.</p></div>';
            }

            let html = '<div class="emp-calendar-list-view active">';
            
            upcomingEvents.forEach(event => {
                html += `
                    <div class="emp-list-event-item" data-event-id="${event.id}">
                        <div class="emp-list-event-date">
                            ${this.formatDate(event.startDate)}
                        </div>
                        <h3 class="emp-list-event-title">${event.title}</h3>
                        <div class="emp-list-event-meta">
                            ${event.location ? `<span>📍 ${event.location}</span>` : ''}
                            ${event.startDate ? `<span>🕐 ${this.formatTime(event.startDate)}</span>` : ''}
                        </div>
                        ${event.description ? `<p>${this.truncate(event.description, 150)}</p>` : ''}
                    </div>
                `;
            });
            
            html += '</div>';
            return html;
        }

        renderModal() {
            return `
                <div class="emp-event-modal">
                    <div class="emp-event-modal-content">
                        <button class="emp-modal-close">×</button>
                        <div class="emp-modal-body"></div>
                    </div>
                </div>
            `;
        }

        showEventDetails(eventId) {
            const event = this.events.find(e => e.id == eventId);
            if (!event) return;

            const modalBody = `
                <h2 class="emp-modal-event-title">${event.title}</h2>
                <div class="emp-modal-event-meta">
                    ${event.startDate ? `
                        <div class="emp-modal-meta-item">
                            <span class="emp-modal-meta-icon">📅</span>
                            <span>${this.formatDate(event.startDate)} at ${this.formatTime(event.startDate)}</span>
                        </div>
                    ` : ''}
                    ${event.location ? `
                        <div class="emp-modal-meta-item">
                            <span class="emp-modal-meta-icon">📍</span>
                            <span>${event.location}</span>
                        </div>
                    ` : ''}
                </div>
                ${event.description ? `
                    <div class="emp-modal-event-description">
                        ${event.description}
                    </div>
                ` : ''}
                <a href="${event.permalink}" class="emp-modal-event-link">
                    View Full Details & Register →
                </a>
            `;

            $('.emp-modal-body').html(modalBody);
            $('.emp-event-modal').addClass('active');
        }

        attachEvents() {
            const self = this;

            // Navigation
            this.container.on('click', '.emp-prev-month', function () {
                self.currentDate.setMonth(self.currentDate.getMonth() - 1);
                self.render();
            });

            this.container.on('click', '.emp-next-month', function () {
                self.currentDate.setMonth(self.currentDate.getMonth() + 1);
                self.render();
            });

            // View toggle
            this.container.on('click', '.emp-view-btn', function () {
                self.viewMode = $(this).data('view');
                self.render();
            });

            // Event click
            this.container.on('click', '.emp-calendar-event, .emp-list-event-item', function (e) {
                e.stopPropagation();
                const eventId = $(this).data('event-id');
                self.showEventDetails(eventId);
            });

            // Day click (show all events)
            this.container.on('click', '.emp-calendar-day', function () {
                const date = new Date($(this).data('date'));
                const events = self.getEventsForDate(date);
                if (events.length > 0) {
                    // Show first event or create a day view
                    self.showEventDetails(events[0].id);
                }
            });

            // Modal close
            this.container.on('click', '.emp-modal-close, .emp-event-modal', function (e) {
                if (e.target === this) {
                    $('.emp-event-modal').removeClass('active');
                }
            });

            // Prevent modal content click from closing
            this.container.on('click', '.emp-event-modal-content', function (e) {
                e.stopPropagation();
            });
        }

        getEventsForDate(date) {
            const dateStr = date.toDateString();
            return this.events.filter(event => {
                return event.startDate.toDateString() === dateStr;
            });
        }

        isToday(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
        }

        getMonthYearString() {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            return `${months[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
        }

        formatDate(date) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
        }

        formatTime(date) {
            let hours = date.getHours();
            const minutes = date.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const minutesStr = minutes < 10 ? '0' + minutes : minutes;
            return `${hours}:${minutesStr} ${ampm}`;
        }

        truncate(str, length) {
            if (!str) return '';
            return str.length > length ? str.substring(0, length) + '...' : str;
        }
    }

    // Initialize calendar when DOM is ready
    $(document).ready(function () {
        const calendarContainer = $('#emp-event-calendar');
        if (calendarContainer.length) {
            new EventCalendar(calendarContainer);
        }
    });

})(jQuery);