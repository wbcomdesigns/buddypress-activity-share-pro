jQuery(function () {

	jQuery(document).on('click', '.add_services_btn', function () {
		jQuery('.error_service').hide();
		var service_value = jQuery('#social_services_selector_id').val();
		var service_name = jQuery('#social_services_selector_id :selected').text();
		var service_faw = jQuery('.faw_class_input').val();
		var service_description = jQuery('.bp_share_description').val();
		if (service_value == '' && service_name == '-select-') {
			jQuery('.error_service_selector').show();
		}
		if (service_faw == '') {
			jQuery('.error_service_faw-icon').show();
		}
		if (service_description == '') {
			jQuery('.error_service_description').show();
		}
		if (service_value != '' && service_name != '-select-' && service_faw != '' && service_description != '') {

			jQuery('.spint_action').show();
			var data = {
				'action': 'bp_share_insert_services_ajax',
				'service_name': service_name,
				'service_faw': service_faw,
				'service_value': service_value,
				'service_description': service_description,
				'nonce': my_ajax_object.nonce
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(my_ajax_object.ajax_url, data, function (response) {
				location.reload();
			});
		}
	});

	jQuery('.bp_share_social_list').sortable({
		scrollSpeed: 1,
		scrollSensitivity: 1,
	});

	jQuery(document).on('click', '.service_delete_icon', function () {
		var service = jQuery(this).data().bind;
		var data = {
			'action': 'bp_share_delete_services_ajax',
			'service_name': service,
			'nonce': my_ajax_object.nonce
		};

		jQuery(this).html('<i class="fa fa-circle-o-notch fa-spin fa-1x fa-fw"></i>');
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(my_ajax_object.ajax_url, data, function (response) {
			jQuery('#tr_' + jQuery.trim(response)).fadeOut("normal", function () {
				jQuery(this).remove();
			});
		});
	});



	jQuery(document).on('click', '.button-primary.bp_share_option_save', function () {
		let sortedList = [];
		let listLegth = jQuery('tr.bp-share-services-row').length;
		jQuery('tr.bp-share-services-row').each(function(index, elem){
			if ( index < listLegth  ){
				let temp = [];
				let key = jQuery(this).data('key');
				temp = { 
					oldIndex : jQuery(this).data('pos-index'),
					newIndex: index,
					key : key,
				}
				sortedList.push( temp );
			}
		});
		jQuery.ajax({
			url: my_ajax_object.ajax_url,
			type: 'post',
			data: { action: 'bp_share_sort_social_links_ajax', 'nonce': my_ajax_object.nonce, 'sorted_data': sortedList },
			dataType: 'JSON',
			success: function (response) {
				
			}
		});
		var selected = [];
		jQuery('#bp_share_chb input:checked').each(function () {
			selected.push(jQuery(this).attr('name'));
		});

		var selected_extras = [];
		jQuery('.bp_share_settings_section_callback_class input:checked').each(function () {
			selected_extras.push(jQuery(this).attr('name'));
		});
		var data = {
			'action': 'bp_share_chb_services_ajax',
			'active_chb_array': selected,
			'active_chb_extras': selected_extras,
			'nonce': my_ajax_object.nonce
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(my_ajax_object.ajax_url, data, function (response) {
			//location.reload();
			jQuery('form#bp_share_form').submit();
		});
		return false;
	});
	
	jQuery('.bp-reshare-color-picker').wpColorPicker();

	/* Drag Drop */
    jQuery(function() {
        var $drag_social_icon = jQuery(".social_icon_section > ul");
        jQuery("li", $drag_social_icon).draggable({
            revert: "invalid",
            helper: "clone"
        });
    });

    jQuery(function() {
        function bpas_drag_drop_social_icon() {
            var $drag_social_icon = jQuery(".social_icon_section > ul");
            jQuery("li", $drag_social_icon).draggable({
                revert: "invalid",
                helper: "clone"
            });
        }

        jQuery("#drag_icon_ul").droppable({
            accept: ".social_icon_section > #drag_social_icon > li",
            drop: function(event, ui) {
                var name = jQuery(ui.draggable).text();
                jQuery(ui.draggable).remove();
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wss_social_icons',
                        term_name: name,
                        nonce: my_ajax_object.nonce
                    },
                    success: function(res) {
                        if (res.success) {
                            var obj = jQuery(ui.draggable);
                            if (obj != null) {
                                obj[0].lastChild.remove();
                                var socialclass = 'icon_' + name;
                                var $list = jQuery('ul', ' #drag_icon_ul').length ? jQuery('ul', '#drag_icon_ul') : jQuery("#drag_icon_ul").append('<li class="socialicon ui-draggable '+ socialclass +' ">' + name + '</li>');
                            }
                            bpas_drag_drop_social_icon();

                        }
                    }
                });
            }
        });

        jQuery("#drag_social_icon").droppable({
            accept: ".social_icon_section > #drag_icon_ul > li",
            drop: function(event, ui) {
                var get_icon_name = jQuery(ui.draggable).text();
                jQuery(ui.draggable).remove();
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wss_social_remove_icons',
                        icon_name: get_icon_name,
                        nonce: my_ajax_object.nonce
                    },
                    success: function(res) {
                        var obj = jQuery(ui.draggable);
                        if (obj != null) {
                            obj[0].lastChild.remove();
                            var socialclass = 'icon_' + get_icon_name;
                            var $list = jQuery('ul', ' #drag_social_icon').length ? jQuery('ul', '#drag_social_icon') : jQuery("#drag_social_icon").append('<li class="socialicon ui-draggable '+ socialclass +'">' + get_icon_name + '</li>');
                        }
                        bpas_drag_drop_social_icon();
                    }
                });
            }
        });
    });

});
