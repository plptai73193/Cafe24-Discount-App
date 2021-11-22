<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;

abstract class BaseService {
    protected $model;

    public function getError(Exception $e) {
        report($e);
        return response()->json([
            "success" => false,
            "code" => Response::HTTP_INTERNAL_SERVER_ERROR,
            "msg" => $e->getMessage(),
        ]);
    }

    /**
     * Get models
     *
     * @param array $where
     * @param array $columns
     * @param string $whereRaw
     * @param array $prepare_whereRaw
     * @param array $group_by_column
     *
     * @return array $models
     */
    public function getModels($where = [], $columns = [], $whereRaw = "", $prepare_whereRaw = [], $group_by_column = []){
        try {
            $builder = $this->getModelQueryBuilder($where, $columns, $whereRaw, $prepare_whereRaw, $group_by_column);
            if ($builder->count() > 0) {
                $models = $builder->get();
                return $models;
            } else {
                return null;
            }
        } catch (Exception $e) {
            $this->getError($e);
        }
    }


    /**
     * Get model
     *
     * @param array $where
     * @param array $columns
     * @param string $whereRaw
     * @param array $prepare_whereRaw
     * @param array $group_by_column
     *
     * @return object $model
     */
    public function getModel($where = [], $columns = [], $whereRaw = "", $prepare_whereRaw = [],  $group_by_column = []){
        try {
            $builder = $this->getModelQueryBuilder($where, $columns, $whereRaw, $prepare_whereRaw, $group_by_column);
            if ($builder->count() > 0) {
                $model = $builder->first();
                return $model;
            } else {
                return null;
            }
        } catch (Exception $e) {
            $this->getError($e);
        }
    }


    /**
     * Create Query_Builder to get models
     *
     * @param array $where
     * @param array $columns
     * @param string $whereRaw
     * @param array $prepare_whereRaw
     * @param array $group_by_columns
     *
     * @return object $builder
     */
    abstract public function getModelQueryBuilder($where = [], $columns = [], $whereRaw = "", $prepare_whereRaw = [], $group_by_columns = []);
}
