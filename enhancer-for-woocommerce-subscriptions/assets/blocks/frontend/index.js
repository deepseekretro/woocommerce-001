( ( ) => {
    'use strict';

    var external_plugins = window["wp"]["plugins"];
    var external_element = window["wp"]["element"];
    var external_blocks = window["wp"]["blocks"];
    var external_blockEditor = window["wp"]["blockEditor"];
    var external_i18n = window["wp"]["i18n"];
    var external_data = window["wp"]["data"];
    var external_compose = window["wp"]["compose"];
    var external_components = window["wp"]["components"];
    var external_primitives = window["wp"]["primitives"];
    var external_wc_blocksCheckout = window["wc"]["blocksCheckout"];
    var external_wc_priceFormat = window["wc"]["priceFormat"];
    var external_wc_settings = window["wc"]["wcSettings"];

    var callBack = {
        ourData : null,
        isOurs : function( e ) {
            // Bail out early.
            if ( undefined === e['enhancer-for-woocommerce-subscriptions'] ) {
                return false;
            }

            callBack.ourData = e['enhancer-for-woocommerce-subscriptions'];
            return true;
        },
        cartBlocks : {
            init : function( e ) {
                if ( callBack.isOurs( e.extensions ) && callBack.ourData.is_available ) {
                    return external_element.createElement( callBack.cartBlocks.cartLevel.init, e );
                }

                return null;
            },
            cartLevel : {
                cartSchema : JSON.parse( "{\"name\":\"woocommerce/cart-order-summary-enr-cart-level-subscription-block\",\"icon\":\"backup\",\"keywords\":[\"subscription\",\"cart\"],\"version\":\"1.0.0\",\"title\":\"Cart Level Subscription\",\"description\":\"Shows the cart level subscription.\",\"category\":\"woocommerce\",\"supports\":{\"align\":false,\"html\":false,\"multiple\":false,\"reusable\":false},\"attributes\":{\"className\":{\"type\":\"string\",\"default\":\"\"},\"lock\":{\"type\":\"object\",\"default\":{\"remove\":true,\"move\":false}}},\"parent\":[\"woocommerce/cart-totals-block\"],\"textdomain\":\"enhancer-for-woocommerce-subscriptions\",\"apiVersion\":2}" ),
                checkoutSchema : JSON.parse( "{\"name\":\"woocommerce/checkout-order-summary-enr-cart-level-subscription-block\",\"icon\":\"backup\",\"keywords\":[\"subscription\",\"cart\"],\"version\":\"1.0.0\",\"title\":\"Cart Level Subscription\",\"description\":\"Shows the cart level subscription.\",\"category\":\"woocommerce\",\"supports\":{\"align\":false,\"html\":false,\"multiple\":false,\"reusable\":false},\"attributes\":{\"className\":{\"type\":\"string\",\"default\":\"\"},\"lock\":{\"type\":\"object\",\"default\":{\"remove\":true,\"move\":false}}},\"parent\":[\"woocommerce/checkout-totals-block\"],\"textdomain\":\"enhancer-for-woocommerce-subscriptions\",\"apiVersion\":2}" ),
                isLoading : false,
                isSubscribed : false,
                planSelected : "",
                intervalSelected : "",
                periodSelected : "",
                lengthSelected : "",
                setLoading : null,
                setIsSubscribed : null,
                setPlanSelected : null,
                setIntervalSelected : null,
                setPeriodSelected : null,
                setLengthSelected : null,
                init : function( e ) {
                    return external_element.createElement( external_element.Fragment, null,
                            external_element.createElement( callBack.cartBlocks.cartLevel.form, null ) );
                },
                form : function() {
                    if ( ! isUserLoggedIn && isCheckout ) {
                        [ callBack.cartBlocks.cartLevel.isLoading, callBack.cartBlocks.cartLevel.setLoading ] = external_element.useState( true );
                    } else {
                        [ callBack.cartBlocks.cartLevel.isLoading, callBack.cartBlocks.cartLevel.setLoading ] = external_element.useState( false );
                    }

                    return external_element.createElement( external_wc_blocksCheckout.TotalsWrapper, { className : "enr-cart-level-subscription-form-wrapper" },
                            ! callBack.ourData.force_subscribe ? external_element.createElement( callBack.cartBlocks.cartLevel.subscribeSelector ) : null,
                            ( callBack.ourData.force_subscribe || callBack.ourData.is_subscribed ) ? external_element.createElement( callBack.cartBlocks.cartLevel.planSelector ) : null,
                            ( callBack.ourData.force_subscribe || callBack.ourData.is_subscribed ) && 'userdefined' === callBack.ourData.subscribed_plan_type ? external_element.createElement( callBack.cartBlocks.cartLevel.userdefinedSelectors ) : null );
                },
                subscribeChanged : function( bool ) {
                    callBack.cartBlocks.cartLevel.setLoading( true );
                    external_wc_blocksCheckout.extensionCartUpdate( {
                        namespace : 'enhancer-for-woocommerce-subscriptions',
                        data : {
                            action : 'subscribe_now',
                            value : bool ? {
                                subscribed : true,
                                subscribed_plan : ( callBack.ourData.chosen_plan > 0 ? callBack.ourData.chosen_plan : callBack.ourData.default_plan ),
                                subscribed_interval : callBack.ourData.chosen_interval,
                                subscribed_period : callBack.ourData.chosen_period,
                                subscribed_length : callBack.ourData.chosen_length
                            } : null
                        }
                    } ).then( function( e ) {
                        callBack.isOurs( e.extensions );
                        callBack.cartBlocks.cartLevel.setIsSubscribed( callBack.ourData.is_subscribed );
                    } ).finally( function() {
                        if ( ! isUserLoggedIn && isCheckout ) {
                            return;
                        }

                        callBack.cartBlocks.cartLevel.setLoading( false );
                    } );
                },
                planChanged : function( value ) {
                    callBack.cartBlocks.cartLevel.setLoading( true );
                    external_wc_blocksCheckout.extensionCartUpdate( {
                        namespace : 'enhancer-for-woocommerce-subscriptions',
                        data : {
                            action : 'subscribe_now',
                            value : {
                                subscribed : callBack.ourData.is_subscribed,
                                subscribed_plan : value,
                                subscribed_interval : callBack.ourData.chosen_interval,
                                subscribed_period : callBack.ourData.chosen_period,
                                subscribed_length : callBack.ourData.chosen_length
                            }
                        }
                    } ).then( function( e ) {
                        callBack.isOurs( e.extensions );
                        callBack.cartBlocks.cartLevel.setPlanSelected( callBack.ourData.chosen_plan );
                    } ).finally( function() {
                        if ( ! isUserLoggedIn && isCheckout ) {
                            return;
                        }

                        callBack.cartBlocks.cartLevel.setLoading( false );
                    } );
                },
                intervalChanged : function( value ) {
                    callBack.cartBlocks.cartLevel.setLoading( true );
                    external_wc_blocksCheckout.extensionCartUpdate( {
                        namespace : 'enhancer-for-woocommerce-subscriptions',
                        data : {
                            action : 'subscribe_now',
                            value : {
                                subscribed : callBack.ourData.is_subscribed,
                                subscribed_plan : callBack.ourData.chosen_plan,
                                subscribed_interval : value,
                                subscribed_period : callBack.ourData.chosen_period,
                                subscribed_length : callBack.ourData.chosen_length
                            }
                        }
                    } ).then( function( e ) {
                        callBack.isOurs( e.extensions );
                        callBack.cartBlocks.cartLevel.setIntervalSelected( callBack.ourData.chosen_interval );
                    } ).finally( function() {
                        if ( ! isUserLoggedIn && isCheckout ) {
                            return;
                        }

                        callBack.cartBlocks.cartLevel.setLoading( false );
                    } );
                },
                periodChanged : function( value ) {
                    callBack.cartBlocks.cartLevel.setLoading( true );
                    external_wc_blocksCheckout.extensionCartUpdate( {
                        namespace : 'enhancer-for-woocommerce-subscriptions',
                        data : {
                            action : 'subscribe_now',
                            value : {
                                subscribed : callBack.ourData.is_subscribed,
                                subscribed_plan : callBack.ourData.chosen_plan,
                                subscribed_interval : callBack.ourData.chosen_interval,
                                subscribed_period : value,
                                subscribed_length : callBack.ourData.chosen_length
                            }
                        }
                    } ).then( function( e ) {
                        callBack.isOurs( e.extensions );
                        callBack.cartBlocks.cartLevel.setPeriodSelected( callBack.ourData.chosen_period );
                    } ).finally( function() {
                        if ( ! isUserLoggedIn && isCheckout ) {
                            return;
                        }

                        callBack.cartBlocks.cartLevel.setLoading( false );
                    } );
                },
                lengthChanged : function( value ) {
                    callBack.cartBlocks.cartLevel.setLoading( true );
                    external_wc_blocksCheckout.extensionCartUpdate( {
                        namespace : 'enhancer-for-woocommerce-subscriptions',
                        data : {
                            action : 'subscribe_now',
                            value : {
                                subscribed : callBack.ourData.is_subscribed,
                                subscribed_plan : callBack.ourData.chosen_plan,
                                subscribed_interval : callBack.ourData.chosen_interval,
                                subscribed_period : callBack.ourData.chosen_period,
                                subscribed_length : value
                            }
                        }
                    } ).then( function( e ) {
                        callBack.isOurs( e.extensions );
                        callBack.cartBlocks.cartLevel.setLengthSelected( callBack.ourData.chosen_length );
                    } ).finally( function() {
                        if ( ! isUserLoggedIn && isCheckout ) {
                            return;
                        }

                        callBack.cartBlocks.cartLevel.setLoading( false );
                    } );
                },
                subscribedPriceString : function( period, interval ) {
                    switch ( interval ) {
                        case '1':
                        case 1:
                            if ( "day" === period )
                                return external_i18n.__( "Billed every day", "enhancer-for-woocommerce-subscriptions" );
                            if ( "week" === period )
                                return external_i18n.__( "Billed every week", "enhancer-for-woocommerce-subscriptions" );
                            if ( "month" === period )
                                return external_i18n.__( "Billed every month", "enhancer-for-woocommerce-subscriptions" );
                            if ( "year" === period )
                                return external_i18n.__( "Billed every year", "enhancer-for-woocommerce-subscriptions" );
                            break;
                        case '2':
                        case 2:
                            return external_i18n.sprintf(
                                    /* translators: %1$s is week, month, year */
                                    external_i18n.__( "Billed every 2nd %1$s", "enhancer-for-woocommerce-subscriptions" ), period );
                            break;
                        case '3':
                        case 3:
                            return external_i18n.sprintf(
                                    /* Translators: %1$s is week, month, year */
                                    external_i18n.__( "Billed every 3rd %1$s", "enhancer-for-woocommerce-subscriptions" ), period );
                            break;
                        default:
                            return external_i18n.sprintf(
                                    /* Translators: %1$d is number of weeks, months, days, years. %2$s is week, month, year */
                                    external_i18n.__( "Billed every %1$dth %2$s", "enhancer-for-woocommerce-subscriptions" ), interval, period );
                    }
                },
                subscribeSelector : function( e ) {
                    [ callBack.cartBlocks.cartLevel.isSubscribed, callBack.cartBlocks.cartLevel.setIsSubscribed ] = external_element.useState( callBack.ourData.is_subscribed );

                    external_element.useEffect( function() {
                        callBack.cartBlocks.cartLevel.subscribeChanged( callBack.cartBlocks.cartLevel.isSubscribed );
                    }, [ callBack.cartBlocks.cartLevel.isSubscribed ] );

                    return external_element.createElement( external_wc_blocksCheckout.TotalsItem, {
                        className : "enr-cart-level-subscription-form-wrapper__subscribe-now",
                        label : external_element.createElement( external_wc_blocksCheckout.CheckboxControl, {
                            id : "enr_subscribe_now",
                            className : "enr-subscribe-now " + ( callBack.cartBlocks.cartLevel.isLoading ? "enr-component--disabled" : "" ),
                            checked : callBack.cartBlocks.cartLevel.isSubscribed,
                            disabled : callBack.cartBlocks.cartLevel.isLoading,
                            onChange : function( bool ) {
                                callBack.cartBlocks.cartLevel.subscribeChanged( bool );
                            }
                        }, callBack.ourData.subscribe_label ) } );
                },
                planSelector : function( e ) {
                    [ callBack.cartBlocks.cartLevel.planSelected, callBack.cartBlocks.cartLevel.setPlanSelected ] = external_element.useState( callBack.ourData.chosen_plan > 0 ? callBack.ourData.chosen_plan : callBack.ourData.default_plan );

                    if ( callBack.ourData.force_subscribe ) {
                        external_element.useEffect( function() {
                            callBack.cartBlocks.cartLevel.planChanged( callBack.cartBlocks.cartLevel.planSelected );
                        }, [ callBack.cartBlocks.cartLevel.planSelected ] );
                    }

                    return external_element.createElement( external_wc_blocksCheckout.TotalsItem, {
                        className : "enr-cart-level-subscription-form-wrapper__subscription-plans",
                        label : callBack.ourData.available_plans.length > 0 ? external_element.createElement( "select", {
                            id : "enr_subscribe_plans",
                            className : "enr-subscribe-plans " + ( callBack.cartBlocks.cartLevel.isLoading ? "enr-component--disabled" : "" ),
                            value : callBack.cartBlocks.cartLevel.planSelected,
                            disabled : callBack.cartBlocks.cartLevel.isLoading,
                            onChange : function( e ) {
                                callBack.cartBlocks.cartLevel.planChanged( e.target.value );
                            }
                        }, external_element.createElement( "optgroup", { label : external_i18n.__( "Select Plan", "enhancer-for-woocommerce-subscriptions" ) }, callBack.ourData.available_plans.map( function( plan ) {
                            return external_element.createElement( "option", { value : plan.id, key : plan.id }, plan.title );
                        } ) ) ) : external_element.createElement( "span", null, external_i18n.__( "No plans available", "enhancer-for-woocommerce-subscriptions" ) ),
                        description : callBack.ourData.is_subscribed ? external_element.createElement( external_element.RawHTML, { className : "enr-plan-meta" }, callBack.ourData.subscribed_price_string ) : null
                    } );
                },
                userdefinedSelectors : function( e ) {
                    [ callBack.cartBlocks.cartLevel.intervalSelected, callBack.cartBlocks.cartLevel.setIntervalSelected ] = external_element.useState( callBack.ourData.chosen_interval );
                    [ callBack.cartBlocks.cartLevel.periodSelected, callBack.cartBlocks.cartLevel.setPeriodSelected ] = external_element.useState( callBack.ourData.chosen_period );
                    [ callBack.cartBlocks.cartLevel.lengthSelected, callBack.cartBlocks.cartLevel.setLengthSelected ] = external_element.useState( callBack.ourData.chosen_length );

                    if ( callBack.ourData.min_length ) {
                        external_element.useEffect( function() {
                            callBack.cartBlocks.cartLevel.lengthChanged( callBack.ourData.min_length.key );
                        }, [ callBack.cartBlocks.cartLevel.periodSelected, callBack.ourData.chosen_period ] );
                    }

                    return external_element.createElement( external_wc_blocksCheckout.TotalsWrapper, { className : "enr-cart-level-subscription-form-wrapper__userdefined" },
                            external_element.createElement( external_wc_blocksCheckout.TotalsItem, {
                                className : "enr-cart-level-subscription-form-wrapper__userdefined__subscribe-period-interval",
                                label : external_i18n.__( 'Subscription billing interval', 'enhancer-for-woocommerce-subscriptions' ),
                                value : callBack.ourData.interval_to_subscribe.length > 0 ? external_element.createElement( "select", {
                                    id : "enr_subscribe_period_interval",
                                    className : "enr-subscribe-period-interval " + ( callBack.cartBlocks.cartLevel.isLoading ? "enr-component--disabled" : "" ),
                                    value : callBack.cartBlocks.cartLevel.intervalSelected,
                                    disabled : callBack.cartBlocks.cartLevel.isLoading,
                                    onChange : function( e ) {
                                        callBack.cartBlocks.cartLevel.intervalChanged( e.target.value );
                                    }
                                }, callBack.ourData.interval_to_subscribe.map( function( interval ) {
                                    return external_element.createElement( "option", { value : interval.key, key : interval.key }, interval.title );
                                } ) ) : null
                            } ),
                            external_element.createElement( external_wc_blocksCheckout.TotalsItem, {
                                className : "enr-cart-level-subscription-form-wrapper__userdefined__subscribe-period",
                                label : external_i18n.__( 'Subscription period', 'enhancer-for-woocommerce-subscriptions' ),
                                value : callBack.ourData.period_to_subscribe.length > 0 ? external_element.createElement( "select", {
                                    id : "enr_subscribe_period",
                                    className : "enr-subscribe-period " + ( callBack.cartBlocks.cartLevel.isLoading ? "enr-component--disabled" : "" ),
                                    value : callBack.cartBlocks.cartLevel.periodSelected,
                                    disabled : callBack.cartBlocks.cartLevel.isLoading,
                                    onChange : function( e ) {
                                        callBack.cartBlocks.cartLevel.periodChanged( e.target.value );
                                    }
                                }, callBack.ourData.period_to_subscribe.map( function( period ) {
                                    return external_element.createElement( "option", { value : period.key, key : period.key }, period.title );
                                } ) ) : null
                            } ),
                            external_element.createElement( external_wc_blocksCheckout.TotalsItem, {
                                className : "enr-cart-level-subscription-form-wrapper__userdefined__subscribe-length",
                                label : external_i18n.__( 'Expire after', 'enhancer-for-woocommerce-subscriptions' ),
                                value : callBack.ourData.length_to_subscribe.length > 0 ? external_element.createElement( "select", {
                                    id : "enr_subscribe_length",
                                    className : "enr-subscribe-length " + ( callBack.cartBlocks.cartLevel.isLoading ? "enr-component--disabled" : "" ),
                                    value : callBack.cartBlocks.cartLevel.lengthSelected,
                                    disabled : callBack.cartBlocks.cartLevel.isLoading,
                                    onChange : function( e ) {
                                        callBack.cartBlocks.cartLevel.lengthChanged( e.target.value );
                                    }
                                }, callBack.ourData.length_to_subscribe.map( function( length ) {
                                    return external_element.createElement( "option", { value : length.key, key : length.key }, length.title );
                                } ) ) : null
                            } ) );
                }
            }
        }
    };

    const {
        pages_to_render_cart_level : pagesToRenderCartLevel,
        is_checkout : isCheckout,
        is_user_logged_in : isUserLoggedIn,
        cart_level_subscribed : cartLevelSubscribed
    } = external_wc_settings.getSetting( 'enhancer-for-woocommerce-subscriptions_data' );

    if ( pagesToRenderCartLevel.length ) {
        if ( 'cart' === pagesToRenderCartLevel[0] ) {
            external_wc_blocksCheckout.registerCheckoutBlock( {
                metadata : callBack.cartBlocks.cartLevel.cartSchema,
                component : callBack.cartBlocks.init
            } );
        }

        if ( 'checkout' === pagesToRenderCartLevel[0] || 'checkout' === pagesToRenderCartLevel[1] ) {
            if ( ! isUserLoggedIn && isCheckout && ! cartLevelSubscribed ) {
                return;
            }

            external_wc_blocksCheckout.registerCheckoutBlock( {
                metadata : callBack.cartBlocks.cartLevel.checkoutSchema,
                component : callBack.cartBlocks.init
            } );
        }
    }
} )( );