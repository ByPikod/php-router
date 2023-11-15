<?php

namespace PHPRouter;

use PHPTest\Test;

/**
 * RouterTest class
 * @since 1.0.0
 */
class RouterTest extends Router
{
    /**
     * Test get branch
     * @test Method Test - getBranch
     * @since 1.0.0
     */
    public function getBranchTest(Test $test): void
    {
        // Check if getter returns the correct branch
        $this->getBranch('/getBranchTest')[] = 'test';
        $test->assertArrayContains($this->getBranch('/getBranchTest'), 'test');
    }

    /**
     * Test seperate path
     * @test Method Test - seperatePath
     * @since 1.0.0
     */
    public function seperatePathTest(Test $test)
    {
        // Clear the router
        $this->clear();

        // Table of assertions
        $assertions = [
            '/' => [],
            '' => [],
            '/seperatePathTest' => ['seperatePathTest'],
            '/seperatePathTest/' => ['seperatePathTest'],
            'seperatePathTest' => ['seperatePathTest'],
            'seperatePathTest/' => ['seperatePathTest'],
            '/seperatePathTest/seperatePathTest' => ['seperatePathTest', 'seperatePathTest'],
            '/seperatePathTest/seperatePathTest/' => ['seperatePathTest', 'seperatePathTest'],
            'seperatePathTest/seperatePathTest' => ['seperatePathTest', 'seperatePathTest'],
            'seperatePathTest/seperatePathTest/' => ['seperatePathTest', 'seperatePathTest'],
        ];

        // Check assertions
        foreach ($assertions as $path => $expected) {
            $test->assertArrayEqual(Utilities::seperatePath($path), $expected);
        }
    }

    /**
     * Test get executables
     * @test Method Test - getExecutables
     * @since 1.0.0
     */
    public function getExecutablesTest(Test $test): void
    {
        $this->clear(); // Clear the router

        // To check if the executables are returned correctly
        $fill = [];
        $fakeFunc = function () use (&$fill) {
            $fill[] = true;
        };

        // Add executables
        $this->use($fakeFunc);
        $this->use($fakeFunc, '/getExecutablesTest');
        $this->route('/getExecutablesTest', $fakeFunc)->use($fakeFunc)->use($fakeFunc);
        $this->use($fakeFunc);

        // Get executables
        $executables = $this->getExecutables(['getExecutablesTest']);
        foreach ($executables as $executable) {
            // Execute the executable
            if (is_callable($executable)) $executable();
            if ($executable instanceof Route) {
                ($executable->callback)();
            }
        }

        // Check if the executables are correct
        $test->assertEqual(6, sizeof($fill));
    }

    /**
     * Run executables test
     * @test Method Test - runExecutables
     * @since 1.0.0
     */
    public function runExecutablesTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // To check if the executables are returned correctly
        $fill = [];
        $fakeFunc = function (Context $ctx) use (&$fill) {
            $fill[] = true;
            $ctx->next();
        };

        // Add executables
        $this->use($fakeFunc);
        $this->use($fakeFunc, '/executablesTest');
        $this->route('/executablesTest', $fakeFunc)->use($fakeFunc)->use($fakeFunc);
        $this->use($fakeFunc);

        // Run executables
        $this->executeTree('/executablesTest');
        $test->assertEqual(sizeof($fill), 6);
    }

    /**
     * Find full path test
     * @test Method Test - findFullPath
     * @since 1.0.1
     */
    public function findFullPathTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Add routes
        $group = $this->group('/findFullPathTest');
        $wildcardGroup = $group->group('/:id');

        // Test if the full path is correct
        $test->assertArrayEqual($this->findFullPath(), []);
        $test->assertArrayEqual($group->findFullPath(), ['findFullPathTest']);
        $test->assertArrayEqual($wildcardGroup->findFullPath(), ['findFullPathTest', ':id']);
    }

    /**
     * Test use
     * @test Middleware add test
     * @since 1.0.0
     */
    public function useAdditionTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Test use
        $testPassed = false;
        $this->use(function () use (&$testPassed) {
            $testPassed = true;
        }, '/useAdditionTest');
        $this->getBranch('/useAdditionTest')[0]();
        $test->assertTrue($testPassed);
    }

    /**
     * Test use
     * @test Middleware test
     * @since 1.0.0
     */
    public function useTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Test use
        $_SERVER['REQUEST_URI'] = '/useTest';
        $testPassed = false;
        $this->use(function (Context $ctx) use (&$testPassed) {
            $testPassed = true;
            $ctx->next();
        }, '/useTest');

        $this->run();
        $test->assertTrue($testPassed);
    }

    /**
     * Test route
     * @test Route add test
     * @since 1.0.0
     */
    public function routeAdditionTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Test route
        $testPassed = false;
        $this->route('/routeAdditionTest', function () use (&$testPassed) {
            $testPassed = true;
        });

        // Test if the route was added
        $route = $this->getBranch('/routeAdditionTest')[0];
        $test->assertInstanceOf($route, Route::class);

        // Test if the route has the callback
        ($route->callback)();
        $test->assertTrue($testPassed);
    }

    /**
     * Test route
     * @test Route test
     * @since 1.0.0
     */
    public function routeTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Test route
        $_SERVER['REQUEST_URI'] = '/routeTest';
        $testPassed = false;

        $this->route('/routeTest', function (Context $context) use (&$testPassed) {
            $testPassed = true;
            $context->next();
        });

        $this->run();
        $test->assertTrue($testPassed);
    }

    /**
     * This test expects Router to not execute next route unless next is called.
     * @test Next route test
     * @since 1.0.0
     */
    public function nextRouteTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Test route
        $_SERVER['REQUEST_URI'] = '/routeNextTest';
        $routeCalled = false;
        $nextCalled = false;
        $this->route('/routeNextTest', function (Context $context) use (&$routeCalled) {
            $routeCalled = true;
        });
        $this->use(function (Context $context) use (&$nextCalled) {
            $nextCalled = true;
            $context->next();
        });

        $this->run();
        $test->assertTrue($routeCalled);
        $test->assertFalse($nextCalled);
    }

    /**
     * Test groups
     * @test Router group test
     * @since 1.0.1
     */
    public function groupTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Test route
        $_SERVER['REQUEST_URI'] = '/groupTest';
        $testPassed = false;

        $group = $this->group('/groupTest');
        $group->route('/', function (Context $context) use (&$testPassed) {
            $testPassed = true;
            $context->next();
        });

        $this->run();
        $test->assertTrue($testPassed);
    }

    /**
     * Test wildcards
     * @test Route wildcard test
     * @since 1.0.0
     */
    public function routeWildcardTest(Test $test): void
    {
        // Clear the router
        $this->clear();

        // Test route
        $_SERVER['REQUEST_URI'] = '/routeWildcardTest/123';
        $testPassed = false;

        $this->route('/routeWildcardTest/:id', function (Context $context) use (&$testPassed) {
            $testPassed = true;
            $context->next();
        });

        $this->run();
        $test->assertTrue($testPassed);
    }
}
