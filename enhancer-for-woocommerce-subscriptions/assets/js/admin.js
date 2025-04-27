/* global enr_admin_params, ajaxurl, tinymce */

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

    // Add buttons to subscription plan and email template screen.
    var $subscription_plan_screen = $( 'body.post-new-php.post-type-enr_subsc_plan,body.post-php.post-type-enr_subsc_plan' ),
            $subscription_email_template_screen = $( 'body.post-new-php.post-type-enr_email_template,body.post-php.post-type-enr_email_template' ),
            $subscription_plan_title_action = $subscription_plan_screen.find( '.wp-heading-inline:first' ),
            $subscription_email_template_title_action = $subscription_email_template_screen.find( '.wp-heading-inline:first' );

    if ( $subscription_plan_title_action.length > 0 ) {
        $subscription_plan_title_action.after( '<a class="page-title-action" href="' + enr_admin_params.back_to_all_subscription_plans_url + '">' + enr_admin_params.back_to_all_label + '</a>' );
    }

    if ( $subscription_email_template_title_action.length > 0 ) {
        $subscription_email_template_title_action.after( '<a class="page-title-action" href="' + enr_admin_params.back_to_all_subscription_email_templates_url + '">' + enr_admin_params.back_to_all_label + '</a>' );
    }

    // Storewide options
    $( '#_enr_allow_cancelling' ).change( function() {
        $( '#_enr_allow_cancelling_after,#_enr_allow_cancelling_after_due,#_enr_allow_cancelling_before_due' ).closest( 'tr' ).hide();

        if ( this.checked ) {
            $( '#_enr_allow_cancelling_after,#_enr_allow_cancelling_after_due,#_enr_allow_cancelling_before_due' ).closest( 'tr' ).show();
        }
    } ).change();

    $( '#_enr_apply_old_subscription_price_as' ).change( function() {
        $( '#_enr_notify_subscription_price_update_before' ).closest( 'tr' ).hide();

        if ( 'new-price' === this.value ) {
            $( '#_enr_notify_subscription_price_update_before' ).closest( 'tr' ).show();
        }
    } ).change();

    $( '#_enr_allow_cart_level_subscribe_now' ).change( function() {
        $( '#_enr_cart_level_subscription_plans,#_enr_page_to_display_cart_level_subscribe_now_form,#_enr_cart_level_subscribe_now_form_position_in_checkout_page' ).closest( 'tr' ).hide();

        if ( this.checked ) {
            $( '#_enr_cart_level_subscription_plans,#_enr_page_to_display_cart_level_subscribe_now_form,#_enr_cart_level_subscribe_now_form_position_in_checkout_page' ).closest( 'tr' ).show();
        }
    } ).change();

    function getShippingPeriodOptions( selector, options ) {
        var selected = selector.val();

        selector.empty();
        $.each( options, function( key, value ) {
            if ( value === selected ) {
                selector.append( $( '<option></option>' )
                        .attr( 'value', value ).attr( 'selected', 'selected' )
                        .text( enr_admin_params.period[value] ).val( value ) );
            } else {
                selector.append( $( '<option></option>' )
                        .attr( 'value', value )
                        .text( enr_admin_params.period[value] ) );
            }
        } );
    }

    function setMaxShippingPeriodInterval( period, periodInterval, fPeriod, fPeriodInterval ) {
        var chosenPeriodInterval = parseInt( periodInterval.val() );

        switch ( fPeriod.val() ) {
            case 'day':
                switch ( period.val() ) {
                    case 'day':
                        fPeriodInterval.attr( 'max', ( chosenPeriodInterval - 1 ) );
                        break;
                    case 'week':
                        fPeriodInterval.attr( 'max', ( ( 7 * chosenPeriodInterval ) - 1 ) );
                        break;
                    case 'month':
                        fPeriodInterval.attr( 'max', ( ( 28 * chosenPeriodInterval ) - 1 ) );
                        break;
                    case 'year':
                        fPeriodInterval.attr( 'max', ( ( 365 * chosenPeriodInterval ) - 1 ) );
                        break;
                }
                break;
            case 'week':
                switch ( period.val() ) {
                    case 'day':
                        fPeriodInterval.attr( 'max', 1 );
                        break;
                    case 'week':
                        fPeriodInterval.attr( 'max', ( chosenPeriodInterval - 1 ) );
                        break;
                    case 'month':
                        fPeriodInterval.attr( 'max', ( ( 4 * chosenPeriodInterval ) - 1 ) );
                        break;
                    case 'year':
                        fPeriodInterval.attr( 'max', ( ( 52 * chosenPeriodInterval ) - 1 ) );
                        break;
                }
                break;
            case 'month':
                switch ( period.val() ) {
                    case 'month':
                        fPeriodInterval.attr( 'max', ( chosenPeriodInterval - 1 ) );
                        break;
                    case 'year':
                        fPeriodInterval.attr( 'max', ( ( 12 * chosenPeriodInterval ) - 1 ) );
                        break;
                    default:
                        fPeriodInterval.attr( 'max', 1 );
                        break;
                }
                break;
            case 'year':
                switch ( period.val() ) {
                    case 'year':
                        fPeriodInterval.attr( 'max', ( chosenPeriodInterval - 1 ) );
                        break;
                    default:
                        fPeriodInterval.attr( 'max', 1 );
                        break;
                }
                break;
        }
    }

    function setShippingPeriodOptions( period, periodInterval, fPeriod ) {
        switch ( periodInterval.val() ) {
            case '1':
                switch ( period.val() ) {
                    case 'week':
                        getShippingPeriodOptions( fPeriod, [ "day" ] );
                        break;
                    case 'month':
                        getShippingPeriodOptions( fPeriod, [ "day", "week" ] );
                        break;
                    case 'year':
                        getShippingPeriodOptions( fPeriod, [ "day", "week", "month" ] );
                        break;
                }
                break;
            default :
                switch ( period.val() ) {
                    case 'day':
                        getShippingPeriodOptions( fPeriod, [ "day" ] );
                        break;
                    case 'week':
                        getShippingPeriodOptions( fPeriod, [ "day", "week" ] );
                        break;
                    case 'month':
                        getShippingPeriodOptions( fPeriod, [ "day", "week", "month" ] );
                        break;
                    case 'year':
                        getShippingPeriodOptions( fPeriod, [ "day", "week", "month", "year" ] );
                        break;
                }
                break;
        }

        fPeriod.change();
    }

    // Productwide options
    var wc_metaboxes_product_data = {
        wrapper : $( '#woocommerce-product-data' ),
        variationsWrapper : $( '#variable_product_options' ),
        init : function() {
            if ( 0 === this.wrapper.length ) {
                return false;
            }

            this.wrapper.on( 'change', '#product-type', this.hideSubscribeNowForSubscription );
            this.wrapper.on( 'change', '#_enr_allow_subscribe_now', this.allowSubscribeNow );
            this.wrapper.on( 'change', '#_enr_allow_cancelling_to', this.allowCancelling );
            this.wrapper.on( 'change', '#_enr_allow_price_update_for_old_subscriptions', this.allowPriceUpdate );
            this.wrapper.on( 'change', '#_enr_subscription_price_for_old_subscriptions', this.overrideStorewidePriceUpdate );
            this.wrapper.on( 'change', '#_enr_enable_seperate_shipping_cycle', this.enableSeperateShippingCycle );
            this.wrapper.on( 'change', '#_subscription_limit', this.applySubscriptionLimit );
            this.wrapper.on( 'change', '#_enr_shipping_period', this.setMaxShippingPeriodInterval );
            this.wrapper.on( 'change', '#_subscription_period_interval,#_subscription_period', this.setShippingPeriodOptions );
            this.wrapper.on( 'woocommerce_variations_added woocommerce_variations_loaded', this.variationLoaded );

            this.variationsWrapper.on( 'change', '._enr_allow_subscribe_now', this.allowSubscribeNowForVariation );
            this.variationsWrapper.on( 'change', '._enr_allow_cancelling_to', this.allowCancellingForVariation );
            this.variationsWrapper.on( 'change', '._enr_allow_price_update_for_old_subscriptions', this.allowPriceUpdateForVariation );
            this.variationsWrapper.on( 'change', '._enr_subscription_price_for_old_subscriptions', this.overrideStorewidePriceUpdateForVariation );
            this.variationsWrapper.on( 'change', '._enr_enable_seperate_shipping_cycle', this.enableSeperateShippingCycleForVariation );
            this.variationsWrapper.on( 'change', '._enr_shipping_period', this.setMaxShippingPeriodIntervalForVariation );
            this.variationsWrapper.on( 'change', '.wc_input_subscription_period_interval,.wc_input_subscription_period', this.setShippingPeriodOptionsForVariation );

            $( '#product-type,#_enr_allow_subscribe_now,#_enr_allow_cancelling_to,#_enr_allow_price_update_for_old_subscriptions,#_enr_enable_seperate_shipping_cycle,#_subscription_limit' ).change();

            this._setShippingPeriodOptions( this.wrapper );
        },
        variationLoaded : function() {
            $( '._enr_allow_subscribe_now,._enr_allow_cancelling_to,._enr_allow_price_update_for_old_subscriptions,._enr_enable_seperate_shipping_cycle' ).change();

            if ( 'variable-subscription' === $( '#product-type' ).val() ) {
                $( '.woocommerce_variation' ).each( function() {
                    $( this ).find( '._enr_allow_subscribe_now_fields' ).hide();
                } );
            } else {
                $( '.woocommerce_variation' ).each( function() {
                    $( this ).find( '._enr_allow_subscribe_now_fields' ).show();
                } );
            }

            $( '.wc_input_subscription_period_interval, .wc_input_subscription_period' ).each( function() {
                wc_metaboxes_product_data._setShippingPeriodOptions( $( this ).closest( '.woocommerce_variation' ), true );
            } );
        },
        hideSubscribeNowForSubscription : function( e ) {
            $( e.currentTarget ).closest( '#woocommerce-product-data' ).find( '._enr_allow_subscribe_now_fields' ).show();

            if ( 'subscription' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( '#woocommerce-product-data' ).find( '._enr_allow_subscribe_now_fields' ).hide();
            }
        },
        allowSubscribeNow : function( e ) {
            $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_subscription_plans_field,._enr_subscribe_now_exclude_reminder_emails_field' ).hide();

            if ( $( e.currentTarget ).is( ':checked' ) ) {
                $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_subscription_plans_field,._enr_subscribe_now_exclude_reminder_emails_field' ).show();
            }
        },
        allowSubscribeNowForVariation : function( e ) {
            $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_subscription_plans_field,._enr_subscribe_now_exclude_reminder_emails_field' ).hide();

            if ( $( e.currentTarget ).is( ':checked' ) ) {
                $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_subscription_plans_field,._enr_subscribe_now_exclude_reminder_emails_field' ).show();
            }
        },
        allowCancelling : function( e ) {
            $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_allow_cancelling_after_field,._enr_allow_cancelling_after_due_field,._enr_allow_cancelling_before_due_field' ).hide();

            if ( 'override-storewide' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_allow_cancelling_after_field,._enr_allow_cancelling_after_due_field,._enr_allow_cancelling_before_due_field' ).show();
            }
        },
        allowCancellingForVariation : function( e ) {
            $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_allow_cancelling_after_field,._enr_allow_cancelling_after_due_field,._enr_allow_cancelling_before_due_field' ).hide();

            if ( 'override-storewide' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_allow_cancelling_after_field,._enr_allow_cancelling_after_due_field,._enr_allow_cancelling_before_due_field' ).show();
            }
        },
        allowPriceUpdate : function( e ) {
            $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_subscription_price_for_old_subscriptions_field,._enr_notify_subscription_price_update_before_field' ).hide();

            if ( 'override-storewide' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_subscription_price_for_old_subscriptions_field' ).show();
                $( '#_enr_subscription_price_for_old_subscriptions' ).change();
            }
        },
        overrideStorewidePriceUpdate : function( e ) {
            $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_notify_subscription_price_update_before_field' ).hide();

            if ( 'new-price' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_notify_subscription_price_update_before_field' ).show();
            }
        },
        allowPriceUpdateForVariation : function( e ) {
            $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_subscription_price_for_old_subscriptions_field,._enr_notify_subscription_price_update_before_field' ).hide();

            if ( 'override-storewide' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_subscription_price_for_old_subscriptions_field' ).show();
                $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_subscription_price_for_old_subscriptions' ).change();
            }
        },
        overrideStorewidePriceUpdateForVariation : function( e ) {
            $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_notify_subscription_price_update_before_field' ).hide();

            if ( 'new-price' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_notify_subscription_price_update_before_field' ).show();
            }
        },
        enableSeperateShippingCycle : function( e ) {
            $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_shipping_frequency_field,._enr_enable_seperate_shipping_cycle_for_old_subscriptions_field,._enr_shipping_frequency_sync_date_field' ).hide();

            if ( $( e.currentTarget ).is( ':checked' ) ) {
                $( e.currentTarget ).closest( '#general_product_data' ).find( '._enr_shipping_frequency_field,._enr_enable_seperate_shipping_cycle_for_old_subscriptions_field,._enr_shipping_frequency_sync_date_field' ).show();
                $( '#_enr_shipping_period' ).change();
            }
        },
        enableSeperateShippingCycleForVariation : function( e ) {
            $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_shipping_frequency_field,._enr_enable_seperate_shipping_cycle_for_old_subscriptions_field,._enr_shipping_frequency_sync_date_field' ).hide();

            if ( $( e.currentTarget ).is( ':checked' ) ) {
                $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_shipping_frequency_field,._enr_enable_seperate_shipping_cycle_for_old_subscriptions_field,._enr_shipping_frequency_sync_date_field' ).show();
                $( e.currentTarget ).closest( '.woocommerce_variation' ).find( '._enr_shipping_period' ).change();
            }
        },
        applySubscriptionLimit : function( e ) {
            if ( 'no' === $( e.currentTarget ).val() ) {
                $( e.currentTarget )
                        .closest( '#advanced_product_data' ).find( '._enr_limit_trial_to_one_field' ).show()
                        .closest( '#advanced_product_data' ).find( '._enr_variable_subscription_limit_level_field' ).hide();
            } else {
                $( e.currentTarget )
                        .closest( '#advanced_product_data' ).find( '._enr_limit_trial_to_one_field' ).hide()
                        .closest( '#advanced_product_data' ).find( '._enr_variable_subscription_limit_level_field' ).show();
            }
        },
        setMaxShippingPeriodInterval : function() {
            wc_metaboxes_product_data._setMaxShippingPeriodInterval( wc_metaboxes_product_data.wrapper );
        },
        setMaxShippingPeriodIntervalForVariation : function( e ) {
            wc_metaboxes_product_data._setMaxShippingPeriodInterval( $( e.currentTarget ).closest( '.woocommerce_variation' ), true );
        },
        setShippingPeriodOptions : function() {
            wc_metaboxes_product_data._setShippingPeriodOptions( wc_metaboxes_product_data.wrapper );
        },
        setShippingPeriodOptionsForVariation : function( e ) {
            wc_metaboxes_product_data._setShippingPeriodOptions( $( e.currentTarget ).closest( '.woocommerce_variation' ), true );
        },
        _setShippingPeriodOptions : function( wrapper, isVariation ) {
            var period, fPeriod, periodInterval;
            isVariation = isVariation || false;

            if ( isVariation ) {
                period = wrapper.find( '.wc_input_subscription_period' );
                fPeriod = wrapper.find( '._enr_shipping_period' );
                periodInterval = wrapper.find( '.wc_input_subscription_period_interval' );
            } else {
                period = wrapper.find( '#_subscription_period' );
                fPeriod = wrapper.find( '#_enr_shipping_period' );
                periodInterval = wrapper.find( '#_subscription_period_interval' );
            }

            wrapper.find( '._enr_enable_seperate_shipping_cycle_field' ).show();

            if ( isVariation ) {
                wrapper.find( '._enr_enable_seperate_shipping_cycle' ).change();
            } else {
                wrapper.find( '#_enr_enable_seperate_shipping_cycle' ).change();
            }

            if ( 'day' === period.val() && '1' === periodInterval.val() ) {
                wrapper.find( '._enr_enable_seperate_shipping_cycle_field,._enr_shipping_frequency_field,._enr_enable_seperate_shipping_cycle_for_old_subscriptions_field,._enr_shipping_frequency_sync_date_field' ).hide();
            }

            setShippingPeriodOptions( period, periodInterval, fPeriod );
        },
        _setMaxShippingPeriodInterval : function( wrapper, isVariation ) {
            var enabled, period, fPeriod, periodInterval, fPeriodInterval;
            isVariation = isVariation || false;

            if ( isVariation ) {
                enabled = wrapper.find( '._enr_enable_seperate_shipping_cycle' ).is( ':checked' );
                period = wrapper.find( '.wc_input_subscription_period' );
                fPeriod = wrapper.find( '._enr_shipping_period' );
                periodInterval = wrapper.find( '.wc_input_subscription_period_interval' );
                fPeriodInterval = wrapper.find( '._enr_shipping_period_interval' );
            } else {
                enabled = wrapper.find( '#_enr_enable_seperate_shipping_cycle' ).is( ':checked' );
                period = wrapper.find( '#_subscription_period' );
                fPeriod = wrapper.find( '#_enr_shipping_period' );
                periodInterval = wrapper.find( '#_subscription_period_interval' );
                fPeriodInterval = wrapper.find( '#_enr_shipping_period_interval' );
            }

            if ( ! enabled || 'day' === fPeriod.val() ) {
                wrapper.find( '._enr_shipping_frequency_sync_date_field' ).hide();
            } else {
                wrapper.find( '._enr_shipping_frequency_sync_date_field' ).show();

                if ( 'week' === fPeriod.val() ) {
                    if ( isVariation ) {
                        wrapper.find( '._enr_shipping_frequency_sync_date_field ._enr_shipping_frequency_sync_date_day' ).hide();
                        wrapper.find( '._enr_shipping_frequency_sync_date_field ._enr_shipping_frequency_sync_date_week' ).show();
                    } else {
                        wrapper.find( '._enr_shipping_frequency_sync_date_field #_enr_shipping_frequency_sync_date_day' ).hide();
                        wrapper.find( '._enr_shipping_frequency_sync_date_field #_enr_shipping_frequency_sync_date_week' ).show();
                    }
                } else {
                    if ( isVariation ) {
                        wrapper.find( '._enr_shipping_frequency_sync_date_field ._enr_shipping_frequency_sync_date_week' ).hide();
                        wrapper.find( '._enr_shipping_frequency_sync_date_field ._enr_shipping_frequency_sync_date_day' ).show();
                    } else {
                        wrapper.find( '._enr_shipping_frequency_sync_date_field #_enr_shipping_frequency_sync_date_week' ).hide();
                        wrapper.find( '._enr_shipping_frequency_sync_date_field #_enr_shipping_frequency_sync_date_day' ).show();
                    }
                }
            }

            setMaxShippingPeriodInterval( period, periodInterval, fPeriod, fPeriodInterval );
        }
    };

    wc_metaboxes_product_data.init();

    // Remove the regular order relationship showing via WCS
    if ( $( '.wp-list-table .type-shop_order ._enr_shipping_fulfilment_order_relationship' ).length ) {
        $( '.wp-list-table .type-shop_order' ).find( '._enr_shipping_fulfilment_order_relationship' ).closest( 'td' ).find( 'span.normal_order' ).remove();
    }

    // Sitewide Plan options
    var metaboxes_subscription_plan_data = {
        $wrapper : $( 'table.enr-subscription-plan-data' ),
        onLoad : true,
        init : function() {
            if ( 0 === this.$wrapper.length ) {
                return false;
            }

            this.$wrapper.on( 'change', '.enr-subscription-plan-type-field-row select', this.planTypeSelected );
            this.$wrapper.on( 'change', '.enr-subscription-predefined-plan-price-fields-row select', this.predefinedPeriodSelected );
            this.$wrapper.on( 'change', '.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date_month', this.syncDateMonthChanged );
            this.$wrapper.on( 'change', '.enr-subscription-predefined-plan-shipping-cycle-fields-row #enable_seperate_shipping_cycle', this.enableSeperateShippingCycle );
            this.$wrapper.on( 'change', '.enr-subscription-predefined-plan-shipping-cycle-fields-row #shipping_period', this.setMaxShippingPeriodInterval );
            this.$wrapper.on( 'change', '.enr-subscription-predefined-plan-allow-cancelling-fields-row #allow_cancelling_to', this.allowCancellingTo );
            this.$wrapper.find( '.enr-subscription-plan-type-field-row select,#enable_seperate_shipping_cycle,#allow_cancelling_to' ).change();
        },
        daysInMonth : function( month ) {
            return new Date( Date.UTC( 2001, month, 0 ) ).getUTCDate();
        },
        planTypeSelected : function( e ) {
            metaboxes_subscription_plan_data.$wrapper.find( '.enr-plan-fields-row,.enr-predefined-plan-fields-row,.enr-userdefined-plan-fields-row' ).hide();

            if ( 'predefined' === $( e.currentTarget ).val() ) {
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-plan-fields-row,.enr-predefined-plan-fields-row' ).slideDown();
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-price-fields-row select:eq(1),#allow_cancelling_to' ).change();
            } else {
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-plan-fields-row,.enr-userdefined-plan-fields-row' ).slideDown();
            }
        },
        syncDateMonthChanged : function( e ) {
            var $syncDateDayElement = $( e.currentTarget ).closest( '.subscription-year-sync-wrap' ).find( '#subscription_payment_sync_date_day' );

            if ( $( e.currentTarget ).val() > 0 ) {
                $syncDateDayElement.val( 1 ).attr( { step : "1", min : "1", max : metaboxes_subscription_plan_data.daysInMonth( $( e.currentTarget ).val() ) } ).prop( 'disabled', false );
            } else {
                $syncDateDayElement.val( 0 ).prop( 'disabled', true );
            }
        },
        predefinedPeriodSelected : function( e ) {
            var $length_element = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-length-fields-row select' ),
                    $sync_date_element = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date' ),
                    chosen_billingPeriod = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-price-fields-row select:eq(1)' ).val(),
                    chosen_sync_date = $sync_date_element.val(),
                    chosen_length = $length_element.val();

            if ( 'day' === chosen_billingPeriod ) {
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row' ).hide();
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date' ).val( 0 );
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date_day,.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date_month' ).val( 0 ).trigger( 'change' );
            } else if ( 'year' === chosen_billingPeriod ) {
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row,.enr-subscription-predefined-plan-sync-fields-row .subscription-year-sync-wrap' ).show();
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date' ).val( 0 ).hide();
            } else {
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row' ).show();
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row .subscription-year-sync-wrap' ).hide();
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date' ).val( 0 ).show();
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date_day,.enr-subscription-predefined-plan-sync-fields-row #subscription_payment_sync_date_month' ).val( 0 ).trigger( 'change' );

                $sync_date_element.empty();
                $.each( enr_admin_params.sync_options[chosen_billingPeriod], function( key, description ) {
                    $sync_date_element.append( $( '<option></option>' ).attr( 'value', key ).text( description ) );
                } );

                $sync_date_element.val( 0 );
                $sync_date_element.children( 'option' ).each( function() {
                    if ( this.value === chosen_sync_date ) {
                        $sync_date_element.val( chosen_sync_date );
                        return false;
                    }
                } );
            }

            $length_element.empty();
            $.each( enr_admin_params.subscription_lengths[ chosen_billingPeriod ], function( length, description ) {
                if ( 0 === parseInt( length ) || 0 === ( parseInt( length ) % metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-price-fields-row select:eq(0)' ).val() ) ) {
                    $length_element.append( $( '<option></option>' ).attr( 'value', length ).text( description ) );
                }
            } );

            $length_element.val( 0 );
            $length_element.children( 'option' ).each( function() {
                if ( this.value === chosen_length ) {
                    $length_element.val( chosen_length );
                    return false;
                }
            } );

            if ( true !== metaboxes_subscription_plan_data.onLoad ) {
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row #shipping_period_interval' ).val( '0' );
            }

            metaboxes_subscription_plan_data.onLoad = false;
            metaboxes_subscription_plan_data.setShippingPeriodOptions();
        },
        enableSeperateShippingCycle : function( e ) {
            $( e.currentTarget ).closest( 'table' ).find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row:eq(1)' ).hide();

            if ( $( e.currentTarget ).is( ':checked' ) ) {
                $( e.currentTarget ).closest( 'table' ).find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row:eq(1)' ).show();
            }
        },
        setMaxShippingPeriodInterval : function() {
            var period, fPeriod, periodInterval, fPeriodInterval;

            period = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-price-fields-row #subscription_period' );
            fPeriod = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row #shipping_period' );
            periodInterval = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-price-fields-row #subscription_period_interval' );
            fPeriodInterval = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row #shipping_period_interval' );

            setMaxShippingPeriodInterval( period, periodInterval, fPeriod, fPeriodInterval );
        },
        setShippingPeriodOptions : function() {
            var period, fPeriod, periodInterval;

            period = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-price-fields-row #subscription_period' );
            fPeriod = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row #shipping_period' );
            periodInterval = metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-price-fields-row #subscription_period_interval' );

            metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row' ).show();
            metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row #enable_seperate_shipping_cycle' ).change();

            if ( '1' === periodInterval.val() && 'day' === period.val() ) {
                metaboxes_subscription_plan_data.$wrapper.find( '.enr-subscription-predefined-plan-shipping-cycle-fields-row' ).hide();
            }

            setShippingPeriodOptions( period, periodInterval, fPeriod );
        },
        allowCancellingTo : function( e ) {
            if ( 'override-storewide' === $( e.currentTarget ).val() ) {
                $( e.currentTarget ).closest( 'table' ).find( '.enr-subscription-predefined-plan-allow-cancelling-fields-row:gt(0)' ).show();
            } else {
                $( e.currentTarget ).closest( 'table' ).find( '.enr-subscription-predefined-plan-allow-cancelling-fields-row:gt(0)' ).hide();
            }
        }
    };

    metaboxes_subscription_plan_data.init();

    // Email template options
    var metaboxes_email_template = {
        $wrapper : $( '#_enr_email_template_data, .enr-email-template-data' ),
        init : function() {
            this.$wrapper.on( 'change', '.enr-email-template-email-id-field-row select', this.emailChanged );
            this.$wrapper.on( 'change', '.enr-email-template-email-product-filter-field-row select', this.productFilterChanged );

            if ( this.$wrapper.length > 0 ) {
                this.$wrapper.find( '.enr-email-template-email-product-filter-field-row select' ).change();
            }
        },
        productFilterChanged : function( e ) {
            e.preventDefault();

            metaboxes_email_template.$wrapper
                    .find( '.enr-email-template-email-included-products-field-row,.enr-email-template-email-included-categories-field-row' )
                    .hide();
            metaboxes_email_template.$wrapper.find( '.enr-email-template-email-' + $( e.currentTarget ).val() + '-field-row' ).show();
        },
        emailChanged : function( e ) {
            e.preventDefault();

            $.each( enr_admin_params.email_default_data, function( wc_email_id, wc_email ) {
                if ( wc_email_id !== $( e.currentTarget ).val() ) {
                    return true;
                }

                metaboxes_email_template.$wrapper.find( 'tr:gt(0)' ).each( function() {
                    var $td = $( this ).find( 'td:eq(1)' );

                    if ( $td.length > 0 ) {
                        if ( $( this ).is( '.enr-email-template-email-mapping-key-field-row' ) ) {
                            $td.find( 'input' ).val( '' );
                            $td.find( '.description' ).html( wc_email.description.email_mapping_key );
                        }

                        if ( $( this ).is( '.enr-email-template-email-subject-field-row' ) ) {
                            $td.find( 'input' ).val( '' ).attr( 'placeholder', wc_email.placeholder.email_subject );
                            $td.find( '.description' ).html( wc_email.description.email_subject );
                        }

                        if ( $( this ).is( '.enr-email-template-email-heading-field-row' ) ) {
                            $td.find( 'input' ).val( '' ).attr( 'placeholder', wc_email.placeholder.email_heading );
                            $td.find( '.description' ).html( wc_email.description.email_heading );
                        }

                        if ( $( this ).is( '.enr-email-template-email-content-field-row' ) ) {
                            if ( typeof tinymce !== undefined ) {
                                var editor = tinymce.get( '_email_content' );

                                if ( editor && editor instanceof tinymce.Editor ) {
                                    editor.setContent( wc_email.email_content );
                                } else {
                                    $td.find( 'textarea' ).val( wc_email.email_content );
                                }
                            } else {
                                $td.find( 'textarea' ).val( wc_email.email_content );
                            }
                        }

                        if ( $( this ).is( '.enr-email-template-email-placeholders-field-row' ) ) {
                            var $placeholders_table = $td.find( 'table tbody' );

                            $placeholders_table.empty();
                            $.each( enr_admin_params.email_placeholders[wc_email_id], function( key, purpose ) {
                                $placeholders_table.append( '<tr><td>' + key + '</td><td>' + purpose + '</td></tr>' );
                            } );
                        }
                    }
                } );
            } );
            return false;
        }
    };

    metaboxes_email_template.init();

    // Our emails and Core emails preview
    var emails_preview = {
        init : function() {
            $( document ).on( 'click', '.enr-email-preview', this.collectInputs );
            $( document.body )
                    .on( 'wc_backbone_modal_loaded', function( e, target ) {
                        if ( 'enr-modal-preview-email-inputs' === target ) {

                        }
                    } )
                    .on( 'wc_backbone_modal_response', function( e, target, data ) {
                        if ( 'enr-modal-preview-email-inputs' === target ) {
                            emails_preview.preview();
                        }
                    } );
        },
        collectInputs : function( e ) {
            e.preventDefault();

            var $previewButton = $( this );
            block( $previewButton );

            $.ajax( {
                type : 'GET',
                url : ajaxurl,
                dataType : 'json',
                data : {
                    action : '_enr_collect_preview_email_inputs',
                    security : enr_admin_params.preview_email_inputs_nonce,
                    email_id : $previewButton.data( 'email-id' )
                },
                success : function( response ) {
                    if ( response && response.success ) {
                        $( this ).WCBackboneModal( {
                            template : 'enr-modal-preview-email-inputs',
                            variable : response.data
                        } );
                    }
                },
                complete : function() {
                    unblock( $previewButton );
                }
            } );
            return false;
        },
        preview : function() {
            $.ajax( {
                type : 'GET',
                url : ajaxurl,
                dataType : 'json',
                data : {
                    action : '_enr_preview_email',
                    security : enr_admin_params.preview_email_nonce,
                    data : $( '.enr_email_inputs_wrapper :input[name]' ).serialize()
                },
                success : function( response ) {
                    if ( response.success ) {
                        $( this ).WCBackboneModal( {
                            template : 'enr-modal-preview-email',
                            variable : response.data
                        } );
                    } else {
                        window.alert( response.data.error )
                    }
                }
            } );
            return false;
        },
    };

    emails_preview.init();
} );
