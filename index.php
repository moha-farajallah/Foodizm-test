
<?php
// Make sure to sync the timezone with your server settings
date_default_timezone_set('Asia/Tokyo');

function getConfig($filename)
{
	$contents = file_get_contents($filename);
	$config = json_decode($contents, true);
	
	return $config;
}

function isOnSaleMode()
{
	// Make sure the path of the config.json file is correct.
	$config = getConfig('config.json');


	$saleStartDate = strtotime($config['start_time']);
	$saleEndDate   = strtotime($config['end_time']);
	$now 		   = strtotime(date('Y-m-d H:i:s')); // Gets the current timestamp

	if ($now >= $saleStartDate && $now <= $saleEndDate) {
		return true;
	}
	
	return false;
}

function main()
{
	$saleMode = isOnSaleMode();

	if ($saleMode) {
		echo "index2.html";
	} else {
		echo "index.html";
	}
}
?>
<?php
/**
 * Application entry point
 *
 * Example - run a particular store or website:
 * --------------------------------------------
 * require __DIR__ . '/app/bootstrap.php';
 * $params = $_SERVER;
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'website2';
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
 * $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
 * \/** @var \Magento\Framework\App\Http $app *\/
 * $app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
 * $bootstrap->run($app);
 * --------------------------------------------
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
try {
    require __DIR__ . '/app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
// Check Dates 
$saleMode = isOnSaleMode();

	if ($saleMode) {
		$bootstrap->run($app);
	} else {
		header("Location: https://foula-store.events/end/"); /* Redirect browser */
  		exit();
	}

