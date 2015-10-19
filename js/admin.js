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

		select.change(function() {
			$('.taxonomies-add').hide();
			show_tax_blocks(select.val(),cpts);
		});
	}

	if ( !( $('.instagram-tab.active').length > 0 ) ) {
		$('.instagram-tab:first, .help-tab-content:first').addClass('active');
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
		tb_show( window.dwinstagram.logout_text, 'https://instagram.com/accounts/logout/?TB_iframe=true');
		setTimeout(function(){
			tb_remove();
			$('.user-authenticate').submit();
		},1000);
		return false;
	});

	var spinner = $('.spinner-wrap, .spinner-wrap .spinner');
	var strong = spinner.next('strong').hide();
	var messagesDiv = $('.updated.instagram-import-message');
	var msgSpinner = $('.spinner', messagesDiv);
	var msgList = $('ol', messagesDiv);
	var doingloop = false;
	var import_continue = true;

	// Stop button
	$('#insta-import-stop').click(function() {
		import_continue = false;
		$(this).val('Stopping...');
	});

	// when clicking "import"
	$('.button-secondary.import-button').click( function(event) {
		event.preventDefault();

		var el = $(this);
		var userid = el.data('instagramuser');

		spinner.show();
		// import our photos
		instagramAjax(userid);
	});

	function instagramAjax(userid, next_url) {
		var data = {
			action: 'dsgnwrks_instagram_import',
			instagram_user: userid
		};
		if ( typeof next_url !== 'undefined' ) {}
			data.next_url = next_url;

		$.ajax({
			type : "post",
			dataType : "json",
			url : window.ajaxurl,
			data : data,
			success : instagramSuccess,
			error: instagramError
		});
	}

	// ajax success handler
	function instagramSuccess(response) {
		// console.log(response);
		spinner.hide();

		$('#message').remove();
		if ( !doingloop )
			window.scrollTo(0, 0);

		if ( response.success && import_continue ) {
			var next_url = typeof response.data.next_url !== 'undefined' ? response.data.next_url : false;
			var userid = typeof response.data.userid !== 'undefined' ? response.data.userid : false;

			console.log(response.data.messages);
			messagesDiv.show();
			msgList.append(response.data.messages);

			// If we want to loop again
			if ( next_url && userid ) {
				console.log('we want to loop again');
				msgSpinner.show();
				doingloop = true;
				return instagramAjax(userid, next_url);
			} else {
				// ok, we're done looping
				msgSpinner.hide();
				messagesDiv.append('<div class="clear"><a class="button" id="instagram-remove-messages" href="#">Hide</a></div>');
			}

		}
		else {
			if ( doingloop ) {
				// ok, we're done looping
				msgSpinner.hide();
				messagesDiv.append('<div class="clear"><a class="button" id="instagram-remove-messages" href="#">Hide</a>&nbsp;&nbsp;All done!</div>');
			}
			// Just a standard "no photos" response
			else {

				$('#icon-tools + h2').after(response.data);
			}
		}
	}
	// ajax error handler
	function instagramError(xhr, ajaxOptions, thrownError) {
		console.warn(xhr.status);
		console.warn(thrownError);
		spinner.hide();
		strong.show();
		setTimeout( function(){
			strong.fadeOut('slow');
		}, 2000);
	}

	// hides the imported posts notice box
	$('body').on( 'click', '#instagram-remove-messages', function(event) {
		event.preventDefault();
		messagesDiv.hide();
	});

});
