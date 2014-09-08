<?php

/**
 * ImageCrop Class
 * 
 * This class is used to handle cropping functionalities of rasterized images.
 *
 * @package		File
 * @subpackage	Image
 * @category	Crop
 * 
 * @author		Can Berkol
 * @copyright   Biber Ltd. (www.biberltd.com)
 * 
 * @date        10.05.2013
 * @version     1.2.0
 */
use BiberLtd\Bundle\FileManagementBundle\Services\ImageUpload;

class ImageCrop extends ImageUpload {

    protected $cropratio;/** The width:height ratio of the crop */
    protected $cropwidth;/** The width of the cropped image */
    protected $cropheight;/** The height of the cropped image */

    /**
     *  @name 			__construct()
     *  				Constructor
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			string 	$path 		Source file (path).
     * 		
     */
    public function __construct($config) {
        parent::__construct($config);
        $this->set_cropratio(NULL);
        $this->set_cropwidth(NULL);
        $this->set_cropheight(NULL);
    }

    /**
     *  @name 			__destruct()
     *  				Destructor.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     */
    public function __destruct() {
        parent::__destruct();
        foreach ($this as $element) {
            $element = NULL;
        }
    }

    /**
     *  @name 			validate_cropdimension()
     *  				Checks if a given dimension is more or less than crop dimension.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			string 	$rule 		less, more
     *  @param			string	$dimension	width, height
     * 		
     * 	@return			bool
     */
    protected function validate_cropdimension($rule = 'less', $dimension = 'width') {
        switch ($dimension) {
            case 'height':
            case 'width':
                $dimension = $dimension;
                break;
            default:
                $dimension = 'width';
                break;
        }
        switch ($rule) {
            case 'more':
                if ($dimension == 'width') {
                    if ($this->get_width() >= $this->get_cropwidth()) {
                        return TRUE;
                    }
                } else if ($dimension == 'height') {
                    if ($this->get_height() >= $this->get_cropheight()) {
                        return TRUE;
                    }
                }
                return FALSE;
            case 'less':
            default:
                if ($dimension == 'width') {
                    if ($this->get_width() <= $this->get_cropwidth()) {
                        return TRUE;
                    }
                } else if ($dimension == 'height') {
                    if ($this->get_height() <= $this->get_cropheight()) {
                        return TRUE;
                    }
                }
                return FALSE;
        }
        return FALSE;
    }

    /**
     *  @name 			validate_cropwidth()
     *  				Checks if a width is more or less than crop width.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			string 	$rule 		less, more
     * 		
     * 	@return			bool
     */
    public function validate_cropwidth($rule = 'less') {
        return $this->validate_cropdimension($rule, 'width');
    }

    /**
     *  @name 			validate_cropheight()
     *  				Checks if a height is more or less than crop height.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			string 	$rule 		less, more
     * 		
     * 	@return			bool
     */
    public function validate_cropheight($rule = 'less') {
        return $this->validate_cropdimension($rule, 'height');
    }

    /**
     *  @name 			get_cropwidth()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     * 	@return			string 				Member value.
     */
    public function get_cropwidth() {
        return $this->cropwidth;
    }

    /**
     *  @name 			get_cropheight()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     * 	@return			string 				Member value.
     */
    public function get_cropheight() {
        return $this->cropheight;
    }

    /**
     *  @name 			get_cropratio()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     * 	@return			string 				Member value.
     */
    public function get_cropratio() {
        return $this->cropratio;
    }

    /**
     *  @name 			set_cropheight()
     *  				Sets cropheight member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			integer			$height	 	Image height.
     * 		
     * 	@return			object|bool		$this		Current object iteration.
     */
    public function set_cropheight($height) {
        if (!is_numeric($height) || $height < 0) {
            return FALSE;
        }
        $this->cropheight = $height;
        return $this;
    }

    /**
     *  @name 			set_cropwidth()
     *  				Sets extension member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			integer			$width	 	Image width.
     * 		
     * 	@return			object|bool		$this		Current object iteration.
     */
    public function set_cropwidth($width) {
        if (!is_numeric($width) || $width < 0) {
            return FALSE;
        }
        $this->cropwidth = $width;
        return $this;
    }

    /**
     *  @name 			set_copratio()
     *  				set_cropratio member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			integer		$width		 	Image width.
     *  @param 			integer		$height		 	Image height.
     * 		
     * 	@return			decimal		$ratio			Image width:height ratio.
     */
    public function set_cropratio($ratio) {
        if ($ratio != 1 && (!is_float($ratio) || $ratio < 0)) {
            return FALSE;
        }
        $this->cropratio = $ratio;
        return $this;
    }

    /**
     * @name 			crop()
     *  				Crop image based on given coordinates.
     * @since			1.2.0
     *
     *
     * @param           integer        $x       Crop x coordinate.
     * @param           integer        $y       Crop y coordinate.
     * @param           integer        $w       Crop width.
     * @param           integer        $h       Crop height.
     * @param           bool           $resize  Resize before crop?
     * @param           integer        $rw      Resized width
     * @param           integer        $rh      Resized height
     * @param           bool           $show    Either show the cropped image or keep it as resource
     *
     * @return		    mixed					Cropped image as resource or the $this
     */
    public function crop($x, $y, $w, $h, $resize = TRUE, $rw = 0, $rh = 0, $show = FALSE) {
        $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $this->set_cropwidth($w)->set_cropheight($h);
        $width = $this->get_cropwidth();
        $height = $this->get_cropheight();
        $cropped_img = imagecreatetruecolor($width, $height);

        $resource = $this->get_copy();

        imagecopyresampled($cropped_img, $resource, 0, 0, $x, $y, $w, $h, $w, $h);
        if ($resize) {
            $resized_img = imagecreatetruecolor($rw, $rh);
            imagecopyresampled($resized_img, $cropped_img, 0, 0, 0, 0, $rw, $rh, $width, $height);
            $cropped_img = $resized_img;
        }
        if ($show) {
            $extension = $this->get_extension();
            if (!in_array($extension, $allowed_extensions)) {
                return FALSE;
            }
            if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
                header('Content-type: image/jpeg');
                imagejpeg($cropped_img);
            } else if (strtolower($extension) == 'png') {
                header('Content-type: image/png');
                imagepng($cropped_img);
            } else if (strtolower($extension) == 'gif') {
                header('Content-type: image/gif');
                imagegif($cropped_img);
            }
        } else {
            $this->set_copy($cropped_img);
            return $this;
        }
    }

    /**
     *  @name 			crop_to_width()
     *  				Crop image based on given width.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			string		$from		 	top-left, top-right, bottom-right, bottom-left, 
     * 												absolute-center.
     * 		
     * 	@return			resource					Cropped image as resource.
     */
    public function crop_to_width($from = 'bottom-right', $show = TRUE) {
        $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $width = $this->get_cropwidth();
        $ratio = $this->get_cropratio();
        $height = ceil($width / $ratio);
        $cropped_img = imagecreatetruecolor($width, $height);
        $resource = $this->get_copy();
        $coor_x = 0;
        $coor_y = 0;
        switch ($from) {
            case 'top-left':
                if ($this->get_width() > $width) {
                    $coor_x = $this->get_width() - $width;
                }
                $coor_y = 0;
                break;
            case 'top-right':
                $coor_x = 0;
                $coor_y = 0;
                break;
            case 'bottom-right':
                $coor_x = 0;
                if ($this->get_height() > $height) {
                    $coor_y = $this->get_height() - $height;
                }
                break;
            case 'bottom-left':
                if ($this->get_width() > $width) {
                    $coor_x = $this->get_width() - $width;
                }
                if ($this->get_height() > $height) {
                    $coor_y = $this->get_height() - $height;
                }
                break;
            case 'absolute-center':
                if ($this->get_width() > $width) {
                    $coor_x = ceil($this->get_width() - $width) / 2;
                }
                if ($this->get_height() > $height) {
                    $coor_y = ceil($this->get_height() - $height) / 2;
                }
                break;
            default:
                return FALSE;
        }
        if ($this->width < $width || $this->height < $height) {
            imagecopyresampled($cropped_img, $resource, 0, 0, $coor_x, $coor_y, $width, $height, $this->width, $this->height);
        } else {
            imagecopy($cropped_img, $resource, 0, 0, $coor_x, $coor_y, $width, $height);
        }
        if ($show) {
            $extension = $this->get_extension();
            if (!in_array($extension, $allowed_extensions)) {
                return FALSE;
            }
            if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
                header('Content-type: image/jpeg');
                imagejpeg($cropped_img);
            } else if (strtolower($extension) == 'png') {
                header('Content-type: image/png');
                imagepng($cropped_img);
            } else if (strtolower($extension) == 'gif') {
                header('Content-type: image/gif');
                imagegif($cropped_img);
            }
        } else {
            $this->set_copy($cropped_img);
            return $this;
        }
    }

    /**
     *  @name 			crop_to_width()
     *  				Crop image based on given height.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			string		$from		 	top-left, top-right, bottom-right, bottom-left, 
     * 												absolute-center.
     * 		
     * 	@return			resource					Cropped image as resource.
     */
    public function crop_to_height($from = 'top', $show = TRUE) {
        $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $height = $this->get_cropheight();
        $ratio = $this->get_cropratio();
        $witdh = ceil($height * $ratio);
        $cropped_img = imagecreatetruecolor($width, $height);
        $resource = $this->get_copy();
        $coor_x = 0;
        $coor_y = 0;
        switch ($from) {
            case 'top-left':
                if ($this->get_width() > $width) {
                    $coor_x = $this->get_width() - $width;
                }
                $coor_y = 0;
                break;
            case 'top-right':
                $coor_x = 0;
                $coor_y = 0;
                break;
            case 'bottom-right':
                $coor_x = 0;
                if ($this->get_height() > $height) {
                    $coor_y = $this->get_height() - $height;
                }
                break;
            case 'bottom-left':
                if ($this->get_width() > $width) {
                    $coor_x = $this->get_width() - $width;
                }
                if ($this->get_height() > $height) {
                    $coor_y = $this->get_height() - $height;
                }
                break;
            case 'absolute-center':
                if ($this->get_width() > $width) {
                    $coor_x = ceil($this->get_width() - $width) / 2;
                }
                if ($this->get_height() > $height) {
                    $coor_y = ceil($this->get_height() - $height) / 2;
                }
                break;
        }

        imagecopy($resource, $cropped_img, 0, 0, $coor_x, $coor_y, $width, $height);
        if ($show) {
            $extension = $this->get_extension();
            if (!in_array($extension, $allowed_extensions)) {
                return FALSE;
            }
            if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
                header('Content-type: image/jpeg');
                imagejpeg($cropped_img);
            } else if (strtolower($extension) == 'png') {
                header('Content-type: image/png');
                imagepng($cropped_img);
            } else if (strtolower($extension) == 'gif') {
                header('Content-type: image/gif');
                imagegif($cropped_img);
            }
        } else {
            $this->set_copy($cropped_img);
            return $this;
        }
    }

}
