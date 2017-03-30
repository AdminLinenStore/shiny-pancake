<?php
return array(
    'components'=>array(
        //Database of Magento1
        'mage1' => array(
            'connectionString' => 'mysql:host=production-db.clagbu9jhwos.us-west-1.rds.amazonaws.com;dbname=temco_mage',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'ra#eYYsioKvOSFTOf5*6MHv94jUu9i6e1t0DWhZO',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'class' => 'CDbConnection'
        ),
        //Database of Magento2 beta
        'mage2' => array(
            'connectionString' => 'mysql:host=localhost;dbname=temco',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'HDO303mmM',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'class' => 'CDbConnection'
        )
    ),

    'import'=>array(
        //This can change for your magento1 version if needed
        //'application.models.db.mage19x.*',
        'application.models.db.mage19x.*',
    )
);
