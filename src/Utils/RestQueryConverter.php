<?php
/**
 * Created by PhpStorm.
 * User: Dinkola
 */

namespace Dinkara\RepoBuilder\Utils;

use Dinkara\RepoBuilder\Utils\AvailableRestQueryParams;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;

/*
 * Converts query params to usable format for querying
 */
class RestQueryConverter
{
    public $finalParams = [
        AvailableRestQueryParams::_SORT  => [],
        AvailableRestQueryParams::_START => AvailableRestQueryParams::DEFAULT_START,
        AvailableRestQueryParams::_LIMIT => AvailableRestQueryParams::DEFAULT_LIMIT,
        AvailableRestQueryParams::_WHERE => [],
    ];
    public function __construct(Request $req, $tableName = null)
    {
        try{
            $this->tableName = $tableName ? $tableName . '.' : '';
            $params = $req->query();

            if(count($params) === 0){
                return $this->finalParams;
            }

            if(key_exists(AvailableRestQueryParams::_SORT, $params)){
                $this->finalParams[AvailableRestQueryParams::_SORT] = $this->convertSortQueryParams($params[AvailableRestQueryParams::_SORT]);
                unset($params[AvailableRestQueryParams::_SORT]);
            }

            if(key_exists(AvailableRestQueryParams::_START, $params)){
                $this->finalParams[AvailableRestQueryParams::_START] = $this->convertStartQueryParams($params[AvailableRestQueryParams::_START]);
                unset($params[AvailableRestQueryParams::_START]);
            }

            if(key_exists(AvailableRestQueryParams::_LIMIT, $params)){
                $this->finalParams[AvailableRestQueryParams::_LIMIT] = $this->convertLimitQueryParams($params[AvailableRestQueryParams::_LIMIT]);
                unset($params[AvailableRestQueryParams::_LIMIT]);
            }

            if(count($params) > 0){
                $this->finalParams[AvailableRestQueryParams::_WHERE] = $this->convertWhereQueryParams($params);
            }
        } catch (RepoBuilderException $e){//First catch RepoBuilderException for custom messages
            throw $e;
        } catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Returns parsed querying array
     *
     * @return array
     */
    public function getParams(){
        try {
            return $this->finalParams;
        }catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Convert string to sort array
     *
     * @param $sort
     * @return array
     * @throws RepoBuilderException
     */
    private function convertSortQueryParams($sort){
        try {
            $sortKeys = [];

            if(!is_string($sort)){
                throw new RepoBuilderException(null, trans('repobuilder.exceptions.custom.convertSortQueryParams', ['type' => gettype($sort)]));
            }

            foreach (explode(',', $sort) as $part) {
                $field = '';
                $order = AvailableRestQueryParams::ASC;
                $arr = explode(':', $part);
                if (count($arr) > 0) {
                    $field = $arr[0];
                    $order = count($arr) > 1 ? $arr[1] : $order;
                }

                if ($field !== '' && in_array(strtolower($order), AvailableRestQueryParams::sortDirections())) {
                    $item['data'] = [$this->tableName . $field, $order];
                    $item['field'] = $field;
                    array_push($sortKeys, $item);
                }else{
                    throw new RepoBuilderException(null, trans('repobuilder.exceptions.custom.sortingTypes'));
                }
            }
            return $sortKeys;
        }catch (RepoBuilderException $e){
            throw $e;
        } catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Convert string to pagination offset(start)
     *
     * @param $start
     * @return int
     */
    private function convertStartQueryParams($start){
        try {
            /*            if(!is_int($start)){
                            throw new RepoBuilderException(null, trans('repobuilder.exceptions.custom.convertStartQueryParams', ['type' => gettype($start)]));
                        }*/
            return ($start >= 0) ? $start : AvailableRestQueryParams::DEFAULT_START;
        }catch (RepoBuilderException $e){
            throw $e;
        } catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Convert string to pagination limit
     *
     * @param $limit
     * @return int
     */
    private function convertLimitQueryParams($limit){
        try {
            /*            if(!is_int($limit)){
                            throw new RepoBuilderException(null, trans('repobuilder.exceptions.custom.convertLimitQueryParams', ['type' => gettype($limit)]));
                        }*/
            return ($limit >= 0) ? $limit : AvailableRestQueryParams::DEFAULT_LIMIT;
        }catch (RepoBuilderException $e){
            throw $e;
        } catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Convert string to search array
     *
     * @param $where
     * @return array
     */
    private function convertWhereQueryParams($where){
        try {
            $result = [];
            foreach ($where as $key => $item){
                $operatorStart = strpos($key, "_");
                if($key !== 'q' && $key !== -1){//for sofa eloquence builder
                    $operator = substr($key, $operatorStart, strlen ($key)-1 );
                    $columnName = substr($key, 0, $operatorStart);
                    $key = $this->tableName . $columnName;
                    $value = $item;
                    switch ($operator){
                        case AvailableRestQueryParams::IN:{
                            $data['func'] = AvailableRestQueryParams::WHERE_IN;
                            $data['data'] = [$key, explode(',', $value)];
                            break;
                        }
                        case AvailableRestQueryParams::NIN:{
                            $data['func'] = AvailableRestQueryParams::WHERE_NOT_IN;
                            $data['data'] = [$key, explode(',', $value)];
                            break;
                        }
                        case AvailableRestQueryParams::NIL:{
                            $data['func'] = AvailableRestQueryParams::WHERE_NULL;
                            $data['data'] = [$key];
                            break;
                        }
                        case AvailableRestQueryParams::NNIL:{
                            $data['func'] = AvailableRestQueryParams::WHERE_NOT_NULL;
                            $data['data'] = [$key];
                            break;
                        }
                        case AvailableRestQueryParams::BTW:{
                            $data['func'] = AvailableRestQueryParams::WHERE_BETWEEN;
                            $data['data'] = [$key, explode(',', $value)];
                            break;
                        }
                        case AvailableRestQueryParams::NBTW:{
                            $data['func'] = AvailableRestQueryParams::WHERE_NOT_BETWEEN;
                            $data['data'] = [$key, explode(',', $value)];
                            break;
                        }
                        default:{
                            $data['func'] = AvailableRestQueryParams::WHERE;
                            $data['data'] = [$key, AvailableRestQueryParams::textToOperator($operator), $value];
                            break;
                        }

                    }
                    $data['key'] = $columnName;
                    $data['operator'] = $operator;
                    $result[] = $data;
                }
            }
            return $result;
        }catch (RepoBuilderException $e){
            throw $e;
        } catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Parse request url to array NOT IN USE
     *
     * @param Request $req
     * @return array
     */
    private function prepareRequestParams(Request $req){
        try {
            $this->finalParams[AvailableRestQueryParams::_START ] = 0;
            $this->finalParams[AvailableRestQueryParams::_LIMIT ] = AvailableRestQueryParams::DEFAULT_LIMIT;

            $result = [];
            if($requestUri = $req->getRequestUri() ){
                $query = explode('?', $requestUri);
                if(count($query) === 2){
                    $keyValuesArray = explode('&', $query[1]);
                    foreach ($keyValuesArray as $keyValue){
                        $keyValueItem = explode('=', $keyValue);
                        if(count($keyValueItem) > 1){
                            $item['key'] = $keyValueItem[0];
                            $item['value'] = $keyValueItem[1];
                            $result[$item['key']] = $item['value'];
                            /*                        if(in_array($item['key'], AvailableRestQueryParams::additionalFilters())){
                                                        $result[$item['key']] = $item['value'];
                                                    }else{
                                                        if(key_exists($item['key'], $result)){
                                                            array_push($result[$item['key']], $item['value']);
                                                        }else{
                                                            $result[$item['key']] = [];
                                                            array_push($result[$item['key']], $item['value']);
                                                        }
                                                    }*/
                        }
                    }
                }
            }
            return $result;
        }catch (Exception $e){
            throw new RepoBuilderException('prepareRequestParams', $e);
        }
    }
}
