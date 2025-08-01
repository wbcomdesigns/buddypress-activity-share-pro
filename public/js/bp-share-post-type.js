/**
 * BuddyPress Share Pro - Post Type Sharing JavaScript
 * Version: 2.1.0
 */

(function($) {
    'use strict';

    /**
     * Main controller for post type sharing
     */
    class BPSharePostType {
        constructor() {
            this.wrapper = $('.bp-share-floating-wrapper');
            this.isActive = false;
            this.clipboard = null;
            
            this.init();
        }

        /**
         * Initialize the sharing functionality
         */
        init() {
            if (!this.wrapper.length) return;

            this.bindEvents();
            this.initClipboard();
            this.trackVisibility();
            this.handleScrollBehavior();
        }

        /**
         * Bind all event handlers
         */
        bindEvents() {
            // Toggle floating wrapper
            this.wrapper.find('.bp-share-toggle').on('click', (e) => {
                e.preventDefault();
                this.toggleWrapper();
            });

            // Handle service clicks
            this.wrapper.find('.bp-share-service').on('click', (e) => {
                const $link = $(e.currentTarget);
                const service = $link.data('service');
                
                if (service === 'copy') {
                    e.preventDefault();
                    // Get the URL from the href attribute
                    const shareUrl = $link.attr('href');
                    this.copyLinkUrl(shareUrl);
                } else if (service === 'print') {
                    e.preventDefault();
                    this.printPage();
                } else if (service === 'email') {
                    // Let email links work normally
                    // Track the share
                    this.trackShare(service, this.wrapper.data('post-id'));
                } else {
                    // For social media links, track the share
                    this.trackShare(service, this.wrapper.data('post-id'));
                    // Let the link open normally in new tab
                }
            });

            // Handle inline button clicks
            $(document).on('click', '.bp-share-button', (e) => {
                const $button = $(e.currentTarget);
                const service = $button.data('service');
                
                if (service === 'copy') {
                    e.preventDefault();
                    this.copyLink(e.currentTarget);
                } else if (service === 'print') {
                    e.preventDefault();
                    this.printPage();
                } else if (service !== 'email') {
                    // Let default behavior handle email
                    this.trackShare(service, $button.data('post-id'));
                }
            });

            // Close on outside click
            $(document).on('click', (e) => {
                if (this.isActive && 
                    !this.wrapper.is(e.target) && 
                    this.wrapper.has(e.target).length === 0) {
                    this.toggleWrapper(false);
                }
            });

            // Close on escape key
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.isActive) {
                    this.toggleWrapper(false);
                }
            });
        }

        /**
         * Toggle the floating wrapper open/closed
         */
        toggleWrapper(state = null) {
            this.isActive = state !== null ? state : !this.isActive;
            this.wrapper.toggleClass('bp-share-active', this.isActive);
        }

        /**
         * Copy URL to clipboard
         */
        copyLinkUrl(url) {
            const postId = this.wrapper.data('post-id');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    this.showTooltip(bp_share_post_vars.copied_text);
                    this.trackShare('copy', postId);
                }).catch(() => {
                    this.fallbackCopyLink(url);
                });
            } else {
                this.fallbackCopyLink(url);
            }
        }

        /**
         * Copy link to clipboard (for inline buttons)
         */
        copyLink(element) {
            const postId = this.wrapper.data('post-id') || $(element).data('post-id');
            // For inline buttons, get the URL from data attribute
            const url = $(element).attr('data-href') || $(element).attr('href');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    this.showTooltip(bp_share_post_vars.copied_text);
                    this.trackShare('copy', postId);
                }).catch(() => {
                    this.fallbackCopyLink(url);
                });
            } else {
                this.fallbackCopyLink(url);
            }
        }

        /**
         * Fallback copy method for older browsers
         */
        fallbackCopyLink(url) {
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(url).select();
            document.execCommand('copy');
            $temp.remove();
            
            this.showTooltip(bp_share_post_vars.copied_text);
            this.trackShare('copy', this.wrapper.data('post-id'));
        }

        /**
         * Print the current page
         */
        printPage() {
            window.print();
            this.trackShare('print', this.wrapper.data('post-id'));
        }

        /**
         * Track share event via AJAX
         */
        trackShare(service, postId) {
            $.ajax({
                url: bp_share_post_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'bp_share_post',
                    service: service,
                    post_id: postId,
                    nonce: bp_share_post_vars.nonce
                },
                success: (response) => {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.success && data.count !== undefined) {
                            this.updateShareCount(data.count);
                        } else if (!data.success && data.message) {
                            // Log for developers only
                            if (window.console && window.console.warn) {
                                console.warn('BuddyPress Share: ' + data.message);
                            }
                        }
                    } catch (e) {
                        // Log for developers only
                        if (window.console && window.console.error) {
                            console.error('BuddyPress Share: Unable to process response', e);
                        }
                    }
                },
                error: (xhr, status, error) => {
                    // Log for developers only
                    if (window.console && window.console.error) {
                        console.error('BuddyPress Share: Network error', error);
                    }
                    // Don't show error to user - sharing still works
                }
            });
        }

        /**
         * Update the share count display
         */
        updateShareCount(count) {
            const $count = this.wrapper.find('.bp-share-count');
            if ($count.length) {
                $count.text(this.formatCount(count));
                
                // Show count if it was hidden
                if (count > 0 && $count.is(':hidden')) {
                    $count.fadeIn();
                }
            }
        }

        /**
         * Format large numbers for display
         */
        formatCount(count) {
            if (count < 1000) return count;
            if (count < 1000000) return (count / 1000).toFixed(1) + 'K';
            return (count / 1000000).toFixed(1) + 'M';
        }

        /**
         * Show tooltip message with smooth animation
         */
        showTooltip(message) {
            const $tooltip = this.wrapper.find('.bp-share-tooltip');
            const $text = $tooltip.find('.bp-share-tooltip-text');
            
            $text.text(message);
            $tooltip.show().addClass('show');
            
            setTimeout(() => {
                $tooltip.removeClass('show');
                setTimeout(() => {
                    $tooltip.hide();
                }, 300);
            }, 2000);
        }

        /**
         * Initialize clipboard functionality
         */
        initClipboard() {
            // Optional: Use clipboard.js if available
            if (typeof ClipboardJS !== 'undefined') {
                this.clipboard = new ClipboardJS('.bp-share-service-copy', {
                    text: (trigger) => {
                        // Get the actual share URL from the element
                        return $(trigger).attr('href');
                    }
                });
                
                this.clipboard.on('success', (e) => {
                    this.showTooltip(bp_share_post_vars.copied_text);
                    this.trackShare('copy', this.wrapper.data('post-id'));
                    e.clearSelection();
                });
            }
        }

        /**
         * Track visibility for analytics
         */
        trackVisibility() {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // Track impression
                            this.trackImpression();
                            observer.disconnect();
                        }
                    });
                });
                
                observer.observe(this.wrapper[0]);
            }
        }

        /**
         * Track share widget impression
         */
        trackImpression() {
            // Send impression tracking if needed
            // This could be used for A/B testing or analytics
        }

        /**
         * Handle scroll behavior for better UX
         */
        handleScrollBehavior() {
            let lastScrollTop = 0;
            let scrollTimer = null;
            
            $(window).on('scroll', () => {
                clearTimeout(scrollTimer);
                
                const scrollTop = $(window).scrollTop();
                
                // Hide on scroll down, show on scroll up
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    this.wrapper.addClass('bp-share-hidden');
                } else {
                    this.wrapper.removeClass('bp-share-hidden');
                }
                
                lastScrollTop = scrollTop;
                
                // Reset after scrolling stops
                scrollTimer = setTimeout(() => {
                    this.wrapper.removeClass('bp-share-hidden');
                }, 500);
            });
        }
    }

    /**
     * Helper function for share window positioning
     */
    function centerWindow(url, title, w, h) {
        const left = (screen.width - w) / 2;
        const top = (screen.height - h) / 2;
        
        return window.open(
            url, 
            title,
            `toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=${w}, height=${h}, top=${top}, left=${left}`
        );
    }

    /**
     * Initialize on document ready
     */
    $(document).ready(() => {
        new BPSharePostType();
    });

    /**
     * Export for external use
     */
    window.BPSharePostType = BPSharePostType;

})(jQuery);