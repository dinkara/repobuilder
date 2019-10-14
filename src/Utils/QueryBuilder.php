<?php
/**
 * Created by PhpStorm.
 * User: Dinkola
 * Date: 10/9/2019
 * Time: 11:11 AM
 */

namespace Dinkara\RepoBuilder\Utils;

use Dinkara\RepoBuilder\Repositories\IRepo;
use Illuminate\Http\Request;
use Dinkara\RepoBuilder\Utils\RestQueryConverter;
use Exception;


class QueryBuilder
{

    public function __construct(Request $req, IRepo $repo, $eloquentBuilder = null)
    {
        try{
            if($eloquentBuilder && is_object($eloquentBuilder) &&
                (get_class($eloquentBuilder) === "Sofa\Eloquence\Builder" ||
                    strpos(get_class($eloquentBuilder), "Illuminate\Database\Eloquent\Relations") !== false )){
                $this->eloquentBuilder = $eloquentBuilder;
            }else{
                $this->eloquentBuilder = $req->q ? $repo->model()->search(["*" . $req->q . "*"], false) : $repo->model();
            }

            $this->repo = $repo;
            $this->req = $req;
            $this->columns = $this->repo->getModel()->getAllColumnsNames();
            $this->restQueryConverter = new RestQueryConverter($req, $repo->model()->getTable());
        }catch (RepoBuilderException $e){
            throw $e;
        }
        catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Returns data
     *
     * @return string
     */
    public function getData(){
        try{
            $this->prepareQuery();
            $pagination = $this->preparePagination();
            if(is_array($pagination) && count($pagination) == 2){
                return $this->eloquentBuilder->skip($pagination[0])->paginate($pagination[1]);
            }
        }catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Returns built query
     *
     * @return mixed
     */
    public function getQuery(){
        try{
            $this->prepareQuery();
            return $this->eloquentBuilder;
        }catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }


    /**
     * Convert query array to query builder
     *
     * @throws RepoBuilderException
     */
    private function prepareQuery(){
        try{
            $this->eloquentBuilder = $this->prepareWhere($this->eloquentBuilder);
            $this->eloquentBuilder = $this->prepareSort($this->eloquentBuilder);
        }catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Convert query array to query builder
     *
     * @param $query
     * @return mixed
     */
    private function prepareWhere($query){
        try{
            $restQuery = $this->restQueryConverter->getParams();
            if( isset($restQuery[AvailableRestQueryParams::_WHERE])
                && is_array($restQuery[AvailableRestQueryParams::_WHERE])
                && count($restQuery[AvailableRestQueryParams::_WHERE]) > 0){
                foreach ($restQuery[AvailableRestQueryParams::_WHERE] as $key => $item){
                    if( $this->validateWhereItem($item) ){
                        $query = $query->{$item['func']}( ...$item['data'] );
                    }
                }
            }
            return $query;
        }catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Convert sorting array to query builder
     *
     * @param $query
     * @return mixed
     */
    private function prepareSort($query){
        try{
            $restQuery = $this->restQueryConverter->getParams();
            if( isset($restQuery[AvailableRestQueryParams::_SORT])
                && is_array($restQuery[AvailableRestQueryParams::_SORT])
                && count($restQuery[AvailableRestQueryParams::_SORT]) > 0){
                foreach ($restQuery[AvailableRestQueryParams::_SORT] as $key => $item){
                    if( $this->validateSortItem($item) ) {
                        $query = $query->orderBy( ...$item['data'] );
                    }
                }
            }
            return $query;
        }catch (Exception $e){
            throw new RepoBuilderException($e);
        }
    }

    /**
     * Prepare pagination params exported from request
     *
     * @return array
     */
    private function preparePagination(){
        try{
            $restQuery = $this->restQueryConverter->getParams();
            $limit = $this->repo->getModel()->_limit;
            $start = AvailableRestQueryParams::DEFAULT_START;

            if( isset($restQuery[AvailableRestQueryParams::_LIMIT])
                && $restQuery[AvailableRestQueryParams::_LIMIT]
                && $restQuery[AvailableRestQueryParams::_LIMIT] <= $limit){
                $limit = $restQuery[AvailableRestQueryParams::_LIMIT];
            }
            if( isset($restQuery[AvailableRestQueryParams::_START])
                && $restQuery[AvailableRestQueryParams::_START]){
                $start = $restQuery[AvailableRestQueryParams::_START];
            }
            return [$start*$limit, $limit];
        }catch (Exception $e){
            throw new RepoBuilderException($e);
        }

    }

    /**
     * Check item, if meet requests format for sort
     *
     * @param $item
     * @return bool
     */
    private function validateSortItem($item){
        return     isset($item['data'])
            && isset($item['field'])
            && is_array($item['data'])
            && in_array($item['field'], $this->columns);
    }

    /**
     * Check item, if meet requests format for where
     *
     * @param $item
     * @return bool
     */
    private function validateWhereItem($item){
        return     isset($item['operator'])
            && isset($item['key'])
            && isset($item['func'])
            && isset($item['data'])
            && is_array($item['data'])
            && in_array($item['key'], $this->columns)
            && in_array($item['operator'], AvailableRestQueryParams::filters());
    }

    /*            if($eloquentBuilder === null || !is_object($eloquentBuilder) ||
                ( get_class($eloquentBuilder) !== "Sofa\Eloquence\Builder" &&
                  get_class($eloquentBuilder) !== "Sofa\Eloquence\Builder")){
                throw new RepoBuilderException(null, trans('repobuilder.exceptions.custom.instanceOfBuilder',
                    ['type' => is_object($eloquentBuilder) ? get_class($eloquentBuilder) : gettype($eloquentBuilder)]));
            }else{

            }*/

}