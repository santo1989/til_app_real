/**
 * Auto Refresh Module - Refreshes page content every 30 seconds
 * Usage: Add data-auto-refresh="true" attribute to any container
 */
(function() {
    'use strict';

    const AutoRefresh = {
        interval: 30000, // 30 seconds
        timers: {},
        
        init: function() {
            this.setupRefreshContainers();
            this.setupManualRefresh();
        },
        
        setupRefreshContainers: function() {
            const containers = document.querySelectorAll('[data-auto-refresh="true"]');
            containers.forEach(container => {
                const url = container.getAttribute('data-refresh-url') || window.location.href;
                const target = container.getAttribute('data-refresh-target') || container.id;
                
                if (target) {
                    this.startRefresh(url, target);
                }
            });
        },
        
        startRefresh: function(url, targetId) {
            // Initial indicator setup
            this.addRefreshIndicator(targetId);
            
            // Set up periodic refresh
            this.timers[targetId] = setInterval(() => {
                this.refreshContent(url, targetId);
            }, this.interval);
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (this.timers[targetId]) {
                    clearInterval(this.timers[targetId]);
                }
            });
        },
        
        refreshContent: function(url, targetId) {
            const target = document.getElementById(targetId);
            if (!target) return;
            
            const indicator = document.getElementById(`refresh-indicator-${targetId}`);
            if (indicator) {
                indicator.classList.add('active');
            }
            
            // Add AJAX header to request
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                // Create temp container to parse response
                const temp = document.createElement('div');
                temp.innerHTML = html;
                
                // Find matching element in response
                const newContent = temp.querySelector(`#${targetId}`);
                if (newContent) {
                    target.innerHTML = newContent.innerHTML;
                    // Reinitialize any event listeners if needed
                    this.triggerRefreshEvent(targetId);
                }
            })
            .catch(error => {
                console.error('Auto-refresh error:', error);
            })
            .finally(() => {
                if (indicator) {
                    indicator.classList.remove('active');
                }
            });
        },
        
        addRefreshIndicator: function(targetId) {
            const target = document.getElementById(targetId);
            if (!target || document.getElementById(`refresh-indicator-${targetId}`)) return;
            
            const indicator = document.createElement('div');
            indicator.id = `refresh-indicator-${targetId}`;
            indicator.className = 'refresh-indicator';
            indicator.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span class="ms-2">Updating...</span>
            `;
            
            // Insert before target
            target.parentNode.insertBefore(indicator, target);
        },
        
        setupManualRefresh: function() {
            document.querySelectorAll('[data-manual-refresh]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = button.getAttribute('data-manual-refresh');
                    const url = button.getAttribute('data-refresh-url') || window.location.href;
                    this.refreshContent(url, targetId);
                });
            });
        },
        
        triggerRefreshEvent: function(targetId) {
            const event = new CustomEvent('contentRefreshed', { 
                detail: { targetId: targetId } 
            });
            document.dispatchEvent(event);
        },
        
        stopRefresh: function(targetId) {
            if (this.timers[targetId]) {
                clearInterval(this.timers[targetId]);
                delete this.timers[targetId];
            }
        }
    };
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AutoRefresh.init());
    } else {
        AutoRefresh.init();
    }
    
    // Expose globally
    window.AutoRefresh = AutoRefresh;
})();
