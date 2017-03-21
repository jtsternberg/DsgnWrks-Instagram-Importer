window.dwinstagram = window.dwinstagram || {};

jQuery(document).ready(function($) {
	var dw = window.dwinstagram;

	var log = function() {
		log.history = log.history || [];
		log.history.push( arguments );
		if ( dw.debug && window.console && window.console.log ) {
			window.console.log( Array.prototype.slice.call(arguments) );
		}
	};

	if ( window.dwinstagram.cpts !== undefined) {

		show_tax_blocks_init();

		$('.instagram-tab a').click( function() {
			show_tax_blocks_init($(this).text());
		});

	}

	function show_tax_blocks_init(user) {

		$('.taxonomies-add').hide();

		var select = $('.help-tab-content.active .instagram-post-type'),
		curr_cpt = $('.help-tab-content.active .instagram-post-type').val(),
		cpts = dwinstagram.cpts;

		if (user !== undefined) {
			select = $('#instagram-post-type-'+user);
			curr_cpt = $('#instagram-post-type-'+user).val();
		}

		show_tax_blocks(curr_cpt,cpts);

		select.change(function() {
			$('.taxonomies-add').hide();
			show_tax_blocks(select.val(),cpts);
		});
	}

	if ( ! $('.instagram-tab.active').length ) {
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
		spinner.hide();

		$('#message').remove();
		if ( !doingloop )
			window.scrollTo(0, 0);

		if ( response.success && import_continue ) {
			var next_url = typeof response.data.next_url !== 'undefined' ? response.data.next_url : false;
			var userid = typeof response.data.userid !== 'undefined' ? response.data.userid : false;

			log(response.data.messages);
			messagesDiv.show();
			msgList.append(response.data.messages);

			// If we want to loop again
			if ( next_url && userid ) {
				log('we want to loop again');
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

( function( window, document, $, dw, undefined ) {
	'use strict';

	dw.Model = Backbone.Model.extend({
		defaults: {
			id    : 0,
			url   : '',
			title : '',
			nonce : ''
		},

		url: function() {
			// add query vars to our ajax url
			return window.ajaxurl +'?action=dw_insta_blacklist&id='+ encodeURIComponent( this.get( 'id' ) ) +'&nonce=' + encodeURIComponent( this.get( 'nonce' ) );
		}
	} );

	dw.Collection = Backbone.Collection.extend({ model : dw.Model });

	dw.Views = {};
	dw.Views.Table = Backbone.View.extend({
		rows: [],
		events : {
			'click thead input' : 'toggleAll',
			'change [type="checkbox"]' : 'maybeEnableButton',
			'click tfoot button' : 'deleteAll'
		},

		initialize: function() {
			var self = this;
			// create a sub view for every model in the collection
			this.collection.each( function( model ) {
				self.rows.push( new dw.Views.Row({ model: model }) );
			});

			this.$el.show();
			this.render();
		},

		maybeEnableButton: function( evt ) {
			this.$( 'tfoot button' ).prop( 'disabled', ! this.$( '.deleted-blacklist-row [type="checkbox"]:checked' ).length );
		},

		toggleAll: function( evt ) {
			var checked = $( evt.currentTarget ).is( ':checked' );
			this.$( 'td [type="checkbox"]' ).prop( 'checked', checked );
		},

		deleteAll: function( evt ) {
			var self = this;
			var $checked = this.$( '.deleted-blacklist-row [type="checkbox"]:checked' );

			if ( $checked.length && confirm( $( evt.currentTarget ).data( 'confirm' ) ) ) {
				$checked.each( function() {
					var id = $( this ).val();

					var model = self.collection.find( function( model ) {
						return model.get( 'id' ) === id;
					} );

					model.trigger( 'maybeDelete' );
				} );
			}
		},

		rowsHtml: function() {
			var addedElements = document.createDocumentFragment();
			_.each( this.rows, function( row ) {
				addedElements.appendChild( row.render().el );
			});

			return addedElements;
		},

		render: function() {
			this.$( 'tbody' ).html( this.rowsHtml() );
		}
	});

	dw.Views.Row = Backbone.View.extend({
		tagName : 'tr',
		className : 'deleted-blacklist-row',
		id : function() {
			return 'blacklist-item-' + this.model.get( 'id' );
		},
		template : wp.template( 'dw-deleted-blacklist-row' ),
		events : {
			'click a.delete-from-blacklist' : 'deleteIt'
		},

		initialize: function() {
			this.listenTo( this.model, 'maybeDelete', this.doDelete );
		},

		// Render the row
		render: function() {
			var html = this.template( this.model.toJSON() );
			this.$el.html( html );
			return this;
		},

		// Perform the Denial
		deleteIt: function( evt ) {
			evt.preventDefault();
			if ( confirm( $( evt.currentTarget ).data( 'confirm' ) ) ) {
				this.doDelete();
			}
		},

		// Perform the Denial
		doDelete: function() {
			var self = this;

			// Ajax error handler
			var destroyError = function( model, response ) {
				log( 'destroyError response', response );
				// whoops.. re-show row
				self.$el.fadeIn( 300 );
			};

			// Ajax success handler
			var destroySuccess = function( model, response ) {
				// If our response reports success
				if ( response.success ) {
					// remove our row completely
					self.$el.remove();
				} else {
					// whoops, error
					destroyError( model, response );
				}
			};

			// Optimistically hide row
			self.$el.fadeOut( 300 );

			// Remove model and fire ajax event
			this.model.destroy({ success: destroySuccess, error: destroyError, wait: true });
		}
	});

	dw.init = function() {
		var $table = $( document.getElementById( 'deleted-blacklist' ) );
		if ( ! $table.length ) {
			return;
		}

		// Get our attachment model data from the dom, and initiate the collection
		var collection = new dw.Collection( dw.deleted );

		// Send the model data to our table view
		dw.collectionView = new dw.Views.Table({
			collection: collection,
			el: $table
		});

	};

	$( dw.init );

} )( window, document, jQuery, window.dwinstagram );
