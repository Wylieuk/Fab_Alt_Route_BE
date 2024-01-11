<?php
defined("isInSideApplication")?null:die('no access');

#[AllowDynamicProperties]
class image_functions {

    private $params;
    public $resizedImage = '';

    function __construct() {

    }

    function resize($params) {

        $defaultParams = [
            'imgBlob' => null,
            'width' => null,
            'height' => null,
            'crop' => false,
            'caption' => null,
            'compression' => 80,
            'outputType' => 'jpg'
        ];

        $this->params = (object)array_merge($defaultParams, $params);

        //debug($this->params->crop);

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $ft = $fileInfo->buffer($this->params->imgBlob);
        $fileType = substr($ft, strpos($ft, "/") + 1);

        $this->img = new Imagick();
        //$img->setResolution($resolution, $resolution);

        if ($fileType === 'pdf') {
            $this->img->setColorspace(Imagick::COLORSPACE_SRGB);
        }

        $this->img->readImageBlob($this->params->imgBlob);
        $this->img->setImageFormat($this->params->outputType);
        $this->img->setImageCompressionQuality($this->params->compression);

        if ($this->params->crop) { 
            $this->resizeWithCrop();
        } else {
            $this->resizeNoCrop();
        }

        // if (isset($this->params->caption) && !empty($this->params->caption)) {
        //     $this->params->caption = urldecode($this->params->caption);
        //     $this->addCaption($this->params->caption);
        // }

        $this->resizedImage = $this->img->getImageBlob();
    }

    //returns the size of the current imgagick object
    function getImageSize() {
        return (object)[
            'width' => $this->img->getImageWidth(),
            'height' => $this->img->getImageHeight()
        ];
    }

    static function setFileType(string $base64Blob, string $fileType ){
        $imagick = new Imagick();
        $imagick->readImageBlob(base64_decode($base64Blob));

        switch($fileType){
            case 'jpg':
                $imagick->setFormat('jpeg');
                break;
            case 'gif':
                $imagick->setFormat('jpeg');
                break;
            case 'png':
                $imagick->setFormat('jpeg');
                break;
            default:
            throw new Exception("file type not supported");
        }

        return base64_encode($imagick->getImageBlob());
        
    }


    static function base64FitToConstraints(string $base64Blob, int $maxWidth, int $maxHeight, int $quality=80){

        $imagick = new Imagick();
        $imagick->readImageBlob(base64_decode($base64Blob));
        if($imagick->getImageWidth() > $maxWidth || $imagick->getImageHeight >$maxHeight){
            $imagick->resizeImage(
                min($imagick->getImageWidth(),  $maxWidth),
                min($imagick->getImageHeight(), $maxHeight),
                Imagick::FILTER_CATROM,
                1,
                true
            );
            $imagick->setImageCompressionQuality($quality);
            return base64_encode($imagick->getImageBlob());
        }

        else {
            return $base64Blob;
        }
        
        
    }

    static function base64ToBlob($base64String){
        $imagick = new Imagick();
        $imagick->readImageBlob(base64_decode($base64String));
        return $imagick->getImageBlob();
    }

    static function blobToBase64($blob){
        return base64_encode($blob);
    }


    /*
    * t: Checks if an image is smaller than the size you want to resize it to. If it is then the image is rejected
    ***************************************/
    function fitsConstraints() {
        $currentSize = $this->getImageSize();
        
        switch (true) {
            case $currentSize->width < $this->params->width && $currentSize->height < $this->params->height:
                return -1;

            case $currentSize->width == $this->params->width && $currentSize->height == $this->params->height:
                return 0;

            default:
                return 1;  
        }
    }

    /*
    * t: Resizes an image to a set height and width without cropping it. Whitespace is added to the final image to preserve the original aspect ratio.
    ***************************************/
    function resizeNoCrop() {
        $this->img->scaleImage(0, $this->params->height);
        $currentSize = $this->getImageSize();
        // if after resizing the height, the width is still larger than the final canvas
        if ($currentSize->width > $this->params->width) {
            $this->img->scaleImage($this->params->width, 0);
            $currentSize = $this->getImageSize();
        }
        $canvas = new Imagick();
        $canvas->newImage($this->params->width, $this->params->height, 'white', 'jpg');
        // TERRY! IF YOU WANT TO GO BACK TO CROPPED IMAGES BEING CENTERED, UNCOMMENT THE LINE BELOW AND COMMENT OUT LINE 157
        // $offsetX = intval(($this->params->width  / 2) - ($currentSize->width  / 2));
        $offsetX = 0;
        $offsetY = intval(($this->params->height / 2) - ($currentSize->height / 2));
        $canvas->compositeImage($this->img, Imagick::COMPOSITE_OVER, $offsetX, $offsetY );
        $this->img = $canvas;
    }

    /*
    * t: Resizes an image to a set height and width by cropping it.
    ***************************************/
    function resizeWithCrop() {
        $currentSize = $this->getImageSize();
        $ratio = $this->params->width / $this->params->height;
            $oldRatio = $currentSize->width / $currentSize->height;

            if ($ratio > $oldRatio) {
                $newWidth = $this->params->width;
                $newHeight = intval($this->params->width / $currentSize->width * $currentSize->height);
                $cropX = 0;
                $cropY = intval(($newHeight - $this->params->height) / 2);
            } else {
                $newWidth = intval($this->params->height / $currentSize->height * $currentSize->width);
                $newHeight = $this->params->height;
                $cropX = intval(($newWidth - $this->params->width) / 2);
                $cropY = 0;
            }

            $this->img->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 0.9, true);
            $this->img->cropImage($this->params->width, $this->params->height, $cropX, $cropY);
            $this->img->setImagePage($this->params->width, $this->params->height, 0, 0);
    }

    /*
    * t: Creates a small white space below the image and adds a caption
    * todo: Add another param to determine where to place the text
    ***************************************/
    function addCaption($text) {
        $width = $this->getImageSize()->width;
        $height = $this->getImageSize()->height;
        $this->img->scaleImage(0, ($height - 30));
        $currentSize = $this->getImageSize();

        $canvas = new Imagick();
        $canvas->newImage($width, $height, 'white', 'jpg');
        $offsetX = intval(($width  / 2) - ($currentSize->width  / 2));
        $offsetY = 0;
        $canvas->compositeImage($this->img, Imagick::COMPOSITE_OVER, $offsetX, $offsetY );
        
        $this->img = $canvas;

        $draw = new ImagickDraw();
        //$draw->setFont('Arial');
        $draw->setFontSize(11);
        $draw->setFillColor('black');
        $draw->setFontStyle(Imagick::STYLE_ITALIC); 
        $draw->setGravity(Imagick::GRAVITY_SOUTH);
        $this->img->annotateImage($draw, 0, 8, 0, $text);
    }

    function getImageBlob() {
        return $this->resizedImage;
    }
}