unpack coin-serviceregistry-1.0.tgz

    <VirtualHost *:80>
        ServerName serviceregistry.example.com

        RewriteEngine   on
        RewriteCond     %{SERVER_PORT} ^80$
        RewriteRule     ^(.*)$ https://%{SERVER_NAME}$1 [L,R]
  </VirtualHost>
  <Virtualhost *:443>
        ServerAdmin youremail@example.com

        DocumentRoot /var/www/serviceregistry/www
        ServerName serviceregistry.example.com

        Alias /simplesaml /var/www/serviceregistry/www

        SSLEngine on
        SSLCertificateFile      /etc/httpd/ssl/example.pem
        SSLCertificateKeyFile   /etc/httpd/ssl/example.key
        SSLCertificateChainFile /etc/httpd/ssl/chain-example.pem
  </VirtualHost>

enable janus:
  touch modules/janus/enable

cp janus_patches/rest.php modules/janus/www/services/rest/
