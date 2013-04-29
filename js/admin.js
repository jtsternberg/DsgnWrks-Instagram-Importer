jQuery(document).ready(function($) {

	if (window.dwinstagram.cpts !== undefined) {

		show_tax_blocks_init();

		$('.instagram-tab a').click(function() {
			show_tax_blocks_init($(this).text());
		});

	}

	function show_tax_blocks_init(user) {

		$('.taxonomies-add').hide();

		var select = $('.help-tab-content.active .instagram-post-type'),
		curr_cpt = $('.help-tab-content.active .instagram-post-type').val(),
		cpts = dwinstagram.cpts;

		if (user !== undefined) {
			select = $('#instagram-post-type-'+user),
			curr_cpt = $('#instagram-post-type-'+user).val();
		}

		show_tax_blocks(curr_cpt,cpts);

		$(select).change(function() {
			$('.taxonomies-add').hide();
			show_tax_blocks($(select).val(),cpts);
		});
	}


	function show_tax_blocks(curr_cpt,cpts) {

		// hashtags saver (disable)
		var selector = 'select[id$="hashtags_as_tax"]:visible';
		$(selector).prop('disabled',true);

		if (typeof cpts[curr_cpt] !== 'undefined') {
			curr_taxes = cpts[curr_cpt];

			curr_taxes = curr_taxes.toString();
			curr_taxes = curr_taxes.split(',');

			// hashtags saver (disable options)
			$(selector + ' option:not(.empty)').prop('disabled',true);
			var selected = $(selector + ' option:selected').prop('selected',false).text();
			var option;

			for ( var i = 0; i < curr_taxes.length; i++ ) {

				var tax = curr_taxes[i];
				$('.taxonomy-'+tax).show();

				// skip post formats
				if ( tax === 'post_format' )
					continue;

				// hashtags saver (re-enable options)
				$(selector).prop('disabled',false);
				option = $(selector + ' option.taxonomy-'+tax);
				option.prop('disabled',false);
				if ( option.text() === selected )
					option.prop('selected', true);
			}
		}
	}

	$('.delete-instagram-user').click(function(event) {
		var userid = '#full-username-' + $(this).attr('id').replace('delete-','');
		if ( !confirm(window.dwinstagram.delete_text +', '+ $(userid).text() +'?') ) {
			event.preventDefault();
		}
	});

	// var width = $('.help-tab-content').width();
	// $('.dw-pw-form').width(width-85);

	$('.button-primary.save').click(function(event) {
		var curr_user = $.trim($('.instagram-tab.active').text());
		$('input[name="dsgnwrks_insta_options[username]"]').val(curr_user);
		// event.preventDefault();
	});

	$('.save-warning').hide();
	// $('.dw-pw-form').hide();
	$('.user-options input, .user-options select').change(function() {
		var curr_user = $.trim($('.instagram-tab.active').text());
		$('.save-warning.user-'+curr_user).show();
	});

	$('.button-primary.authenticate.logout').click( function(event) {
		tb_show( window.dwinstagram.logout_text, 'https://instagr.am/accounts/logout/?TB_iframe=true');
		setTimeout(function(){
			tb_remove();
			$('.user-authenticate').submit();
		},1000);
		return false;
	});

	$('.button-secondary.import-button').click( function(event) {
		event.preventDefault();

		var el = $(this);
		var userid = el.data('instagramuser');
		var spinner = $('.spinner-wrap, .spinner-wrap .spinner');
		var strong = spinner.next('strong').hide();

		spinner.show();

		$.ajax({
			type : "post",
			dataType : "json",
			url : window.ajaxurl,
			data : {
				action: 'dsgnwrks_instagram_import',
				instagram_user: userid
			},
			success : function(response) {
				spinner.hide();
				$('#message').remove();
				window.scrollTo(0, 0);
				$('#icon-tools + h2').after(response.data);

			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.warn(xhr.status);
				console.warn(thrownError);
				spinner.hide();
				strong.show();
				setTimeout( function(){
					strong.fadeOut('slow');
				}, 2000);
			}
		});

	});

	$('body').on( 'click', '#instagram-remove-messages', function(event) {
		event.preventDefault();
		$('.updated.instagram-import-message').remove();
	});

});