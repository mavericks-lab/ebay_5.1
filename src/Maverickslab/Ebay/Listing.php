<?php
    /**
     * Created by PhpStorm.
     * User: Optimistic
     * Date: 19/03/2015
     * Time: 20:41
     */

    namespace Maverickslab\Ebay;


    class Listing
    {
        use InjectAPIRequester;


        /**
         * publish product to eBay
         *
         * @param     $user_token
         * @param     $listing_data
         * @param int $site_id
         *
         * @return mixed
         */
        public function publish($user_token, $listing_data, $site_id = 0)
        {
            $inputs = self::prepareXML($user_token, $listing_data);

            $verification = self::verify($user_token, $inputs, $site_id);

            if ($verification['Ack'] === "Failure")
                return $verification;

            return $this->requester->request($inputs, 'AddFixedPriceItem', $site_id);
        }

        /**
         * Revise an existing eBay Listing
         *
         * @param     $user_token
         * @param     $listing_data
         * @param int $site_id
         *
         * @return mixed
         */
        public function revise($user_token, $listing_data, $site_id = 0)
        {
            $inputs = self::prepareXML($user_token, $listing_data);

            //return $verification = self::verify($user_token, $inputs, $site_id);

            //            if ($verification['Ack'] === "Failure")
            //                return $verification;

            return $this->requester->request($inputs, 'ReviseFixedPriceItem', $site_id);
        }

        /**
         * Verify data been sent to eBay
         *
         * @param $user_token
         * @param $listing_data
         * @param $site_id
         *
         * @return mixed
         */
        public function verify($user_token, $listing_data, $site_id)
        {
            return $this->requester->request($listing_data, 'VerifyAddFixedPriceItem', $site_id);
        }

        /**
         * check if variation is enabled for a category
         *
         * @param $user_token
         * @param $category_id
         *
         * @return bool
         */
        public function variationEnabled($user_token, $category_id)
        {
            $response = (new Category($this->requester))->getFeatures($user_token, $category_id);

            return ($response['SiteDefaults']['VariationsEnabled'] == "true") ? : false;
        }

        /**
         * create product specific data
         *
         * @param $option_values
         *
         * @return array
         */
        public function createSpecifics($option_values)
        {
            $specifics = [];

            foreach ($option_values as $key => $value) {
                array_push($specifics, [
                    'Name'  => $key,
                    'Value' => $value
                ]);
            }

            return $specifics;
        }

        /**
         * create variation data
         *
         * @param $variations
         *
         * @return array
         */
        public function createVariations($variations)
        {
            $_variations = [];

            foreach ($variations as $variation) {
                $specifics = [];
                foreach ($variation['option_values'] as $key => $value) {
                    array_push($specifics, [
                        'Name'  => $key,
                        'Value' => $value
                    ]);
                }

                $_variations[] = [
                    'Quantity'           => $variation['quantity'],
                    'SKU'                => $variation['sku'],
                    'StartPrice'         => $variation['price'],
                    'VariationSpecifics' => [
                        'NameValueList' => $specifics
                    ]
                ];
            }

            return $_variations;
        }


        /**
         * @param array    $array
         * @param callable $callback
         * @param null     $userdata
         *
         * @return array
         */
        public function array_walk_recursive_delete(array &$array, callable $callback, $userdata = NULL)
        {
            foreach ($array as $key => &$value) {
                if (is_array($value)) {
                    $value = self::array_walk_recursive_delete($value, $callback, $userdata);
                }
                if ($callback($value, $key, $userdata)) {
                    unset($array[ $key ]);
                }
            }

            return $array;
        }

        /**
         * set default if an array index does not exist
         *
         * @param      $array
         * @param      $key
         * @param null $default
         *
         * @return null
         */
        public function setDefaults($array, $key, $default = NULL)
        {
            return isset($array[ $key ]) ? $array[ $key ] : $default;
        }

        /**
         * set up the data in a fashion that can be sent to eBay
         *
         * @param $user_token
         * @param $listing_data
         *
         * @return array
         */
        public function prepareXML($user_token, $listing_data)
        {
            $inputs['RequesterCredentials'] = [
                'eBayAuthToken' => $user_token
            ];
            $inputs['Item'] = [
                'CategoryBasedAttributesPrefill' => true,
                'CategoryMappingAllowed'         => true,
                'ConditionDescription'           => self::setDefaults($listing_data, 'condition_description'),
                'ConditionID'                    => self::setDefaults($listing_data, 'condition_id'),
                'Country'                        => self::setDefaults($listing_data, 'country'),
                'Currency'                       => self::setDefaults($listing_data, 'currency'),
                'Description'                    => self::setDefaults($listing_data, 'description'),
                'DisableBuyerRequirements'       => true,
                'DispatchTimeMax'                => self::setDefaults($listing_data, 'dispatch_max_time'),
                'IncludeRecommendations'         => true,
                'ItemID'                         => self::setDefaults($listing_data, 'item_id'),
                'ItemSpecifics'                  => [
                    'NameValueList' => self::createSpecifics(self::setDefaults($listing_data, 'item_specifics'))
                ],
                'ListingDuration'                => self::setDefaults($listing_data, 'listing_duration'),
                'ListingType'                    => self::setDefaults($listing_data, 'listing_type'),
                'Location'                       => self::setDefaults($listing_data, 'location'),
                'PaymentMethods'                 => self::setDefaults($listing_data, 'payment_methods'),
                'PayPalEmailAddress'             => self::setDefaults($listing_data, 'paypal_email_address'),
                'PictureDetails'                 => [
                    'PhotoDisplay' => 'PicturePack',
                    'PictureURL'   => self::setDefaults($listing_data, 'pictures')
                ],
                'PostalCode'                     => self::setDefaults($listing_data, 'postal_code'),
                'PrimaryCategory'                => [
                    'CategoryID' => self::setDefaults($listing_data, 'category_id')
                ],
                'ProductListingDetails'          => [
                    'BrandMPN' => [
                        'Brand' => self::setDefaults($listing_data, 'brand'),
                        'MPN'   => self::setDefaults($listing_data, 'manufacturer_part_number')
                    ],
                    'EAN'      => self::setDefaults($listing_data, 'ean'),
                    'GTIN'     => self::setDefaults($listing_data, 'gtin'),
                    'ISBN'     => self::setDefaults($listing_data, 'isbn'),
                ],
                'Quantity'                       => self::setDefaults($listing_data, 'quantity'),
                'ReturnPolicy'                   => [
                    'Description'              => self::setDefaults($listing_data, 'return_policy_description'),
                    'RefundOption'             => self::setDefaults($listing_data, 'refund_option'),
                    'ReturnsAcceptedOption'    => self::setDefaults($listing_data, 'returns_accepted'),
                    'ReturnsWithinOption'      => self::setDefaults($listing_data, 'return_within'),
                    'ShippingCostPaidByOption' => self::setDefaults($listing_data, 'shipping_cost_paid_by')
                ],
                'ShippingDetails'                => [
                    'CODCost'                => self::setDefaults($listing_data, 'cost_of_delivery'),
                    'GlobalShipping'         => self::setDefaults($listing_data, 'global_shipping'),
                    'PaymentInstructions'    => self::setDefaults($listing_data, 'payment_instructions'),
                    'ShippingServiceOptions' => [
                        'FreeShipping'                  => self::setDefaults($listing_data, 'free_shipping'),
                        'ShippingService'               => self::setDefaults($listing_data, 'shipping_service'),
                        'ShippingServiceAdditionalCost' => self::setDefaults($listing_data, 'shipping_service_additional_cost'),
                        'ShippingServiceCost'           => self::setDefaults($listing_data, 'shipping_service_cost'),
                        'ShippingServicePriority'       => 1
                    ]
                ],
                'ShippingPackageDetails'         => [
                    'MeasurementUnit' => 'English',
                    'PackageDepth'    => self::setDefaults($listing_data, 'package_depth'),
                    'PackageLength'   => self::setDefaults($listing_data, 'package_length'),
                    'PackageWidth'    => self::setDefaults($listing_data, 'package_width'),
                    'ShippingPackage' => self::setDefaults($listing_data, 'shipping_package'),
                    'WeightMajor'     => self::setDefaults($listing_data, 'weight_major'),
                    'WeightMinor'     => self::setDefaults($listing_data, 'weight_minor'),
                ],
                'ShipToLocations'                => self::setDefaults($listing_data, 'ship_to_locations'),
                'Site'                           => self::setDefaults($listing_data, 'site'),
                'SKU'                            => self::setDefaults($listing_data, 'sku'),
                'StartPrice'                     => self::setDefaults($listing_data, 'price'),
                'Title'                          => self::setDefaults($listing_data, 'title'),
                'Variations'                     => [
                    'Variation'             => self::createVariations(self::setDefaults($listing_data, 'variations')),
                    'VariationSpecificsSet' => [
                        'NameValueList' => self::createSpecifics(self::setDefaults($listing_data, 'option_values'))
                    ]
                ]];

            return self::array_walk_recursive_delete($inputs, function ($value, $key) {
                if (is_array($value)) {
                    return empty($value);
                }

                return ($value === NULL);
            });
        }
    }