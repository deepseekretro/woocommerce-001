/* global enr_frontend_params */

jQuery( function( $ ) {
    'use strict';

    var is_blocked = function( $node ) {
        return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
    };

    /**
     * Block a node visually for processing.
     *
     * @param {JQuery Object} $node
     */
    var block = function( $node ) {
        $.blockUI.defaults.overlayCSS.cursor = 'wait';

        if ( ! is_blocked( $node ) ) {
            $node.addClass( 'processing' ).block( {
                message : null,
                overlayCSS : {
                    background : '#fff',
                    opacity : 0.6
                }
            } );
        }
    };

    /**
     * Unblock a node after processing is complete.
     *
     * @param {JQuery Object} $node
     */
    var unblock = function( $node ) {
        $node.removeClass( 'processing' ).unblock();
    };

    // Hide variable level limit notice when it is limited to variation level
    if ( 'yes' === enr_frontend_params.hide_variable_limited_notice && $( 'form.variations_form' ).length && $( 'form.variations_form' ).find( '.limited-subscription-notice' ).length ) {
        $( 'form.variations_form' ).find( '.limited-subscription-notice' ).hide();
    }

    function maybeProcessSwitchRequest() {
        if ( window.location.href.indexOf( 'switch-subscription' ) != - 1 && window.location.href.indexOf( 'item' ) != - 1 ) {
            $( '.product form.cart' ).prop( 'action', '' );
        }
    }

    /**
     * Handle product page subscribe events.
     */
    var wcs_variations_form = {
        variationForm : $( '.variations_form' ),
        cartForm : $( 'form.cart' ),
        init : function() {
            $( document ).on( 'found_variation.wc-variation-form', this.onFoundVariation );
            $( document ).on( 'reset_data', this.onResetVariation );
        },
        onFoundVariation : function( evt, variation ) {
            wcs_variations_form.onResetVariation();

            if ( variation.enr_limited_subscription_notice ) {
                wcs_variations_form.variationForm.find( '.woocommerce-variation-add-to-cart' ).after( variation.enr_limited_subscription_notice );
            }

            if ( variation.enr_resubscribe_link ) {
                wcs_variations_form.variationForm.find( '.woocommerce-variation-add-to-cart' ).after( variation.enr_resubscribe_link );
            }

            if ( variation.enr_subscribe_now_form ) {
                wcs_variations_form.variationForm.find( '.woocommerce-variation-add-to-cart' ).before( variation.enr_subscribe_now_form );
                wcs_variations_form.variationForm.find( '.single_add_to_cart_button' ).text( variation.enr_single_add_to_cart_text );
                wcs_variations_form.variationForm.find( '.enr-subscribe-now-wrapper #enr_subscribe_now' ).change();
            }

            maybeProcessSwitchRequest();
        },
        onResetVariation : function() {
            if ( wcs_variations_form.variationForm.find( '.enr-variation-wrapper' ).length ) {
                wcs_variations_form.variationForm.find( '.enr-variation-wrapper' ).remove();
            }

            if ( wcs_variations_form.variationForm.find( '.enr-subscribe-now-wrapper' ).length ) {
                wcs_variations_form.variationForm.find( '.enr-subscribe-now-wrapper' ).parent().remove();
            }

            wcs_variations_form.variationForm.find( '.single_add_to_cart_button' ).removeClass( 'enr_single_subscribe_button' ).text( enr_frontend_params.single_add_to_cart_text );
        },
    };

    /**
     * Handle subscribe now events.
     */
    var wcs_subscribe_now = {
        wrapper : $( '.enr-subscribe-now-wrapper' ),
        init : function() {
            if ( ! enr_frontend_params.is_user_logged_in && enr_frontend_params.is_checkout && this.wrapper.length > 0 ) {
                if ( enr_frontend_params.cart_level_subscribed ) {
                    this.wrapper.find( 'input,select' ).each( function( e, element ) {
                        $( element ).prop( 'disabled', true );
                    } );
                } else {
                    this.wrapper.hide();
                }
                return;
            }

            $( document ).on( 'change', '#enr_subscribe_now,#enr_subscribe_plans,#enr_subscribe_period,#enr_subscribe_period_interval,#enr_subscribe_length', this.updateAction );
            $( document ).on( 'updated_wc_div, updated_cart_totals', this.mayBeShowForm );

            if ( this.wrapper.length > 0 ) {
                this.wrapper.find( '#enr_subscribe_now' ).change();
                maybeProcessSwitchRequest();
            }
        },
        showForm : function( node ) {
            var wrapper = node;
            wrapper = node.closest( '.enr-subscribe-now-wrapper' );

            if ( wrapper.length > 0 ) {
                if ( wrapper.find( '#enr_subscribe_now' ).is( ':checked' ) ) {
                    wrapper.find( 'tr:gt(0)' ).slideDown();
                } else {
                    wrapper.find( 'tr:gt(0)' ).slideUp();
                }
            }
        },
        mayBeShowForm : function() {
            wcs_subscribe_now.showForm( wcs_subscribe_now.wrapper );
        },
        updateAction : function( evt, refreshed ) {
            evt.preventDefault();

            var $this = $( evt.currentTarget );
            refreshed = refreshed || false;

            wcs_subscribe_now.showForm( $this );
            block( wcs_subscribe_now.getNode() );

            $.ajax( {
                type : 'POST',
                url : enr_frontend_params.ajax_url,
                data : {
                    action : '_enr_subscribe_now',
                    security : enr_frontend_params.subscribe_now_nonce,
                    is_switch_request : enr_frontend_params.is_switch_request,
                    data : $this.closest( '.enr-subscribe-now-wrapper' ).find( ':input[name]' ).serialize()
                },
                success : function( response ) {
                    if ( response.success ) {
                        $this.closest( response.data.subscribe_wrapper_class ).empty().append( response.data.html );
                        wcs_subscribe_now.showForm( $this );

                        if ( $( '.single_add_to_cart_button' ).length ) {
                            if ( '1' !== enr_frontend_params.is_switch_request && $this.closest( '.enr-subscribe-now-wrapper' ).find( '#enr_subscribe_now' ).is( ':checked' ) ) {
                                $( '.single_add_to_cart_button' ).addClass( 'enr_single_subscribe_button' ).text( enr_frontend_params.subscribe_now_button_text );
                            } else {
                                $( '.single_add_to_cart_button' ).removeClass( 'enr_single_subscribe_button' ).text( enr_frontend_params.single_add_to_cart_text );
                            }
                        }

                        if ( false === response.data.refresh || true === refreshed ) {
                            $( document.body ).trigger( 'update_checkout' );

                            if ( $( '.woocommerce-cart-form' ).length ) {
                                $( document.body ).trigger( 'wc_update_cart' );
                            }
                        } else {
                            $( '#enr_subscribe_now' ).trigger( 'change', [ true ] );
                        }

                        $( document.body ).trigger( 'enr_subscribe_form_submitted_success' );
                    }
                },
                complete : function() {
                    unblock( wcs_subscribe_now.getNode() );
                }
            } );
            return false;
        },
        getNode : function() {
            if ( $( '.woocommerce-cart-form' ).length ) {
                return $( 'div.cart_totals' );
            } else if ( $( '.woocommerce-checkout' ).length ) {
                return $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).closest( 'form' );
            } else {
                return $( '.enr-subscribe-now-wrapper' );
            }
        },
    };

    wcs_variations_form.init();
    wcs_subscribe_now.init();
} );
