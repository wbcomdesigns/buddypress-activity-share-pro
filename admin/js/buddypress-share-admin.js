jQuery(function () {

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
				helper: "clone",
				start: function () {
					jQuery(this).css('opacity', '0.5');
				},
				stop: function () {
					jQuery(this).css('opacity', '1');
				}
			});
		}

		jQuery("#drag_icon_ul").droppable({
			accept: ".social_icon_section > #drag_social_icon > li",
			drop: function (event, ui) {
				var name = jQuery(ui.draggable).text();
				var socialclass = 'icon_' + name;
				var newElem = jQuery('<li class="socialicon ui-draggable ' + socialclass + '">' + name + '</li>').hide();
				jQuery("#drag_icon_ul").append(newElem);
				newElem.fadeIn();

				jQuery(ui.draggable).fadeOut("normal", function () {
					jQuery(this).remove();
				});

				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wss_social_icons',
						term_name: name,
						nonce: my_ajax_object.nonce
					},
					success: function (res) {
						if (res.success) {
							// Additional success handling
						}
					},
					complete: function () {
						bpas_drag_drop_social_icon();
					}
				});
			}
		});

		jQuery("#drag_social_icon").droppable({
			accept: ".social_icon_section > #drag_icon_ul > li",
			drop: function (event, ui) {
				var get_icon_name = jQuery(ui.draggable).text();
				var socialclass = 'icon_' + get_icon_name;
				var newElem = jQuery('<li class="socialicon ui-draggable ' + socialclass + '">' + get_icon_name + '</li>').hide();
				jQuery("#drag_social_icon").append(newElem);
				newElem.fadeIn();

				jQuery(ui.draggable).fadeOut("normal", function () {
					jQuery(this).remove();
				});

				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wss_social_remove_icons',
						icon_name: get_icon_name,
						nonce: my_ajax_object.nonce
					},
					success: function (res) {
						if (res.success) {
							// Additional success handling
						}
					},
					complete: function () {
						bpas_drag_drop_social_icon();
					}
				});
			}
		});

		// Initial call to set up draggable and droppable elements.
		bpas_drag_drop_social_icon();
    });

});
