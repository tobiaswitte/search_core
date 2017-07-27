mkfile_path := $(abspath $(lastword $(MAKEFILE_LIST)))
current_dir := $(dir $(mkfile_path))

TYPO3_WEB_DIR := $(current_dir).Build/Web
# Allow different versions on travis
TYPO3_VERSION ?= ~7.6
typo3DatabaseName ?= "searchcore_test"
typo3DatabaseUsername ?= "dev"
typo3DatabasePassword ?= "dev"
typo3DatabaseHost ?= "127.0.0.1"

.PHONY: install
install: clean
	COMPOSER_PROCESS_TIMEOUT=1000 composer require -vv --dev --prefer-source --ignore-platform-reqs typo3/cms="$(TYPO3_VERSION)"
	git checkout composer.json
	# Version conflicts with test suite, therefore download
	composer global require 'phpunit/phpcov=*'

functionalTests:
	typo3DatabaseName=$(typo3DatabaseName) \
		typo3DatabaseUsername=$(typo3DatabaseUsername) \
		typo3DatabasePassword=$(typo3DatabasePassword) \
		typo3DatabaseHost=$(typo3DatabaseHost) \
		TYPO3_PATH_WEB=$(TYPO3_WEB_DIR) \
		.Build/bin/phpunit --colors --debug -v \
			-c Tests/Functional/FunctionalTests.xml

unitTests:
	TYPO3_PATH_WEB=$(TYPO3_WEB_DIR) \
		.Build/bin/phpunit --colors --debug -v \
		-c Tests/Unit/UnitTests.xml

mergeCoverage: unitTests functionalTests
	mkdir -p .Build/report-merged
	.Build/bin/phpcov merge --html=.Build/report-merged/html .Build/report
	.Build/bin/phpcov merge --php=.Build/report-merged/php .Build/report
	.Build/bin/phpcov merge --clover=.Build/report-merged/clover .Build/report

uploadCodeCoverage: mergeCoverage uploadCodeCoverageToScrutinizer uploadCodeCoverageToCodacy

uploadCodeCoverageToScrutinizer:
	wget https://scrutinizer-ci.com/ocular.phar && \
		php ocular.phar code-coverage:upload --format=php-clover .Build/report-merged/clover

uploadCodeCoverageToCodacy:
	composer require -vv --dev codacy/coverage && \
		git checkout composer.json && \
		php .Build/bin/codacycoverage clover .Build/report-merged/clover

clean:
	rm -rf .Build composer.lock
