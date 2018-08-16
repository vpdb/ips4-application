# Installation

## Setup Authorization in IPS

IPS 4.3 introduced built-in authentication via OAuth2. Here's how to register a
new client for VPDB for seamless user integration.

Go to AdminCP, under *System*, choose *REST & OAuth*. Under *OAuth Clients*,
choose *Create New*.

<details>
 <summary>Screenshot</summary>
 <img src="https://raw.githubusercontent.com/vpdb/ips4-application/master/doc/img/setup-01-oauth-main.png"/>
</details>

You might need to download and copy a `.htaccess` files to your `api` folder.
If you're running Apache that will do it, if you're using Nginx I will add
instructions later.

