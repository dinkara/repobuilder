<?php
/**
 * Created by PhpStorm.
 * User: Dinkola
 */

namespace Dinkara\RepoBuilder\Utils;

use Exception;
class RepoBuilderException extends Exception
{
    const CODE = 501;
    public function __construct(Exception $e = null, $customMessage = null)
    {
        $trans = $customMessage ? $customMessage : trans('repobuilder.exceptions.default');
        if(!$customMessage){
            $log =debug_backtrace();
            if(is_array($log) && count($log) > 1 && isset($log[1]['class']) && isset($log[1]['function'])){
                $file = explode('\\', $log[1]['class']);
                if(is_array($file)){
                    $class = $file[count($file)-1];
                    $function = $log[1]['function'];
                    $trans = trans('repobuilder.exceptions.'. $class . '.' .$function);
                }
            }
        }

        parent::__construct($trans, $this::CODE, $e);
    }

}