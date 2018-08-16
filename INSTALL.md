# Installation

## Setup Authorization in IPS

IPS 4.3 introduced built-in [authentication via OAuth2](https://invisioncommunity.com/news/product-updates/43-sign-in-from-other-sites-using-oauth-r1058/). Here's how to register a
new client for VPDB for seamless user integration.

### 1. Open Up Admin

Go to AdminCP, under *System*, choose *REST & OAuth*. 

<details>
 <summary>Screenshot</summary>
 <img src="https://raw.githubusercontent.com/vpdb/ips4-application/master/doc/img/setup-01-oauth-main.png"/>
 
</details>


You might need to download and copy a `.htaccess` files to your `api` folder.
If you're running Apache that will do it, if you're using Nginx I will add
instructions later.

Now, under *OAuth Clients*, choose *Create New*.

### 2. Setup Client

In the first *Settings* tab, choose a name, and we're going to create a 
*Custom Confidential OAuth Client*. The *Redirection URI* will be different
than in the screenshot. Also, we only want to ask the user for confirmation
once (*New sign ins only*).

<details>
 <summary>Screenshot</summary>
 <img src="https://raw.githubusercontent.com/vpdb/ips4-application/a9da0785614660e21dd81a22899766c0a492b070/doc/img/setup-02-oauth-create-new.png"/>
 
</details>

Switch to *Scopes*. The scopes define what permission VPDB is going to ask.
We need access to the member ID and email address, so we're going to create
two scopes, `profile` and `email`.

Those scopes should actually already be prepopulated. Make sure the permissions
are checked like in the screenshot.

<details>
 <summary>Screenshot</summary>
 <img src="https://raw.githubusercontent.com/vpdb/ips4-application/a9da0785614660e21dd81a22899766c0a492b070/doc/img/setup-03-oauth-scopes.png"/>
 
</details>

Now click on *Save* on the bottom on the screen.

### 3. Write Down Client Secret

You've now created a client ID and secret for VPDB to authenticate at the IPS
board. Write those down so they can be added to VPDB!

<details>
 <summary>Screenshot</summary>
 <img src="https://raw.githubusercontent.com/vpdb/ips4-application/a9da0785614660e21dd81a22899766c0a492b070/doc/img/setup-04-oauth-client-secret.png"/>
 
</details>

### 4. Done!

That's it! You should now see the VPDB client under *REST & OAuth*.

<details>
 <summary>Screenshot</summary>
 <img src="https://raw.githubusercontent.com/vpdb/ips4-application/a9da0785614660e21dd81a22899766c0a492b070/doc/img/setup-05-oauth-done.png"/>
 
</details>