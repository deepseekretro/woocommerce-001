( () => {
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
            cartLevel : {
                cartSchema : JSON.parse( "{\"name\":\"woocommerce/cart-order-summary-enr-cart-level-subscription-block\",\"icon\":\"backup\",\"keywords\":[\"subscription\",\"cart\"],\"version\":\"1.0.0\",\"title\":\"Cart Level Subscription\",\"description\":\"Shows the cart level subscription.\",\"category\":\"woocommerce\",\"supports\":{\"align\":false,\"html\":false,\"multiple\":false,\"reusable\":false},\"attributes\":{\"className\":{\"type\":\"string\",\"default\":\"\"},\"lock\":{\"type\":\"object\",\"default\":{\"remove\":true,\"move\":false}}},\"parent\":[\"woocommerce/cart-totals-block\"],\"textdomain\":\"enhancer-for-woocommerce-subscriptions\",\"apiVersion\":2}" ),
                checkoutSchema : JSON.parse( "{\"name\":\"woocommerce/checkout-order-summary-enr-cart-level-subscription-block\",\"icon\":\"backup\",\"keywords\":[\"subscription\",\"cart\"],\"version\":\"1.0.0\",\"title\":\"Cart Level Subscription\",\"description\":\"Shows the cart level subscription.\",\"category\":\"woocommerce\",\"supports\":{\"align\":false,\"html\":false,\"multiple\":false,\"reusable\":false},\"attributes\":{\"className\":{\"type\":\"string\",\"default\":\"\"},\"lock\":{\"type\":\"object\",\"default\":{\"remove\":true,\"move\":false}}},\"parent\":[\"woocommerce/checkout-totals-block\"],\"textdomain\":\"enhancer-for-woocommerce-subscriptions\",\"apiVersion\":2}" ),
                init : function() {
                    return external_element.createElement( external_wc_blocksCheckout.TotalsWrapper, { className : "enr-cart-level-subscription-form-wrapper" },
                            external_element.createElement( callBack.cartBlocks.cartLevel.subscribeSelector ) );
                },
                edit : function( e ) {
                    return external_element.createElement( "div", external_blockEditor.useBlockProps(),
                            external_element.createElement( callBack.cartBlocks.cartLevel.init ) );
                },
                save : function( e ) {
                    return external_element.createElement( "div", external_blockEditor.useBlockProps.save() );
                },
                subscribeSelector : function( e ) {
                    return external_element.createElement( external_wc_blocksCheckout.TotalsItem, {
                        className : "enr-cart-level-subscription-form-wrapper__subscribe-now",
                        label : external_element.createElement( external_wc_blocksCheckout.CheckboxControl, {
                            id : "enr_subscribe_now",
                            className : "enr-subscribe-now",
                            disabled : true
                        }, external_i18n.__( "Subscribe Now", 'enhancer-for-woocommerce-subscriptions' ) ) } );
                }
            }
        }
    };

    const { pages_to_render_cart_level : pagesToRenderCartLevel } = external_wc_settings.getSetting( 'enhancer-for-woocommerce-subscriptions_data' );

    if ( pagesToRenderCartLevel.length ) {
        // Register Block in the Editor.

        if ( 'cart' === pagesToRenderCartLevel[0] ) {
            external_blocks.registerBlockType( callBack.cartBlocks.cartLevel.cartSchema.name, {
                title : callBack.cartBlocks.cartLevel.cartSchema.title, // Localize title using wp.i18n.__()
                version : callBack.cartBlocks.cartLevel.cartSchema.version,
                description : callBack.cartBlocks.cartLevel.cartSchema.description,
                category : callBack.cartBlocks.cartLevel.cartSchema.category, // Category Options: common, formatting, layout, widgets, embed
                supports : callBack.cartBlocks.cartLevel.cartSchema.supports,
                icon : callBack.cartBlocks.cartLevel.cartSchema.icon, // Dashicons Options – https://goo.gl/aTM1DQ
                keywords : callBack.cartBlocks.cartLevel.cartSchema.keywords, // Limit to 3 Keywords / Phrases
                parent : callBack.cartBlocks.cartLevel.cartSchema.parent,
                textdomain : callBack.cartBlocks.cartLevel.cartSchema.textdomain,
                apiVersion : callBack.cartBlocks.cartLevel.cartSchema.apiVersion,
                attributes : callBack.cartBlocks.cartLevel.cartSchema.attributes, // Attributes set for each piece of dynamic data used in your block
                edit : callBack.cartBlocks.cartLevel.edit, // Determines what is displayed in the editor
                save : callBack.cartBlocks.cartLevel.save // Determines what is displayed on the frontend
            } );
        }

        if ( 'checkout' === pagesToRenderCartLevel[0] || 'checkout' === pagesToRenderCartLevel[1] ) {
            external_blocks.registerBlockType( callBack.cartBlocks.cartLevel.checkoutSchema.name, {
                title : callBack.cartBlocks.cartLevel.checkoutSchema.title, // Localize title using wp.i18n.__()
                version : callBack.cartBlocks.cartLevel.checkoutSchema.version,
                description : callBack.cartBlocks.cartLevel.checkoutSchema.description,
                category : callBack.cartBlocks.cartLevel.checkoutSchema.category, // Category Options: common, formatting, layout, widgets, embed
                supports : callBack.cartBlocks.cartLevel.checkoutSchema.supports,
                icon : callBack.cartBlocks.cartLevel.checkoutSchema.icon, // Dashicons Options – https://goo.gl/aTM1DQ
                keywords : callBack.cartBlocks.cartLevel.checkoutSchema.keywords, // Limit to 3 Keywords / Phrases
                parent : callBack.cartBlocks.cartLevel.checkoutSchema.parent,
                textdomain : callBack.cartBlocks.cartLevel.checkoutSchema.textdomain,
                apiVersion : callBack.cartBlocks.cartLevel.checkoutSchema.apiVersion,
                attributes : callBack.cartBlocks.cartLevel.checkoutSchema.attributes, // Attributes set for each piece of dynamic data used in your block
                edit : callBack.cartBlocks.cartLevel.edit, // Determines what is displayed in the editor
                save : callBack.cartBlocks.cartLevel.save // Determines what is displayed on the frontend
            } );
        }
    }
} )();