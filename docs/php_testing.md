# PHP testing
There are 4 different test opportunities in EngineBlock to use while developing features. These are:

- Unit tests (PHPUnit)
- Functional tests (PHPunit)
- Acceptance tests (Behat)
- Integration tests (PHPunit)

Most tests reside in the `./tests` folder in the document root except for the behat tests, they can be found in the Symfony source folder in: `./src/OpenConext/EngineBlockFunctionTestingBundle`

For more info about JS testing, see [the JS testing documentation](js_tesing.md)

# Running tests
Tests can be run:
 - From your IDE. Be sure to configure your interpreter to be based on the OpenConext-deploy virtual machine. That way tests run on a familiar infrastructure.
 - Using the and build targets on the VM.
 - On bare metal (your host), but your mileage may vary, especially for functional and acceptance tests.

# Best practices

## Unit tests
Try to cover any new feature in ample unit test coverage. And put effort into keeping tests up to date when existing features are modified.

Some older unit tests of the Corto part of EngineBlock can be found in the `tests/library/Engineblock/Test` folder. Testing the legacy part of EngineBlock might be more difficult as dependency injection is not fully utilized here.

## Integration tests
There are not many integration tests yet, but use the `./tests/integration` folder for writing PHPUnit integration tests.

## Acceptance tests

Aim to cover any functional changes or new features in a Behat scenario. These tests have proven very useful over the years.

Some aids in working with these tests:

1. When you want to try the test in your own browser: Echo the `$ssoStartLocation` and stop execution in `MockSpContext::iTriggerTheLoginEitherAtOrUnsolicitedAtEb`. Run the test and open the link in your own browser. Ensure you run EngineBlock in test environment in order to use the test fixtures.
2. Xdebug Step debugging: Add the `Xdebug step debugging is enabled in the browser` step definition to your test to let Mink set the debug cookie in the browser. Enabling you to listen for incoming debug requests.
3. WIP tests: add the `@WIP` or `@SKIP` annotations to any scenario to flag it a WIP, or skip it altogether. When calling Behat from CLI, be sure to only run the `wip` suite.

## Quality assurance
The different QA tooling we know and love in other OpenConext projects are also included in EngineBlock. Most can be run using one of the ant build targets.

JavaScript tests can be run using NPM scripts found in the `theme/packages.json`. These include unit, end to end and some linting tasks. Consult the aforementioned packages.json for the current set of available tools.
