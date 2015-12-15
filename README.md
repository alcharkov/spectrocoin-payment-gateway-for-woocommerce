# Spectrocoin WooCommerce payment gateway
<p>Spectrocoin (unofficial and experimental) WooCommerce payment gateway allows customers to make payment with Bitcoin or euros</p>
<p>Spectrocoin is a trademark of Spectro Finance Ltd (https://spectrocoin.com/)</p>
<p>Donate bitcoins: 1F2JnMqW63pRkJJmsf7ra9PdMDpQL4VdVo</p>


**INSTALLATION**

1. Upload plugin to wp-content/plugins/ directory
2. Generate private and public keys
    1. Private key:
    ```shell
    # generate a 2048-bit RSA private key
    openssl genrsa -out "C:\private" 2048
    ```
    2. Public key:
    ```shell
    # output public key portion in PEM format
    openssl rsa -in "C:\private" -pubout -outform PEM -out "C:\public"
    ```
