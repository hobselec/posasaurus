<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
use Log;

class ActionLog
{
    /**
     * Handle an incoming request.
     *
     * logs the controller/action and important request parameters (id, user_id, ?)
     * 
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
      if($request->isMethod('get'))
        return $next($request);

      $action = $request->route()->getAction()['controller'] ?? '';

      $loggedFields = [];
      $params = array_merge($request->route()->parameters(), $request->all());
      

      // only save important params if there are more than 3
      //$len = strlen(json_encode($params));
      // these controller actions produce too much output, so don't log it
      $blackListActions = ["App\\Http\\Controllers\\PositionAdminController@save"];
      
      if(in_array($action, $blackListActions))
        $loggedFields = [];
     // else if(count($params) > 3)
     // {
        // log select fields, this could be buried deep so using iterator
/*        $iterator  = new \RecursiveArrayIterator($params);
        $recursive = new \RecursiveIteratorIterator($iterator,\RecursiveIteratorIterator::SELF_FIRST);

        foreach ($recursive as $key => $value) {
            if ($key === 'customer_id' || $key == 'userId')
                $loggedFields['user_id'] = $value;
            if($key == 'id')
                $loggedFields['id'] = $value;
        }*/
   //   }
      else if(count($params) > 0)
        $loggedFields = $params;
      else
        $loggedFields = null;

      if($action != '' && Auth::user())
        Log::channel('access')->info('Success', ['user'=> Auth::user()->email, 'controller' => $action, 'params' => $loggedFields]);



        return $next($request);
    }
}
