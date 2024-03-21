<?php 
/**  VanguardLTE\Http\Middleware \Illuminate\Http\Middleware\TrustProxies
 * */

namespace VanguardLTE\Http\Middleware;
use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
{
    class TrustProxies extends Middleware
    {
        protected $proxies = null;
        protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
    }

}
