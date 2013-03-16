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

		if (typeof cpts[curr_cpt] !== 'undefined') {
			curr_taxes = cpts[curr_cpt];

			curr_taxes = curr_taxes.toString();
			curr_taxes = curr_taxes.split(',');
			$.each(curr_taxes, function(i, tax) {
				$('.taxonomy-'+tax).show();
			});
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


	function changeQueryVar(url, keyString, replaceString) {
		var vars = url.split('&');
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split('=');
			if (pair[0] == keyString) {
				vars[i] = pair[0] + '=' + replaceString;
			}
		}
		return vars.join('&');
	}
});