# Askit
### Installation

```bash
cd <magento_root>
```

Download and install composer module 
```bash
composer config repositories.swissup composer https://swissup.github.io/packages/
composer require swissup/askit
bin/magento module:enable Swissup_Askit
bin/magento setup:upgrade
```
