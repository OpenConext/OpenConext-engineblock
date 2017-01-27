.DEFAULT_GOAL := help
.PHONY: help

BASEDIR := $(abspath $(abspath $(lastword $(MAKEFILE_LIST)))/../)
PHPUNIT := $(BASEDIR)/vendor/bin/phpunit -c $(BASEDIR)/tests/phpunit.xml
BEHAT   := $(BASEDIR)/vendor/bin/behat -c $(BASEDIR)/tests/behat.yml '@OpenConextEngineBlockFunctionalTestingBundle'

help:
	@echo "\n\033[1;32mOpenConext-engineblock Makefile\033[0m\n  \033[1;36mUsage:\033[0m make [target]\n"
	@echo "\033[1;93mAvailable targets:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -v -E "^ci-|^util-" | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[1;36m%-28s\033[0m %s\n", $$1, $$2}'
	@echo "\n\033[1;93mAvailable targets intended for CI:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -E "^ci-" | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[1;36m%-28s\033[0m %s\n", $$1, $$2}'
	@echo "\n\033[1;93mAvailable utility targets:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -E "^util-" | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[1;36m%-28s\033[0m %s\n", $$1, $$2}'

build: test-suites code-quality functional-tests ## Complete build to be run on a CI
pre-commit: test-suites php-lint-staged phpmd phpcs ## Build to run in the pre-commit hook
pre-push: clean test-suites code-quality behat-regression clean ## Build to run in the pre-push hook
functional-tests: ci-behat-regression ## Alias for ci-behat-regression
functional-tests-wip: ci-behat-wip ## Alias for ci-behat-wip
code-quality: php-lint phpmd phpcs  ## Code quality inspections (lint, md, cs)
test-suites: test-unit-eb4 test-unit test-integration ## Run all test suites

install-git-hooks: ## Installs the pre-push and pre-commit git hooks
	@cp $(BASEDIR)/ci/dev/pre-commit.dist $(BASEDIR)/.git/hooks/pre-commit && chmod a+x,g-w $(BASEDIR)/.git/hooks/pre-commit
	@cp $(BASEDIR)/ci/dev/pre-psuh.dist $(BASEDIR)/.git/hooks/pre-push && chmod a+x,g-w $(BASEDIR)/.git/hooks/pre-push

php-lint-staged: ## Runs php lint on staged php source files to be committed
	@git diff --cached --name-only -- '*.php' | tr "\n" ' ' | xargs -n 1 -P 4 php -l 1>/dev/null && echo "PHP lint: \033[1;92m✓\033[0m"

php-lint: ## Runs php lint on all php source files, excluding the vendor folder
	@find $(BASEDIR) -type f -name '*.php' ! -path "$(BASEDIR)/vendor/*" | tr "\n" ' ' | xargs -n 1 -P 4 php -l 1>/dev/null && echo "PHP lint: \033[1;92m✓\033[0m"

phpmd: ## Perform project mess detection using PHPMD
	@$(BASEDIR)/vendor/bin/phpmd src text ci/travis/phpmd.xml --exclude */Tests/* 1>/dev/null && echo "PHPMD: \033[1;92m✓\033[0m"

phpcs: ## Check the code style compliance
	@$(BASEDIR)/vendor/bin/phpcs --report=full --standard=ci/travis/phpcs.xml --warning-severity=0 --extensions=php $(BASEDIR)/src && echo "PHPCS: \033[1;92m✓\033[0m"

test-unit-eb4: util-rm-test-dir ; @$(PHPUNIT) --testsuite=eb4 $(opt) ## Run the EngineBlock library tests
test-unit: util-rm-test-dir ; @$(PHPUNIT) --testsuite=unit ## Run the EngineBlock unittest suite
test-integration: util-rm-test-dir ; @$(PHPUNIT) --testsuite=integration ## Run the Integration tests
ci-test-unit-eb4: util-rm-test-dir ; @$(PHPUNIT) --testsuite=eb4 --coverage-text ## Same as test-unit-eb4, with code-coverage
ci-test-unit: util-rm-test-dir ; @$(PHPUNIT) --testsuite=unit --coverage-text  ## Same as test-unit, with code-coverage
ci-test-integration: util-rm-test-dir ; @$(PHPUNIT) --testsuite=integration --coverage-text  ## Same as test-integration, with code-coverage

behat-wip: util-enable-func-test util-prepare-env-on-vm util-run-behat-wip util-disable-func-test util-revert-env-on-vm ## Run the behat features and scenario's in the WIP profile against the dev env
behat-regression: util-enable-func-test util-prepare-env-on-vm util-run-behat-regression util-disable-func-test util-revert-env-on-vm ## Run the behat regression suite against the dev env
ci-behat-wip: util-enable-func-test util-run-behat-wip util-disable-func-test ## Run the behat features and scenario's in the WIP profile
ci-behat-regression: util-enable-func-test util-run-behat-regression util-disable-func-test ## Run the behat regression suite against the dev env

clean: util-disable-func-test util-rm-test-dir ## Cleanup :)

util-rm-test-dir: ; @-rm -rf $(BASEDIR)/app/cache/test ## Remove the test env cache
util-prepare-env-on-vm: ; @$(BASEDIR)/run-on-vm.sh "sudo composer prepare-env" ## Run the composer prepare-env command on the VM
util-revert-env-on-vm: ; @$(BASEDIR)/run-on-vm.sh "sudo composer prepare-env" ## Copy of util-prepare-env-on-vm to be able to invoke it twice in one target
util-enable-func-test: ## Enable the functionalTesting flag in the application configuration
	@-perl -pi.bak -e 's/;functionalTesting = true/functionalTesting = true/' $(BASEDIR)/application/configs/application.ini && rm $(BASEDIR)/application/configs/application.ini.bak
util-disable-func-test: ## Disable the functionalTesting flag in the application configuration
	@-perl -pi.bak -e 's/functionalTesting = true/;functionalTesting = true/' $(BASEDIR)/application/configs/application.ini && rm $(BASEDIR)/application/configs/application.ini.bak
util-run-behat-wip: ## Run Behat with the 'wip' profile, allowed to fail
	@-$(BEHAT) --profile wip
util-run-behat-regression: ## Run Behat with the default profile
	@$(BEHAT) --profile default
