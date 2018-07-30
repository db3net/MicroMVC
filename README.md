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
  |_index.php
  |_ config
    |_config.php
  |_controllers
  |_views
  |_models
  
```
You **config/config.php** file should look like this example. Note. The routing is stupidly rudimental. No fancy placeholders for vars...simply anything and everything after the directive ('controller/method') is considered an arg and passed along to the controller.  
```
$_config = array(
	
	'_database' => array(
		'type'=>'file',
	),

	'_routes' => array(
		'__default'=>'welcome',
    '__404'=>'404',
		'jmp'=>'redirect/jump_from_shortcode',
	)
);
```



Your index.php file will look like the following. You can put your MicroMVC folder anywhere on your file-system...preferably away from webserver access.

```

include_once('MicroMVC/MicroMVC.php');

Context::run();

```


