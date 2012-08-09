jQuery(document).ready(function($) {

	if (window.dwinstagram.cpts !== undefined) {

		show_tax_blocks_init();

		$('.tab-instagram-user a').click(function() {
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
		if ( !confirm('Are you sure you want to delete user, '+ $(userid).text() +'?') ) {
			event.preventDefault();
		}
	});

	$('.contextual-help-tabs a').click(function(event) {
		$('.dw-pw-form').hide();
		$('.import-button').show();
	});

	var width = $('.help-tab-content').width();
	$('.dw-pw-form').width(width-85);

	$('.button-primary').click(function(event) {
		var id = $(this).attr('id').replace('save-','');
		$('input[name="dsgnwrks_insta_options[username]"]').val(id);
		// event.preventDefault();
	});


	$('.save-warning').hide();
	$('.dw-pw-form').hide();
	$('.import-button').click(function(event) {
		$(this).hide();
		var id = $(this).attr('id').replace('import-',''),
		action = $('.dw-pw-form').attr('action'),
		replace = changeQueryVar(action,'instaimport',id);
		$('.save-warning').show();
		$('.dw-pw-form').show();
		$('.dw-pw-form').attr('action', replace);
		$('.dw-pw-form input[type="password"]').focus();
		event.preventDefault();
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