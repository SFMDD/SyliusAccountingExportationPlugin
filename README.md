<p align="center">
    <a href="https://fm2d.com/" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" width="200px" alt="Sylius logo" />
    </a>
</p>
<h1 align="center">Sylius Accounting Exportation Plugin</h1>
<p align="center">Allow you to export accounting to csv file</p>

## Installation

```bash
composer require fmdd/sylius-accounting-exportation-plugin
```

Change your config/bundles.php file to add the line for the plugin :

```php
<?php
return [
//..
MonsieurBiz\SyliusSalesReportsPlugin\MonsieurBizSyliusSalesReportsPlugin::class => ['all' => true],
];
```

* Now, you can import the config file :
```yaml
#config/packages/_sylius.yaml
imports:
    - { resource: "@FMDDSyliusAccountingExportationPlugin/Resources/config/config.yaml" }
```

* Finally import the routes in `config/routes/fmdd_sylius_accounting_exportation_plugin.yaml` :
```yaml
fmdd_sylius_accounting_exportation:
  resource: "@FMDDSyliusAccountingExportationPlugin/Resources/config/routing.yaml"
```

