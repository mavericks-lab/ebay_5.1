<?php
    /**
     * Created by PhpStorm.
     * User: Optimistic
     * Date: 21/03/2015
     * Time: 08:01
     */

    namespace Maverickslab\Ebay;

    use Cloudinary;
    use Cloudinary\Uploader;

    class Picture
    {
        use InjectAPIRequester;

        public function upload($user_token, $url, $site_id = 0)
        {
            $image = self::resize($url);

            if ($image) {
                $inputs = [];
                $inputs['RequesterCredentials'] = [
                    'eBayAuthToken' => $user_token
                ];
                $inputs['ExternalPictureURL'] = [$image];
                $inputs['PictureSet'] = ['Supersize'];

                return $this->requester->request($inputs, 'UploadSiteHostedPictures', $site_id);
            }

            return null;
        }

        public function resize($image_url)
        {
            try {
                $file_info = pathinfo($image_url);
                list($original_width, $original_height) = getimagesize(urldecode($image_url));

                if ($original_width >= 1500 || $original_height >= 1500) {
                    return $image_url;
                }

                $desired_width = 1500;
                $desired_height = ceil(($desired_width / $original_width) * $original_height);

                $image_url = "https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?url=$image_url&rewriteMime=image/*&resize_w=$desired_width&resize_h=$desired_height&container=url";

                $filename = "ebay_" . $file_info['filename'] . "" . uniqid();
                $filename = storage_path() . "/" . $filename . ".jpg";//. $file_info['extension'];

                file_put_contents($filename, file_get_contents($image_url));

                $cloudinary_image = self::uploadToCloudinary($filename);
                unlink($filename);

                return $cloudinary_image['url'];
            } catch (\Exception $ex) {
                \Log::info("Image could not be resized");

                return [
                    'Ack'     => 'failure',
                    'message' => 'Image URL not available'
                ];
            }
        }

        public function uploadToCloudinary($image_path)
        {
            Cloudinary::config([
                "cloud_name" => config('ebay.cloudinary_cloud_name'),
                "api_key"    => config('ebay.cloudinary_api_key'),
                "api_secret" => config('ebay.cloudinary_api_secret')
            ]);

            return Uploader::upload($image_path);
        }
    }