# Spectrocoin WooCommerce payment gateway
<p>Spectrocoin (unofficial and experimental) WooCommerce payment gateway allows customers to make payment with Bitcoin or euros</p>
<p>Spectrocoin is a trademark of Spectro Finance Ltd (https://spectrocoin.com/)</p>

**INSTALLATION**

1. Upload plugin to wp-content/plugins/ directory
2. Generate private and public keys
    1. Private key:
    ```shell
    # generate a 2048-bit RSA private key
    openssl genrsa -out "private.key" 2048
    ```
    2. Public key:
    ```shell
    # output public key portion in PEM format
    openssl rsa -in "private.key" -pubout -outform PEM -out "public.key"
    ```
3. Add private key to plugin settings and public key to Spectrocoin project account
4. Choose same receive currency for Wordpress plugin and spectrocoin.com project otherwise plugin would not work.
