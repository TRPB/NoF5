NoF5
====

No longer wear out your F5 key! This simple PHP script will let you avoid refreshing the browser each time you change a file!

Pre-requisites
--------------

- Linux server
- The strace utility (included with almost every linux distribution)
- The user running PHP must have access to run strace
- A browser that supports server-side events e.g. Chromium or Firefox



Usage
-----

1) Include nof5.phar at the top of your script.

```php
require_once 'nof5.phar';
```

2) Save a file on the server and your browser will be updated as long as the page you're viewing has a &lt;head&gt; tag!


Alternative usage
-----------------

If you don't want to edit your script directly for version control reasons, development/live servers, you can add this to .htaccess on your development machine:

```
php_value auto_prepend_file "nof5.phar"
```

Features
--------

* For CSS and JavaScript files, the CSS will be reloaded without refreshing the page. If you have any JavaScript created elements/menus visible they will just be styled with the updated CSS without needing to be regenerated

* For JavaScript files, the javascript is reloaded in-place meaning the entire browser isn't refreshed!

* Any server-side file used by the page (Which includes any xml files, template files or any other file the script has opened), when written to will trigger a browser refresh.
