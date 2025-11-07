jQuery(document).ready(function($) {
    // Archive filtering
    if ($('.emp-filter-bar').length) {
        $('#emp-filter-category, #emp-filter-date').on('change', function() {
            const category = $('#emp-filter-category').val();
            const dateFilter = $('#emp-filter-date').val();
            
            // Build URL with filters
            let url = window.location.pathname;
            const params = new URLSearchParams();
            
            if (category) params.append('event_category', category);
            if (dateFilter) params.append('date_filter', dateFilter);
            
            if (params.toString()) {
                url += '?' + params.toString();
            }
            
            // Reload page with filters
            window.location.href = url;
        });
        
        // Restore filter values from URL
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('event_category');
        const dateFilter = urlParams.get('date_filter');
        
        if (category) $('#emp-filter-category').val(category);
        if (dateFilter) $('#emp-filter-date').val(dateFilter);
    }
});