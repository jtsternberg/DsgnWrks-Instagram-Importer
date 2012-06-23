jQuery(document).ready(function($) {

	if (window.dwinstagram.cpts !== undefined) {

		$('.taxonomies-add').hide();

		var select = $('#instagram-post-type'),
		curr_cpt = $('#instagram-post-type').val(),
		cpts = dwinstagram.cpts;

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
				console.log(tax);
				$('.taxonomy-'+tax).show();
			});
		}
	}

});