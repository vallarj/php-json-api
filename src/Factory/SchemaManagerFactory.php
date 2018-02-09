<?php
/**
 *  Copyright 2017-2018 Justin Dane D. Vallar
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 */

namespace Vallarj\JsonApi\Factory;


use Interop\Container\ContainerInterface;
use Vallarj\JsonApi\SchemaManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Factory\FactoryInterface;

class SchemaManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Create the schema manager
        $pluginManager = new SchemaManager($container, $options ?: []);

        // If config service is not available, return schema manager
        if(!$container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // Get `json_api` config key
        $jsonApiConfig = $config['json_api'] ?? [];

        // If `schema_manager` configuration is missing, return schema manager
        if(!isset($jsonApiConfig['schema_manager']) || !is_array($jsonApiConfig['schema_manager'])) {
            return $pluginManager;
        }

        // Wire service configuration
        (new Config($jsonApiConfig['schema_manager']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}