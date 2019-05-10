<?php

\Craft::setAlias('tests', dirname(__DIR__,2));
return [
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
        ],
    ],
];