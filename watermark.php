<?php
// Исходное изображение
$sourFile = 's.jpg';
// Маска-watermark
$maskFile = 'm.png';
// Конечное изображение
$saveFile = 'd.jpg';
 
// Открываем исходное изображение
$sour = imageCreateFromJpeg($sourFile);
// Открываем маску
$mask = imageCreateFromPng($maskFile);
 
// Получаем высоту и ширину изображения
$imgSize = getImageSize($sourFile);
$imgWidth = $imgSize[0];
$imgHeight = $imgSize[1];
 
// Создаем изображение, куда будет копировать конечно изображение
$dest = imageCreateTrueColor($imgWidth, $imgHeight);
 
// Бегаем по высоте 
for ($y = 0; $y < $imgHeight; $y++) { 
    // Бегаем по ширине
    for ($x = 0; $x < $imgWidth; $x++) {
        // Получаем цвета пикселя с вотермарка
        $SourRgb = getPixColor($sour, $x, $y);
        // Получаем цвета пикселя с маски
        $MaskRgb = getPixColor($mask, $x, $y);
        // Обращаем цвет RBG в обратную строну
        $red   = unBlend($SourRgb['r'], $MaskRgb['r'], $MaskRgb['a']);
        $green = unBlend($SourRgb['g'], $MaskRgb['g'], $MaskRgb['a']);
        $blue  = unBlend($SourRgb['b'], $MaskRgb['b'], $MaskRgb['a']);
        // Соединяем цвета в формат RGB
        $pixelcolor = ( $red << 16 ) | ( $green << 8 ) | $blue ;
        imagesetpixel($dest, $x, $y, $pixelcolor);
    }
}
 
// Сохраняем чистое изображение
imagejpeg($dest, $saveFile);
imagedestroy($dest);
 
 
/**
 * Обращает цвет вотермарка в исходный цвет
 * @param integer $pDest Цвет вотермарка
 * @param integer $pMask Цвет маски
 * @param float $pAlpha уровень прозрачности. От 0(полностью прозрачно) до 1(полностью непрозрачно)
 * @return integer 
 */
function unBlend( $pDest, $pMask, $pAlpha ){
    $color = $pMask;
    // Если $pMask == 1, то обратить не получится
    if ( $pAlpha != 1 ){
        // Обратная формула от формулы наложения вотермарка ( dest = ( sour + ( mask - sour ) * alpha )
        $color = ( $pDest - $pAlpha * $pMask ) / ( 1 - $pAlpha );
        // Не выходим ли мы запределы 0..255
        $color = $color < 0 ? 0 : round($color);
        $color = $color > 255 ? 255 : $color;
    }
    return $color;
}
 
/**
 * Получаем цвет пикселя с прозрачностью. 
 * Возвращает в формате array('r'=>int, 'g'=>int, 'b'=>int, 'a'=>float);
 * @param gdImage $pImage Handle на изображение
 * @param integer $pX позиция по X
 * @param integer $pY позиция по Y
 * @return array 
 */
function getPixColor($pImage, $pX, $pY){
    // Получаем цвет изображения
    $rgb = imagecolorat($pImage, $pX, $pY);
    // Преобразуем
    $red = ($rgb >> 16) & 0xFF;
    $green = ($rgb >> 8) & 0xFF;
    $blue = $rgb & 0xFF;
    // Преобразуем alpha, так как в PNG 127 это полная прозрачность, а 0 - не прозрачность
    $alpha = abs(( ($rgb >> 24) & 0xFF ) / 127 - 1);
    return array('r'=>$red, 'g'=>$green, 'b' => $blue, 'a' => $alpha );
}
 
?>