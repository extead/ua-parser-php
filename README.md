About
=========================

PHP UA Parser library based on [https://github.com/faisalman/ua-parser-js](https://github.com/faisalman/ua-parser-js)

Installation
=========================

```
composer require extead/ua-parser-php
```

Usage
=========================

```
<?php

$parser = new \Extead\UAParser\UAParser();
$parser->setUa("Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10");
$parser->getResult();
```
