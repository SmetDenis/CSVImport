# Cli Import tool for big CSV files [![Build Status](https://travis-ci.org/SmetDenis/CSVImport.svg?branch=master)](https://travis-ci.org/SmetDenis/CSVImport)      [![Coverage Status](https://coveralls.io/repos/github/SmetDenis/CSVImport/badge.svg?branch=master)](https://coveralls.io/github/SmetDenis/CSVImport?branch=master)

[![License](https://poser.pugx.org/SmetDenis/CSVImport/license)](https://packagist.org/packages/SmetDenis/CSV-Import)   [![Latest Stable Version](https://poser.pugx.org/SmetDenis/CSVImport/v/stable)](https://packagist.org/packages/SmetDenis/CSVImport) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SmetDenis/CSVImport/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SmetDenis/CSVImport/?branch=master)

## How to install
```sh
composer create-project smetdenis/csvimport
```

## How to run

From file
```sh
./bin/csvimport csv:insert                      \
    --config=./tests/fixtures/config.php        \
    --source-file=./tests/fixtures/random.csv   \
    -vvv
```

Or pipeline
```sh
cat ./tests/fixtures/random.csv | ./bin/csvimport csv:insert    \
    --config=./tests/fixtures/config.php                        \
    -vvv
```

## Unit tests and check code style
```sh
make
make test-all

./bin/csvimport csv:create                  \ 
    --config=./tests/fixtures/config.php    \
    --lines=10000 |                         \
./bin/csvimport csv:insert                  \
    --config=./tests/fixtures/config.php    \
    -vvv
```


### License

MIT
