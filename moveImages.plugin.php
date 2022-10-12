<?php
if(!defined('MODX_BASE_PATH')) die('What are you doing? Get out of here!');

if (!isset($e->params['template'])){$e->params['template'] = '';}
if (!isset($e->params['limit'])){$e->params['limit'] = '';}

if ($modx->event->name == 'OnDocFormSave') {
$output = '';

$res = $modx->db->select("id,content", $modx->getFullTableName('site_content'), "template='22'", "", "LIMIT 60");

while( $row = $modx->db->getRow( $res ) ) {

preg_match_all('/<img[^>]+src="?\'?([^"\']+)"?\'?[^>]*>/i', $row['content'], $images, PREG_SET_ORDER);

if(!file_exists(MODX_BASE_PATH."assets/galleries/".$row['id'])) {
mkdir(MODX_BASE_PATH."assets/galleries/".$row['id']);
}

foreach ($images as $image) {
$output .= '<div style="color:#fff;">'.$row['id'].'-'.$image[1] . '</div>';
$filename = MODX_BASE_PATH."assets/galleries/".$row['id']."/".basename($image[1]);
copy(MODX_BASE_PATH.$image[1], $filename);

$pathInfo = pathinfo($filename);
$size = getimagesize($filename);
$fileBytes = filesize($filename);
$imgProp = '{"width":'.$size[0].',"height":'.$size[1].',"size":'.$fileBytes.'}';

$fields = array('sg_image'  => MODX_BASE_PATH."assets/galleries/".$row['id']."/".basename($image[1]),
'sg_title' => $pathInfo["filename"],
'sg_properties'  => $imgProp,
'sg_rid' => $row['id'],
);
$modx->db->insert( $fields, $modx->getFullTableName('sg_images'));
}

$content = preg_replace("/<img (.*?)>/", '', $row['content']);
$fields2 = array('content'  => $content);
$modx->db->update( $fields2, $modx->getFullTableName('site_content'), 'id = "' . $row['id'] . '"' );

//удалить старые картинки в папке assets
}

echo $output;
die();
}
