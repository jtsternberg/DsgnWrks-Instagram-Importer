window.dwinstagram = window.dwinstagram || {};

jQuery(document).ready(function($) {
	var dw = window.dwinstagram;
	var spinner = $('.spinner-wrap, .spinner-wrap .spinner');
	var strong = spinner.next('strong').hide();
	var messagesDiv = $('.updated.instagram-import-message');
	var msgSpinner = $('.spinner', messagesDiv);
	var msgList = $('ol', messagesDiv);
	var doingloop = false;
	var import_continue = true;


	dw.log = function() {
		dw.log.history = dw.log.history || [];
		dw.log.history.push( arguments );
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

	// when clicking "import"
	$( document.body )
	.on( 'click', '.button-secondary.import-button', function(event) {
		event.preventDefault();

		var data = $(this).data();
		spinner.show();
		import_continue = true;

		// import our photos
		instagramAjax(data.instagramuser, false, data.reimport);
	})
	// Stop button
	.on( 'click', '#insta-import-stop', function( evt ) {
		evt.preventDefault();
		import_continue = false;

		$(this).text( dw.stopping );
	})
	.on( 'click', '.instagram-import-message a.dashicons-trash', function( evt ) {
		evt.preventDefault();
		var $this = $( this );
		if ( confirm( dw.confirm_trash ) ) {
			$this.parents( 'li' ).fadeOut( 300 );
			$.get( $this.attr( 'href' ), function() {
				$this.parents( 'li' ).remove();
			} ).fail(function() {
				$this.parents( 'li' ).fadeIn( 300 );
				alert( dw.failed_trash );
			} );
		}
	});

	function instagramAjax(userid, next_url, reimport) {
		var data = {
			action: 'dsgnwrks_instagram_import',
			instagram_user: userid,
			reimport: reimport ? 1 : 0,
		};
		if ( next_url ) {
			data.next_url = next_url;
		}

		$.ajax({
			type     : "post",
			dataType : "json",
			url      : window.ajaxurl,
			data     : data,
			success  : instagramSuccess,
			error    : instagramError
		});
	}

	// ajax success handler
	function instagramSuccess(response) {
		spinner.hide();

		$('#message').remove();
		if ( ! doingloop ) {
			window.scrollTo(0, 0);
		}

		if ( response.success ) {
			var next_url = typeof response.data.next_url !== 'undefined' ? response.data.next_url : false;
			var userid = typeof response.data.userid !== 'undefined' ? response.data.userid : false;
			var reimport = typeof response.data.reimport !== 'undefined' ? response.data.reimport : false;

			dw.log('response.data.messages', response.data.messages);

			msgList.append(response.data.messages);
			messagesDiv.show();
			messagesDiv.find( '.dw-all-done' ).remove();

			if ( ! messagesDiv.find( '#insta-import-stop' ).length ) {
				messagesDiv.append('<div class="clear"><a class="button" id="insta-import-stop" href="#">'+ dw.cancel_import +'</a></div>');
			} else {
				messagesDiv.find( '#insta-import-stop' ).show().text( dw.cancel_import );
			}

			// If we want to loop again
			if ( next_url && userid && import_continue ) {
				dw.log('we want to loop again');
				msgSpinner.addClass( 'is-active' ).show();
				doingloop = true;
				return instagramAjax(userid, next_url, reimport);
			} else if ( next_url && userid && ! import_continue ) {
				dwAllDone();
			} else {
				window.scrollTo(0, 0);

				// ok, we're done looping
				msgSpinner.removeClass( 'is-active' ).hide();
				messagesDiv.find( '#insta-import-stop' ).hide();

				if ( ! $( '.instagram-import-message ol li' ).length ) {
					$('#icon-tools + h2').after( '<div id="message" class="updated"><p>'+ dw.no_new_to_import +'</p></div>' );
				}

				if ( ! messagesDiv.find( '#instagram-remove-messages' ).length ) {
					messagesDiv.show().append('<div class="clear"><a class="button" id="instagram-remove-messages" href="#">'+ dw.hide +'</a></div>');
				}
			}

		}
		else {
			dwAllDone();
		}
	}

	function dwAllDone() {
		window.scrollTo(0, 0);

		if ( doingloop ) {
			doingloop = false;
			// ok, we're done looping
			msgSpinner.removeClass( 'is-active' ).hide();
			messagesDiv.find( '#insta-import-stop' ).hide();
			if ( ! messagesDiv.find( '#instagram-remove-messages' ).length ) {
				messagesDiv.append('<div class="clear"><a class="button" id="instagram-remove-messages" href="#">'+ dw.hide +'</a><span class="dw-all-done">&nbsp;&nbsp;'+ dw.all_done +'</span></div>');
			} else {

				if ( ! messagesDiv.find( '#instagram-remove-messages + .dw-all-done' ).length ) {
					messagesDiv.find( '#instagram-remove-messages' ).after('<span class="dw-all-done">&nbsp;&nbsp;'+ dw.all_done +'</span>');
				}
			}

		}
		// Just a standard "no photos" response
		else {

			$('#icon-tools + h2').after( '<div id="message" class="updated"><p>'+ dw.no_new_to_import +'</p></div>' );
		}
	}

	// ajax error handler
	function instagramError(xhr, ajaxOptions, thrownError) {
		console.warn(xhr.status);
		console.warn('thrownError', thrownError);
		console.warn('ajaxOptions', ajaxOptions);
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

	var Item = Backbone.Model.extend({
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

	var ItemRow = Backbone.View.extend({
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
			this.listenTo( this.model, 'hide', this.hide );
			this.listenTo( this.model, 'show', this.show );
			this.listenTo( this.model, 'destroy', this.destroy );
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
				dw.log( 'destroyError response', response );
				// whoops.. re-show row
				self.show();
			};

			// Ajax success handler
			var destroySuccess = function( model, response ) {
				// If our response reports success
				if ( response.success ) {
					// remove our row completely
					self.destroy();
				} else {
					// whoops, error
					destroyError( model, response );
				}
			};

			// Optimistically hide row
			self.hide();

			// Remove model and fire ajax event
			this.model.destroy({ success: destroySuccess, error: destroyError, wait: true });
		},

		destroy: function () {
			this.undelegateEvents();
			this.$el.removeData().unbind();

			this.remove();
		},

		show: function() {
			this.$el.fadeIn( 300 );
		},

		hide: function() {
			this.$el.fadeOut( 300 );
		}

	});

	var BlacklistTableView = Backbone.View.extend({
		$info: {},
		rows: [],
		events : {
			'click thead input' : 'toggleAll',
			'change [type="checkbox"]' : 'maybeEnableButton',
			'click tfoot button' : 'deleteMany'
		},

		initialize: function() {
			this.listenTo( this.collection, 'destroy reset', this.render );
			this.listenTo( this.collection, 'loading', this.renderLoading );

			this.$info = $( document.getElementById( 'deleted-blacklist-info' ) );
			this.render();
		},

		maybeEnableButton: function( evt ) {
			this.$( 'tfoot button' ).prop( 'disabled', ! this.$( '.deleted-blacklist-row [type="checkbox"]:checked' ).length );
		},

		toggleAll: function( evt ) {
			var checked = $( evt.currentTarget ).is( ':checked' );
			this.$( 'td [type="checkbox"]' ).prop( 'checked', checked );
		},

		deleteMany: function( evt ) {
			var self = this;
			var $checked = this.$( '.deleted-blacklist-row [type="checkbox"]:checked' );

			if ( $checked.length && confirm( $( evt.currentTarget ).data( 'confirm' ) ) ) {
				var ids = {};
				$checked.each( function() {
					var id = $( this ).val();

					var model = self.collection.find( function( model ) {
						return model.get( 'id' ) === id;
					} );

					if ( 1 === $checked.length ) {
						model.trigger( 'maybeDelete' );
					} else {
						ids[ id ] = model.get( 'nonce' );
						model.trigger( 'hide' );
					}
				} );

				if ( $checked.length > 1 ) {
					$.post( window.ajaxurl, {
						action: 'dw_insta_blacklist_remove_many',
						ids: ids
					} ).done( function( response ) {
						dw.log( 'dw_insta_blacklist_remove_many response', response );

						if ( response && response.data ) {
							if ( response.data.removed ) {
								self.loopIdsAndTrigger( response.data.removed, 'destroy' );
							}
							if ( response.data.not_removed ) {
								self.loopIdsAndTrigger( response.data.not_removed, 'show' );
							}
						}
					});
				}
			}
		},

		loopIdsAndTrigger: function( ids, eventName ) {
			var self = this;
			_.each( ids, function( id ) {
				var model = self.collection.find( function( model ) {
					return model.get( 'id' ) === id;
				} );

				if ( model ) {
					model.trigger( eventName, model );
				}
			} );

		},

		rowsHtml: function() {
			var addedElements = document.createDocumentFragment();
			this.collection.each( function( model ) {
				var view = new ItemRow({ model: model });
				addedElements.appendChild( view.render().el );
			});

			return addedElements;
		},

		render: function() {
			this.$( 'tbody' ).html( this.rowsHtml() );

			if ( this.collection.length ) {
				this.$info.show();
				this.$el.show();
			} else {
				this.$info.hide();
				this.$el.hide();
			}
		},

		renderLoading: function() {
			this.$( 'tbody' ).html( '<tr><td colspan="2" style="background:#fff"><div class="spinner is-active"></div>'+ window.dwinstagram.loading_text +'</td></tr>' );
		}
	});

	dw.refreshBlacklist = function() {
		dw.blacklistView.renderLoading();

		$.post( window.ajaxurl, {
			action: 'dw_insta_get_blacklist',
		} ).done( function( response ) {
			if ( response && response.success && response.data ) {
				dw.initBlacklist( response.data );
			}
		});
	};

	dw.initBlacklist = function( data ) {
		if ( ! dw.blacklistView ) {
			var Collection = Backbone.Collection.extend({ model : Item });

			// Initiate the collection, and the collection view.
			dw.blacklistView = new BlacklistTableView({
				collection : new Collection( data ),
				el         : $( document.getElementById( 'deleted-blacklist' ) )
			});
		} else {
			// Already exists, so simply reset the data.
			dw.blacklistView.collection.reset( data );
		}
	};

	dw.init = function() {
		dw.initBlacklist( dw.deleted );

		// When changing to the tab w/ the blacklist, refresh the blacklist.
		$('.contextual-help-tabs a').on( 'click', function(e) {
			if ( '#universal-options' === $(this).attr('href') ) {
				dw.refreshBlacklist();
			}
		});
	};

	$( dw.init );

} )( window, document, jQuery, window.dwinstagram );
