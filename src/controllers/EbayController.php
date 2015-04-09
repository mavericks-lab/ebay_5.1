<?php
/**
 * Created by PhpStorm.
 * User: Optimistic
 * Date: 17/03/2015
 * Time: 11:11
 */

namespace Maverickslab\Ebay;


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class EbayController extends Controller{
    public function FetchUserToken(APIRequester $requester){
        $session_id = Session::get('ebay_session_id'); #refactor this for unique tokens
        return (new Authentication($requester))->fetchToken($session_id);
    }
}