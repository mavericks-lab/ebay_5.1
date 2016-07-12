<?php
/**
 * Created by PhpStorm.
 * User: Optimistic
 * Date: 12/07/2016
 * Time: 00:38
 */

namespace Maverickslab\Ebay;


class Notification
{
    use InjectAPIRequester;

    public function setNotificationPreferences($user_token)
    {
        $inputs = [];
        $inputs['RequesterCredentials'] = [
            'eBayAuthToken' => $user_token
        ];
        $inputs['ApplicationDeliveryPreferences'] = [
//            'AlertEmail' =>'mailto://info@syncommerceapp.com',
            'AlertEnable'       => 'Enable',
            'ApplicationEnable' => 'Enable',
            'ApplicationURL'    => config('ebay.notificationUrl'),
            'DeviceType'        => 'Platform',
        ];
        $inputs['UserDeliveryPreferenceArray'] = [];

        foreach (config('ebay.notifications') as $notification) {
            $notificationEnable = [];
            $notificationEnable['NotificationEnable'] = [
                'EventType'   => $notification,
                'EventEnable' => 'Enable'
            ];

            array_push($inputs['UserDeliveryPreferenceArray'], $notificationEnable);
        }

        dd($inputs);

        return $this->requester->request($inputs, 'SetNotificationPreferences');
    }
}