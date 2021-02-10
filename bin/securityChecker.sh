#!/bin/bash
if curl -sL https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_amd64 > local-php-security-checker; then
    chmod +x ./local-php-security-checker
    ./local-php-security-checker
    rm ./local-php-security-checker
else
    printf 'Curl failed downloading php-security-checker with error code "%d"\n' "$?" >&2
    exit 1
fi
