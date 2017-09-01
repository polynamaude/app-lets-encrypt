<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'lets_encrypt';
$app['version'] = '1.0.0';
$app['release'] = '1';
$app['vendor'] = 'WikiSuite';
$app['packager'] = 'WikiSuite';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('lets_encrypt_app_description');
$app['powered_by'] = array(
    'vendor' => array(
        'name' => 'Let\'s Encrypt',
        'url' => 'https://letsencrypt.org',
    ),
    'packages' => array(
        'certbot' => array(
            'name' => 'Certbot',
        ),
    ),
);

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('lets_encrypt_app_name');
$app['category'] = lang('base_category_system');
$app['subcategory'] = lang('base_subcategory_security');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['lets_encrypt']['title'] = $app['name'];
$app['controllers']['settings']['title'] = lang('base_settings');
$app['controllers']['certificate']['title'] = lang('lets_encrypt_certificates');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-network-core',
    'app-certificate-manager-core >= 1:2.4.0',
    'certbot',
    'python2-certbot-apache',
);

$app['requires'] = array(
    'app-certificate-manager',
);

$app['core_file_manifest'] = array(
    'lets_encrypt.conf' => array(
        'target' => '/etc/clearos/lets_encrypt.conf',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
);


$app['core_directory_manifest'] = array(
    '/var/clearos/lets_encrypt' => array(),
    '/var/clearos/lets_encrypt/backup' => array(),
);

$app['delete_dependency'] = array(
    'app-lets-encrypt-core',
    'certbot',
    'python2-certbot-apache',
);
