<?php
    /**
     * Created by PhpStorm.
     * User: Optimistic
     * Date: 21/03/2015
     * Time: 08:01
     */

    namespace Maverickslab\Ebay;


    use Aws\Common\Aws;
    use Aws\Common\Facade\S3;
    use Aws\S3\S3Client;

    class Picture
    {
        use InjectAPIRequester;

        public function upload($user_token, $url, $site_id = 0)
        {
            $url = str_replace("https://","http://", $url);

            $inputs = [];
            $inputs['RequesterCredentials'] = [
                'eBayAuthToken' => $user_token
            ];
            $inputs['ExternalPictureURL'] = [$url];
            $inputs['PictureSet'] = ['Supersize'];

            return $this->requester->request($inputs, 'UploadSiteHostedPictures', $site_id);
        }
    }