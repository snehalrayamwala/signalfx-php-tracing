<?php

namespace DDTrace\Tests\Integrations\Laravel\V5_8;

use DDTrace\Tests\Common\SpanAssertion;
use DDTrace\Tests\Common\WebFrameworkTestCase;
use DDTrace\Tests\Frameworks\Util\Request\RequestSpec;

class CommonScenariosTest extends WebFrameworkTestCase
{
    const IS_SANDBOX = true;

    protected static function getAppIndexScript()
    {
        return __DIR__ . '/../../../Frameworks/Laravel/Version_5_8/public/index.php';
    }

    protected static function getEnvs()
    {
        return array_merge(parent::getEnvs(), [
            'APP_NAME' => 'laravel_test_app',
        ]);
    }

    /**
     * @dataProvider provideSpecs
     * @param RequestSpec $spec
     * @param array $spanExpectations
     * @throws \Exception
     */
    public function testScenario(RequestSpec $spec, array $spanExpectations)
    {
        $traces = $this->tracesFromWebRequest(function () use ($spec) {
            $this->call($spec);
        });

        $this->assertFlameGraph($traces, $spanExpectations);
    }

    public function provideSpecs()
    {
        return $this->buildDataProvider(
            [
                'A simple GET request returning a string' => [
                    SpanAssertion::build(
                        'laravel.request',
                        'laravel_test_app',
                        'web',
                        'App\Http\Controllers\CommonSpecsController@simple simple_route'
                    )->withExactTags([
                        'laravel.route.name' => 'simple_route',
                        'laravel.route.action' => 'App\Http\Controllers\CommonSpecsController@simple',
                        'http.method' => 'GET',
                        'http.url' => 'http://localhost:9999/simple',
                        'http.status_code' => '200',
                    ])->withChildren([
                        SpanAssertion::build('laravel.action', 'laravel_test_app', 'web', 'simple')
                            ->withExactTags([
                            ])
                            ->onlyIf(static::IS_SANDBOX),
                        SpanAssertion::exists(
                            'laravel.provider.load',
                            'Illuminate\Foundation\ProviderRepository::load'
                        )->onlyIf(static::IS_SANDBOX),
                    ]),
                ],
                'A simple GET request with a view' => [
                    SpanAssertion::build(
                        'laravel.request',
                        'laravel_test_app',
                        'web',
                        'App\Http\Controllers\CommonSpecsController@simple_view unnamed_route'
                    )->withExactTags([
                        'laravel.route.action' => 'App\Http\Controllers\CommonSpecsController@simple_view',
                        'http.method' => 'GET',
                        'http.url' => 'http://localhost:9999/simple_view',
                        'http.status_code' => '200',
                    ])->withExistingTagsNames([
                        'laravel.route.name',
                    ])->withChildren([
                        SpanAssertion::build('laravel.action', 'laravel_test_app', 'web', 'simple_view')
                            ->withExactTags([
                            ])
                            ->onlyIf(static::IS_SANDBOX),
                        SpanAssertion::exists(
                            'laravel.provider.load',
                            'Illuminate\Foundation\ProviderRepository::load'
                        )->onlyIf(static::IS_SANDBOX),
                        SpanAssertion::build(
                            'laravel.view',
                            'laravel_test_app',
                            'web',
                            'laravel.view'
                        )->withExactTags([
                        ])->skipIf(static::IS_SANDBOX),
                        SpanAssertion::build(
                            'laravel.view.render',
                            'laravel_test_app',
                            'web',
                            'simple_view'
                        )->withExactTags([
                        ])->withChildren([
                            SpanAssertion::build(
                                'laravel.view',
                                'laravel_test_app',
                                'web',
                                '*/resources/views/simple_view.blade.php'
                            )->withExactTags([
                            ]),
                        ])->onlyIf(static::IS_SANDBOX),
                    ]),
                ],
                'A GET request with an exception' => [
                    SpanAssertion::build(
                        'laravel.request',
                        'laravel_test_app',
                        'web',
                        'App\Http\Controllers\CommonSpecsController@error unnamed_route'
                    )->withExactTags([
                        'laravel.route.name' => '',
                        'laravel.route.action' => 'App\Http\Controllers\CommonSpecsController@error',
                        'http.method' => 'GET',
                        'http.url' => 'http://localhost:9999/error',
                        'http.status_code' => '500',
                    ])->setError()->withChildren([
                        SpanAssertion::exists('laravel.action')
                            ->onlyIf(static::IS_SANDBOX),
                        SpanAssertion::exists('laravel.view')
                            ->skipIf(static::IS_SANDBOX),
                        SpanAssertion::exists('laravel.view.render')
                            ->withChildren([
                                SpanAssertion::exists('laravel.view'),
                            ])->onlyIf(static::IS_SANDBOX),
                        SpanAssertion::exists(
                            'laravel.provider.load',
                            'Illuminate\Foundation\ProviderRepository::load'
                        )->onlyIf(static::IS_SANDBOX),
                    ]),
                ],
            ]
        );
    }
}
