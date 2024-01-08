(function($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(document).ready(function() {

        $(document).on('click', ".bp-share-button", function(e) {
            $('body').addClass('bp-share-popup-active');
            $(this).parent().parent().next(".service-buttons").toggle(500);			
			if ( $(this).parent().parent().next().next(".bp-share-service-popup-overlay").length) {
				$(this).parent().parent().next().next(".bp-share-service-popup-overlay").show();
			}
			
			if ( $('.bp-activity-more-options-wrap').length > 0 && !$('.bp-activity-more-options-wrap').hasClass( 'bp-activity-share-popup-open' ) ) {
				$('.bp-activity-more-options-wrap').addClass('bp-activity-share-popup-open');
			}
			
        });
		
		$(document).on('click', ".bp-share-service-popup-overlay", function(e) {
			$(this).hide();
            $('body').removeClass('bp-share-popup-active');
			$('.service-buttons').hide();
			if ( $('.bp-activity-more-options-wrap').length > 0 && $('.bp-activity-more-options-wrap').hasClass( 'bp-activity-share-popup-open' ) ) {
				$('.bp-activity-more-options-wrap').removeClass('bp-activity-share-popup-open');
			}
            
        });
        $(document).on("click", ".bp-share.has-popup", function(e) {
            var display_attr = $(this).attr('attr-display');
            if ('no-popup' != display_attr) {
                e.preventDefault();
                console.log(display_attr);
                goclicky($(this).attr("href"));
            }
        });

        function FindLeftWindowBoundry() {
            // In Internet Explorer window.screenLeft is the window's left boundry
            if (window.screenLeft) {
                return window.screenLeft;
            }

            // In Firefox window.screenX is the window's left boundry
            if (window.screenX)
                return window.screenX;

            return 0;
        }
        // Find Left Boundry of current Window
        function FindTopWindowBoundry() {
            // In Internet Explorer window.screenLeft is the window's left boundry
            if (window.screenTop) {
                return window.screenTop;
            }

            // In Firefox window.screenY is the window's left boundry
            if (window.screenY)
                return window.screenY;

            return 0;
        }

        function goclicky(meh) {

            console.log(FindLeftWindowBoundry(), FindTopWindowBoundry());
            var x = screen.width / 2 - 700 / 2 + FindLeftWindowBoundry();
            var y = screen.height / 2 - 450 / 2 + FindTopWindowBoundry();
            window.open(meh, '', 'height=485,width=700,left=' + x + ',top=' + y);
        }



        $(document).on('click', ".bp-cpoy", function(e) {
            e.preventDefault();
            var copyText = $(this).data('href');

            document.addEventListener('copy', function(e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            var tooltip = $(this).next();
            tooltip.removeClass('tooltip-hide');
            setTimeout(function() {
                tooltip.addClass('tooltip-hide');
            }, 500);


        });
		$("#activity-share-modal #post-in").select2({
			dropdownParent: $('#activity-share-modal')
		});
        $(document).on('click', '.bp-secondary-action.bp-activity-share-button', function(e) {
			
			e.preventDefault();
			var activity_id = '';
			var activity_html = '';
			var reshare_share_activity = bp_activity_sjare_vars.reshare_share_activity;
			
			 if ( typeof $(this).data( 'post-id' ) !== 'undefined' && $(this).data( 'post-id' ) != '' ) {
				activity_id = $(this).data( 'post-id' );
			} else {
				activity_id = $(this).data('activity-id');
				activity_html = $('#activity-' + activity_id).html();
				if ( reshare_share_activity == 'child') {
					$('#activity-' + activity_id + ' .activity-reshare-item-container').each( function(){
						activity_id = $(this).attr( 'id' ).split("bp-reshare-activity-");
						activity_id = activity_id[1];						
						activity_html = $(this).html();
						if ( $(this).hasClass('post-reshare-item-container')) {
							$('#bp-reshare-type').val('post_share');
						} else {
							$('#bp-reshare-type').val('activity_share');
						}
					}) 
				}
			}

            $('#activity-share-modal').on('shown.bs.modal', function() {
                $('#activity-share-modal').modal('show');
            });

            $('#bp-reshare-activity-id').val(activity_id);
            if (activity_html != '') {
				
                var activity_ul_class = $('#activity-stream ul').attr('class');
                var activity_li_class = $('#activity-stream ul li#activity-' + activity_id).attr('class');
                $('#activity-share-modal .modal-body #bp-activity-share-widget-box-status-header').addClass('');
                $('#activity-share-modal .modal-body #bp-activity-share-widget-box-status-header').addClass(activity_ul_class);
                $('#activity-share-modal .modal-body #bp-activity-share-widget-box-status-header').html('');
                $('#activity-share-modal .modal-body #bp-activity-share-widget-box-status-header').html('<div class="' + activity_li_class + '">' + activity_html + '</div>');
				
				if ( reshare_share_activity == 'parent') {
					$('#activity-share-modal .modal-body #bp-activity-share-widget-box-status-header').find('.activity-reshare-item-container').remove();
				}
            }

            $('#activity-share-modal .modal-body .activity-meta, #activity-share-modal .modal-body .post-footer, #activity-share-modal .modal-body .activity-comments, #activity-share-modal .modal-body .entry-button-wraper, #activity-share-modal .modal-body .bp-activity-post-footer').remove();
        });


        $(document).on('click', '.bp-activity-share-activity', function(e) {
			e.preventDefault();
            var activity_content = $('#activity-share-modal #bp-activity-share-text').val();
            var activity_id = $('#activity-share-modal #bp-reshare-activity-id').val();
            var activity_user_id = $('#activity-share-modal #bp-reshare-activity-user-id').val();
            var component = $('#activity-share-modal #bp-reshare-activity-current-component').val();
            var activity_in = $('#activity-share-modal #post-in').val();
            var type = $('#activity-share-modal #bp-reshare-type').val();
            var activity_in_type = $('#activity-share-modal #post-in option:selected').data( 'type' );

            jQuery.ajax({
                url: bp_activity_sjare_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'bp_activity_create_reshare_ajax',
                    activity_content: activity_content,
                    activity_id: activity_id,
                    activity_user_id: activity_user_id,
                    component: component,
                    activity_in: activity_in,
                    activity_in_type: activity_in_type,
                    type: type,
                    _ajax_nonce: bp_activity_sjare_vars.ajax_nonce
                },
                dataType: 'text',
                success: function(data) {
                    $('#activity-share-modal').modal('hide');
                    $('#activity-share-modal #bp-activity-share-text').val('');
					var share_count = $( '#bp-activity-reshare-count-' + activity_id ).html();
					
					$( '#bp-activity-reshare-count-' + activity_id ).html( parseInt(share_count) + parseInt('1') );
					if ( $( '#bp-activity-share-' + activity_id + ' .share-count' ).length != 0 ){
						$( '#bp-activity-share-' + activity_id + ' .share-count' ).html( parseInt(share_count) + parseInt('1') );
					}
					
                }
            });

        });

        $(document).on('change', '#post-in', function() {
            var activity_id = $('#activity-share-modal #bp-reshare-activity-id').val();
            var member_msg_url = bp_activity_sjare_vars.member_profile_url;
            var activity_url = member_msg_url +  'activity/' + activity_id + '/'; 
            var parameter = '?activity_id=' + activity_url; 
            if( $(this).val() == 'message' ){
                $('.bp-activity-share-activity').hide();
                $("<a href= " + member_msg_url + parameter +"  class='button small secondary'>Post</a>").insertAfter(".bp-activity-share-activity");
            }else{
                $('.bp-activity-share-activity').show();
                $('.bp-activity-share-post-footer-actions a').remove();
            }
        });
		
		$( document ).on('click','.bp-activity-share-close', function(){
			$('#activity-share-modal').modal('hide');
		});

        /* Share button toggle */
        $(document).on(
            'click',
            '.bp-activity-share-dropdown-toggle a.dropdown-toggle',
            function(e) {
                e.preventDefault();
                var current = $(this).closest('.bp-activity-share-dropdown-toggle');
                current.siblings('.selected').removeClass('selected');
                current.toggleClass('selected');
            }
        );

        $('body').mouseup(
            function(e) {
                var container = $('.bp-activity-share-dropdown-toggle *');
                if (!container.is(e.target)) {
                    $('.bp-activity-share-dropdown-toggle').removeClass('selected');
                }
            }
        );

        // Custom Code - Start
        $(document).on('click', '.bp-activity-reshare-btn', function(e) {
            e.preventDefault();
            var reshareOption = $(this).data('reshare');

            // Get the data-title attribute of the clicked button
            var reshareOptionText = $(this).attr('data-title');

            // Change the selected option in the regular dropdown
            $('#post-in').val(reshareOption).trigger('change');

            // Refresh the Select2 control and set the text of the selected option
            $('#post-in').next('.select2-container').find('.select2-selection__rendered').text(reshareOptionText);
            
        });

        $(document).on('click', '.bp-activity-reshare-btn', function(e) {
            var reshareOptionText = $(this).attr('data-title');
            var reshareOptionTextClass = reshareOptionText.toLowerCase().replace(/\s+/g, '-');
            
            $('.activity-share-modal').addClass(reshareOptionTextClass).data('reshareOptionTextClass', reshareOptionTextClass);
        });

        $('body').mouseup(function(e) {
            var container = $('.activity-share-modal');
            var reshareOptionTextClass = container.data('reshareOptionTextClass');
            
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                container.removeClass(reshareOptionTextClass);
            }
        });

        $('.bp-activity-share-activity, .activity-share-modal-close').on('click', function(e) {
            var container = $('.activity-share-modal');
            var reshareOptionTextClass = container.data('reshareOptionTextClass');
            
            e.preventDefault();
            container.removeClass(reshareOptionTextClass);
        });


    });

})(jQuery);