# MicroMVC
Single-file, zero-dependency PHP MVC Framework which supports URL and CLI with routing, views, a simple JSON file-store.

# Details to come but....

URLS and webserver should be set up to handle directives and args like this 

```
index.php?/<controller>/<method>/arg1/arg2/arg3/etc
```

Folders should be set up like...
```
Root
  |
  |_index.php
  |_controllers
  |_views
  |_models
```

Your index.php file will look like the following. You can put your MicroMVC folder anywhere on your file-system...preferably away from webserver access.

```

include_once('MicroMVC/MicroMVC.php');

Context::run();

```


