<?php

return [

    'exceptions' => [
        'default' =>'RepoBuilder: Something went wrong',
        'QueryBuilder' =>[
            '__construct'           => 'QueryBuilder: Something went wrong during initializing new instance',
            'getData'               => 'QueryBuilder: Something went wrong during executing query',
            'getQuery'              => 'QueryBuilder: Something went wrong during parsing query',
            'prepareQuery'          => 'QueryBuilder: Something went wrong during preparation parsing query',
            'prepareWhere'          => 'QueryBuilder: Something went wrong during preparation query (where) ',
            'prepareSort'           => 'QueryBuilder: Something went wrong during preparation query (sort)',
            'preparePagination'     => 'QueryBuilder: Something went wrong during preparation query (pagination)',
        ],
        'RestQueryConverter' =>[
            '__construct'               => 'RestQueryConverter: Something went wrong during initializing new instance',
            'getParams'                 => 'RestQueryConverter: Something went wrong during parsing query',
            'convertSortQueryParams'    => 'RestQueryConverter: Invalid value for sort',
            'convertStartQueryParams'   => 'RestQueryConverter: Invalid value for pagination start',
            'convertLimitQueryParams'   => 'RestQueryConverter: Invalid value for pagination limit',
            'convertWhereQueryParams'   => 'RestQueryConverter: Invalid search query',
        ],
        'custom' => [
            'convertStartQueryParams' => 'RestQueryConverter: Pagination start expected a positive integer got :type',
            'convertLimitQueryParams' => 'RestQueryConverter: Pagination limit expected a positive integer got :type',
            'convertSortQueryParams'  => 'RestQueryConverter: Sort expected a string, got :type',
            'sortingTypes'            => 'RestQueryConverter: Sort can only be one of asc|desc|ASC|DESC',
        ],

    ],

];
