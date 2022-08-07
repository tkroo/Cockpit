<?php

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use App\GraphQL\Types\JsonType;

$gql->queries['fields']['content'] = [

    'type' => Type::listOf(JsonType::instance()),

    'args' => [
        'model'  => Type::nonNull(Type::string()),
        'limit' => Type::int(),
        'skip'  => Type::int(),
        'sort'  => JsonType::instance(),
        'locale'  => ['type' => Type::string(), 'defaultValue' => 'default'],
        'populate'   => ['type' => Type::int(), 'defaultValue' => 0],
        'projection' => ['type' => Type::string(), 'defaultValue' => ''],
        'filter'   => ['type' => JsonType::instance(), 'defaultValue' => []]
    ],

    'resolve' => function ($root, $args) use($app) {

        $model = $args['model'];

        if (!$app->module('content')->exists($model)) {
            return [];
        }

        if (!$app->helper('acl')->isAllowed("content/{$model}/read", $app->helper('auth')->getUser('role'))) {
            $app->response->status = 412;
            return [];
        }

        $meta = $app->module('content')->model($model);

        if ($meta['type'] == 'singleton') {
            return [$app->module('content')->item($model)];
        }

        $process  = ['locale' => $args['locale']];
        $options  = [];

        if ($args['populate']) {
            $process['populate'] = $args['populate'];
        }

        if (isset($args['limit'])) $options['limit'] = $args['limit'];
        if (isset($args['skip'])) $options['skip'] = $args['skip'];

        if (isset($args['sort'])) {
            $options['sort'] = $args['sort'];
        }

        if ($args['filter']) {
            $options['filter'] = $args['filter'];
        }

        if (!isset($options['filter']) || !is_array($options['filter'])) {
            $options['filter'] = [];
        }

        $options['filter']['_state'] = 1;

        return $app->module('content')->items($model, $options, $process);

    }
];

$gql->mutations['fields']['saveContentItem'] = [
    'args' => [
        'model' => Type::nonNull(Type::string()),
        'data'  => Type::nonNull(JsonType::instance()),
    ],
    'type' => new ObjectType([
        'name' => 'saveContentItemOutput',
        'fields' => [
            'item' => ['type' => JsonType::instance()],
            'error' => ['type' => Type::string()],
        ]
    ]),
    'resolve' => function ($root, $args) use($app) {

        $model= $app->module('content')->model($args['model']);
        $data = $args['data'];

        if (!$model) {
            $app->response->status = 404;
            return ["error" => "Model <{$args['model']}> not found"];
        }

        if (!$data || !is_array($data)) {
            $app->response->status = 412;
            return ['error' => 'Item data is missing'];
        }

        $default = $app->module('content')->getDefaultModelItem($model['name']);

        // create
        if (!isset($data['_id'])) {

            if (!$app->helper('acl')->isAllowed("content/{$model['name']}/create", $app->helper('auth')->getUser('role'))) {
                $app->response->status = 403;
                return ['error' => 'Permission denied'];
            }

            $data = array_merge($default, $data);

        // update
        } else {

            if (!$app->helper('acl')->isAllowed("content/{$model['name']}/update", $app->helper('auth')->getUser('role'))) {
                $app->response->status = 403;
                return ['error' => 'Permission denied'];
            }
        }

        if (isset($data['_state']) && !$app->helper('acl')->isAllowed("content/{$model['name']}/publish", $app->helper('auth')->getUser('role'))) {
            unset($item['_state']);
        }

        // remove properties not available in the field list

        $allowedKeys = array_keys($default);

        foreach (array_keys($data) as $key) {
            if (!in_array($key, $allowedKeys)) unset($data[$key]);
        }

        $item = $app->module('content')->saveItem($model['name'], $data, ['user' => $app->helper('auth')->getUser()]);

        return compact('item');
    },
];

$gql->mutations['fields']['deleteContentItem'] = [
    'args' => [
        'model' => Type::nonNull(Type::string()),
        'id'  => Type::nonNull(Type::string()),
    ],
    'type' => new ObjectType([
        'name' => 'deleteContentItemOutput',
        'fields' => [
            'success' => ['type' => Type::boolean()],
            'error' => ['type' => Type::string()],
        ]
    ]),
    'resolve' => function ($root, $args) use($app) {

        $model= $app->module('content')->model($args['model']);

        if (!$model) {
            $app->response->status = 404;
            return ["error" => "Model <{$args['model']}> not found"];
        }

        if (!in_array($model['type'], ['collection', 'tree'])) {
            $app->response->status = 412;
            return ['error' => "DELETE method not allowed for <{$model['name']}>"];
        }

        if (!$app->helper('acl')->isAllowed("content/{$model['name']}/delete", $app->helper('auth')->getUser('role'))) {
            $app->response->status = 403;
            return ['error' => 'Permission denied'];
        }

        $app->module('content')->remove($model['name'], ['_id' => $args['id']]);

        return ['success' => true];

    },
];
