The keys and certificates in this directory are only used for the dummy idp and were generated using the following commands:

openssl genrsa -out private_key.pem 1024
openssl rsa -pubout -in private_key.pem -out public_key.pem
openssl req -x509 -nodes -days 10000 -newkey rsa:2048 -keyout private_key.pem -out certificate.crt