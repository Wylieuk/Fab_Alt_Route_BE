<?php defined("isInSideApplication")?null:die('no access');
/*
* usage: index.php?page=web2pdf&title=xxx&relative_url=<urlencoded(index.php?....)>
***************************************/
//

if (!isset($this->data['relative_url']) || !isset($this->data['title'])){
    die('relative url and file name required');
}
//echo '<pre>' . $config['siteaddress']. '/' . (str_replace('&amp;', '&', urldecode($this->data['relative_url']))) . '&cb=' . time() . '</pre>';exit;
//debug($config['siteaddress']. '/' . urldecode($this->data['relative_url']) . '&cb=' . time());exit;

require_once('libs/ConvertApi/autoload.php');

use \ConvertApi\ConvertApi;

ConvertApi::setApiSecret('cj7qRwKOfIr9PBOU');

$pdfConfig = (object)[
   'fromFormat' => 'web',
   'conversionTimeout' => 50,
   'dir' => 'pdfs',
   'title' => $this->data['title'],
   'saveFile' => false
];

set_time_limit($pdfConfig->conversionTimeout + 10);

$result = ConvertApi::convert(
    'pdf',
    [
        'Url' => $config['siteaddress']. '/' . (str_replace('&amp;', '&', urldecode($this->data['relative_url']))) . '&cb=' . time(),
        'FileName' => $pdfConfig->title,
        'WaitElement' => '#RENDER_COMPLETE',
        'PageSize' => 'a4',
        'Timeout' => $pdfConfig->conversionTimeout,
        'CssMediaType' => 'print',
        'JavaScript' => true,
        'PageOrientation' => 'portrait',
        'MarginTop' => 2,
        'MarginRight' => 2,
        'MarginBottom' => 2,
        'MarginLeft' => 2
    ],
    $pdfConfig->fromFormat,
    $pdfConfig->conversionTimeout
);

if ($pdfConfig->saveFile){
    $savedFiles = $result->saveFiles($pdfConfig->dir);
}

$pdfContent = $result->getFile()->getContents();



header("Content-type:application/pdf");
header('Content-Disposition:attachment;filename=' . $pdfConfig->title . '.pdf');
echo $pdfContent;

exit;