<?php

// ------------------------------------------------------------------------
/**
 * File Class
 * 
 * This class is used to handle files and folders.
 *
 * @package		File
 * @subpackage	File
 * @category	File
 * 
 * @author		Can Berkol
 * @copyright   Biber Ltd. (www.biberltd.com)
 * 
 * @date        24.09.2012
 * @version     1.0.6
 */
namespace BiberLtd\Bundle\FileManagementBundle\Services;
use BiberLtd\Bundle\FileManagementBundle\Services\FileUpload;

class ImageUpload extends FileUpload {

    protected $blob;/** Image blob / string */
    protected $ratio;/** Source image width:height ratio */
    protected $width;/** Image width in pixels */
    protected $height;/** Image height in pixels */
    protected $copy;/** Copy of the original image */
    protected $canvas_width;/** Width of canvas */
    protected $canvas_height;/** Height of canvas */
    protected $canvas_m_top;/** Margin top of canvas */
    protected $canvas_m_bottom;/** Margin bottom of canvas */
    protected $canvas_m_left;/** Margin left of canvas */
    protected $canvas_m_right;/** Margin right of canvas */
    protected $canvas_color;/** Margin background color */
    protected $canvas;/** the canvas resource */
    protected $quality = 100;/** Image quality */

    /**
     * @name 		__construct()
     *  			Constructor
     * @author      Can Berkol
     * @since		1.0.1
     * @version     1.2.0
     * 
     * @param 		array          $config
     * 		
     */
    public function init($config) {
        parent::__construct($config);
        if ($this->exists()) {
            $this->set_source($config['path']);
            $img_size = getimagesize($this->get_source());
            $this->set_width($img_size[0]);
            $this->set_height($img_size[1]);
            $this->set_ratio($this->calculate_ratio($this->get_width(), $this->get_height()));
            $this->set_copy($this->create_copy($this->get_source()));
        } else {
            $this->set_source(NULL);
            $this->set_ratio(NULL);
            $this->set_width(NULL);
            $this->set_height(NULL);
            ;
            if (isset($config['blob'])) {
                $this->file_mode = 'virtual';
                if (is_resource($config['blob']) && get_resource_type($config['blob']) == 'gd') {
                    switch ($config['blob_type']) {
                        case 'png':
                            $this->set_copy($config['blob']);
                            break;
                        case 'jpg':
                            $this->set_copy($config['blob']);
                            break;
                        case 'gif':
                            $this->set_copy($config['blob']);
                            break;
                    }
                } else {
                    $this->set_copy(imagecreatefromstring($config['blob']));
                }
                $this->width = imagesx($this->copy);
                $this->height = imagesy($this->copy);
            }
        }
        if (isset($config['quality'])) {
            $this->set_quality($config['quality']);
        }
        $this->canvas_width = $this->canvas_height = 0;
        $this->canvas_m_bottom = $this->canvas_m_top = 0;
        $this->canvas_m_left = $this->canvas_m_right = 0;
        $this->canvas = null;
    }

    /**
     *  @name 			__destruct()
     *  				Destructor.
     *  @author         Can Berkol
     * 	@since			1.0.1
     * 
     */
    public function __destruct() {
        parent::__destruct();
        foreach ($this as $element) {
            $element = NULL;
        }
    }

    /**
     *  @name 			get_width()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 		
     * 	@return			string 				Member value.
     */
    public function get_width() {
        return $this->width;
    }

    /**
     *  @name 			get_canvas_width()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.1.3
     * 		
     * 	@return			string 				Member value.
     */
    public function get_canvas_width() {
        return $this->canvas_width;
    }

    /**
     *  @name 			get_height()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     * 	@return			string 				Member value.
     */
    public function get_height() {
        return $this->height;
    }

    /**
     *  @name 			get_canvas_height()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.1.3
     * 		
     * 	@return			string 				Member value.
     */
    public function get_canvas_height() {
        return $this->canvas_height;
    }

    /**
     *  @name 			get_canvas_m_top()
     *  				Returns the value of top margin.
     *  @author         Can Berkol
     * 	@since			1.1.3
     * 		
     * 	@return			string 				Member value.
     */
    public function get_canvas_m_top() {
        return $this->canvas_m_top;
    }

    /**
     *  @name 			get_canvas_m_bottom()
     *  				Returns the value of bottom margin.
     *  @author         Can Berkol
     * 	@since			1.1.3
     * 		
     * 	@return			string 				Member value.
     */
    public function get_canvas_m_bottom() {
        return $this->canvas_m_bottom;
    }

    /**
     *  @name 			get_canvas_m_left()
     *  				Returns the value of left margin.
     *  @author         Can Berkol
     * 	@since			1.1.3
     * 		
     * 	@return			string 				Member value.
     */
    public function get_canvas_m_left() {
        return $this->canvas_m_left;
    }

    /**
     *  @name 			get_canvas_m_right()
     *  				Returns the value of right margin.
     *  @author         Can Berkol
     * 	@since			1.1.3
     * 		
     * 	@return			string 				Member value.
     */
    public function get_canvas_m_right() {
        return $this->canvas_m_right;
    }

    /**
     *  @name 			set_width()
     *  				Sets member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @version        1.2.0
     * 
     *  @param 			integer 	$width 		Image width.
     * 		
     * 	@return			object		$this		Current object iteration.
     */
    public function set_width($width) {
        if (!is_integer($width) || $width < 0) {
            return FALSE;
        }
        $this->width = $width;
        return $this;
    }

    /**
     *  @name 			set_height()
     *  				Sets member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @version        1.2.0
     * 
     *  @param 			integer 	$height 		Image height.
     * 		
     * 	@return			object		$this		Current object iteration.
     */
    public function set_height($height) {
        if (!is_integer($height) || $height < 0) {
            return FALSE;
        }
        $this->height = $height;
        return $this;
    }

    /**
     *  @name 			get_source()
     *  				Gets member.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     * 	@return			string 				Member value.
     */
    public function get_source() {
        return $this->file_path;
    }

    /**
     *  @name 			set_copy()
     *  				Sets copy member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			resource		$image	 	Image resource.
     * 		
     * 	@return			object|bool		$this		Current object iteration.
     */
    protected function set_copy($image) {
        if (!is_resource($image)) {
            return FALSE;
        }
        $this->copy = $image;
        return $this;
    }

    /**
     *  @name 			get_copy()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     * 	@return			object 				Member value.
     */
    public function get_copy() {
        return $this->copy;
    }

    /**
     * @name 		    add_caption()
     *  				Adds text caption to the image.
     * @author          Can Berkol
     * @since			1.1.3
     * 
     * @version         1.2.3
     * 
     * @param           string      $text           Caption text.
     * @param           float       $size           Font size
     * @param           float       $angle          Angle of text
     * @param           mixed       $position       Either  array that contains x and y coordinates or one of the below strings:
     *                                              top-left, top-center, top-right, center-left, center, center-right, bottom-left, 
     *                                              bottom-center, bottom-right
     * @param           string      $color          i.e. #fff
     * @param           string      $font           Path of the font file to be used.
     * @param           array       $margin         top,right,bottom,left
     * 
     * @return          object      $this
     */
    public final function add_caption($text, $size = 12, $angle = 0, $position = 'bottom-center', $color = '#fff', $font = 'TIMES.ttf', $margin = array()) {
        if (empty($color)) {
            $color = '#fff';
        }
        $color = str_replace('#', '', $color);
        /** If color is given in shorthand form we'll complete the code */
        if (strlen($color) == 3) {
            $color = $color . $color;
        }
        $textcolor = imagecolorallocate($this->copy, hexdec('0x' . $color{0} . $color{1}), hexdec('0x' . $color{2} . $color{3}), hexdec('0x' . $color{4} . $color{5}));
        $text_box = imagettfbbox($size, $angle, $font, $text);
        $text_width = abs($text_box[2]) - abs($text_box[0]);
        $text_height = abs($text_box[7]) - abs($text_box[1]);
        /**
         * Resize text automatically if it is larger than the image.
         * If there is a canvas set we need to disregard the original image size.
         */
        if (isset($this->canvas)) {
            if ($text_width > $this->canvas_width) {
                $scale = $this->canvas_width / $text_width;
                $size = $size * $scale;
                $text_box = imagettfbbox($size, $angle, $font, $text);
                $text_width = abs($text_box[2]) - abs($text_box[0]);
                $text_height = abs($text_box[7]) - abs($text_box[1]);
            }
            if ($text_height > $this->canvas_height) {
                $scale = $this->canvas_height / $text_height;
                $size = $size * $scale;
                $text_box = imagettfbbox($size, $angle, $font, $text);
                $text_width = abs($text_box[2]) - abs($text_box[0]);
                $text_height = abs($text_box[7]) - abs($text_box[1]);
            }
        } else {
            if ($text_width > $this->width) {
                $scale = $this->width / $text_width;
                $size = $size * $scale;
                $text_box = imagettfbbox($size, $angle, $font, $text);
                $text_width = abs($text_box[2]) - abs($text_box[0]);
                $text_height = abs($text_box[7]) - abs($text_box[1]);
            }
            if ($text_height > $this->height) {
                $scale = $this->height / $text_height;
                $size = $size * $scale;
                $text_box = imagettfbbox($size, $angle, $font, $text);
                $text_width = abs($text_box[2]) - abs($text_box[0]);
                $text_height = abs($text_box[7]) - abs($text_box[1]);
            }
        }

        $text_ratio = $text_width / $text_height;
        $x = $y = 0;
        /**
         * Change caption width and height if there is a canvas set
         */
        if (isset($this->canvas)) {
            $width = $this->canvas_width;
            $height = $this->canvas_height;
        } else {
            $width = $this->width;
            $height = $this->height;
        }
        if (is_array($position)) {
            $x = $position['x'];
            $y = $position['y'];
        } else {
            switch ($position) {
                case 'top-left':
                    $x = 0;
                    $y = $text_height;
                    break;
                case 'top-center':
                    $x = ceil(($width - $text_width) / 2);
                    $y = $text_height;
                    break;
                case 'top-right':
                    $x = $width - $text_width;
                    $y = $text_height;
                    break;
                case 'center-left':
                    $x = 0;
                    $y = ceil(($height - $text_height) / 2);
                    break;
                case 'center-center':
                    $x = ceil(($width - $text_width) / 2);
                    $y = ceil(($height - $text_height) / 2);
                    break;
                case 'center-right':
                    $x = $width - $text_width;
                    $y = ceil(($height - $text_height) / 2);
                    break;
                case 'bottom-left':
                    $x = 0;
                    $y = $height;
                    break;
                case 'bottom-center':
                    $x = ceil(($width - $text_width) / 2);
                    $y = $height;
                    break;
                case 'bottom-right':
                    $x = $width - $text_width;
                    $y = $height;
                    break;
            }
        }
        /**
         * Finally, set margins
         */
        if (count($margin) > 0) {
            foreach ($margin as $position => $value) {
                switch ($position) {
                    case 'bottom':
                        $y -= $value;
                        break;
                    case 'top':
                        $y += $value;
                        break;
                    case 'right':
                        $x -= $value;
                        break;
                    case 'left':
                        $x += $value;
                        break;
                }
            }
        }
        /**
         * Add the caption
         */
        imagettftext($this->copy, $size, $angle, $x, $y, $textcolor, $font, $text);

        return $this;
    }

    /**
     * @name 		add_canvas()
     *  			Adds canvas / frame around original image.
     * @author      Can Berkol
     * @since		1.1.3
     * 
     * @version     1.2.0
     * 
     * @param       array               $margins - top, bottom, left, right
     * @param       array               $dimensions - width, height
     * @param       string              $color      HTML code i.e. #fffff
     * 		
     * @return	    object 				$this.
     */
    public final function add_canvas($margins = array(), $dimensions = array(), $color = '#fff') {
        $width = $this->width;
        $height = $this->height;
        $m_top = 0;
        $m_bottom = 0;
        $m_left = 0;
        $m_right = 0;
        /**
         * If a fresh dimension is provided for the canvas we'll set main image
         * width and height acordingly. Note that canvas cannot be smaller or equal 
         * to the image's original dimensions. It must be bigger!
         */
        if (count($dimensions) > 0) {
            if (isset($dimensions['width'])) {
                if ($dimensions['width'] > $this->width) {
                    $width = $dimensions['width'];
                }
            }
            if (isset($dimensions['height'])) {
                if ($dimensions['height'] > $this->height) {
                    $height = $dimensions['height'];
                }
            }
        }
        /**
         * Now we'll apply dimensions. Don't forget if fresh dimensions are provided
         * this function overwrites the supplied margin values.
         */
        if (count($margins) > 0 && count($dimensions) == 0) {
            foreach ($margins as $margin => $value) {
                if ($value < 0) {
                    $this->error_log[] = array('class' => get_class(__CLASS__),
                        'method' => __METHOD__,
                        'error' => ExF3x003,
                        'hint' => 'Margin (' . $margin . ') value is ' . $value . ' Margin value is set to zero.',
                        'time' => date('Y-m-d H:i'),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'exception' => ''
                    );
                    $value = 0;
                }
                switch ($margin) {
                    case 'top':
                        $m_top = $value;
                        break;
                    case 'bottom':
                        $m_bottom = $value;
                        break;
                    case 'left':
                        $m_left = $value;
                        break;
                    case 'right':
                        $m_right = $value;
                        break;
                }
            }
        } else if (count($margins)) {
            if (isset($margins['top']) && isset($margins['bottom'])) {
                $m_top = $margins['top'];
                $m_bottom = $margins['bottom'];
            } else if (isset($margins['top']) && !isset($margins['bottom'])) {
                $m_top = $margins['top'];
                $m_bottom = $height - ($this->height + $m_top);
            } else if (!isset($margins['top']) && isset($margins['bottom'])) {
                $m_bottom = $margins['bottom'];
                $m_top = $height - ($this->height + $m_bottom);
            } else {
                $m_top = $m_bottom = ($dimensions['height'] - $this->height) / 2;
            }
            if (isset($margins['left']) && isset($margins['right'])) {
                $m_left = $margins['left'];
                $m_right = $margins['right'];
            } else if (isset($margins['left']) && !isset($margins['right'])) {
                $m_left = $margins['left'];
                $m_right = $width - ($this->width + $m_left);
            } else if (!isset($margins['left']) && isset($margins['right'])) {
                $m_right = $margins['right'];
                $m_left = $height - ($this->width + $m_right);
            } else {
                $m_left = $m_right = ($dimensions['width'] - $this->width) / 2;
            }
        }
        $this->canvas_m_left = $m_left;
        $this->canvas_m_right = $m_right;
        $this->canvas_m_top = $m_top;
        $this->canvas_m_bottom = $m_bottom;
        $this->canvas_height = $this->height + $this->canvas_m_top + $this->canvas_m_bottom;
        $this->canvas_width = $this->width + $this->canvas_m_left + $this->canvas_m_right;

        $this->canvas = imagecreatetruecolor($this->canvas_width, $this->canvas_height);
        /** color the canvas */
        $color = str_replace('#', '', $color);
        /** If color is given in shorthand form we'll complete the code */
        if (strlen($color) == 3) {
            $color = $color . $color;
        }
        $color = imagecolorallocate($this->canvas, hexdec('0x' . $color{0} . $color{1}), hexdec('0x' . $color{2} . $color{3}), hexdec('0x' . $color{4} . $color{5}));
        imagefill($this->canvas, 0, 0, $color);
        /**
         * Finally we will add the original image on canvas and replace the copy.
         */
        imagecopy($this->canvas, $this->copy, $this->canvas_m_left, $this->canvas_m_top, 0, 0, $this->width, $this->height);
        $this->copy = $this->canvas;
        return $this;
    }

    /**
     *  @name           resize()
     * 
     *  @author         Can Berkol
     *  @since          1.1.0
     * 
     *  @version        1.1.3
     * 
     *  @param          integer         $width
     *  @param          integer         $height
     *  @param          string          $resizeto   width, height
     *  @param          bool            $keepratio
     * 
     *  @return         this
     */
    public function resize($width = NULL, $height = NULL, $resizeto = 'width', $keepratio = TRUE) {
        if (($width == NULL && $height == NULL) || (!is_integer($width) && !is_integer($height))) {
            return FALSE;
        }
        $resizeto_options = array('width', 'height');
        if (!in_array($resizeto, $resizeto_options)) {
            return FALSE;
        }

        if (is_null($this->canvas)) {
            $cur_height = $this->get_height();
            $cur_width = $this->get_width();
        } else {
            $cur_height = $this->canvas_height;
            $cur_width = $this->canvas_width;
        }

        if ($height == NULL) {
            $height = $cur_height;
        }
        if ($width == NULL) {
            $width = $cur_width;
        }

        $resource = $this->get_copy();
        /**
         * If ratio is not important...
         */
        if (!$keepratio) {
            $resized_img = imagecreatetruecolor($width, $height);
        } else {
            /**
             * if both width & height is set, still width is the priority
             */
            if ($resizeto == 'height') {
                if ($height >= $cur_height) {
                    $ratio = $cur_height / $height;
                } else {
                    $ratio = $height / $cur_height;
                }
                $width = $cur_width * $ratio;
            } else if ($resizeto == 'width') {
                if ($width >= $cur_width) {
                    $ratio = $cur_width / $width;
                } else {
                    $ratio = $width / $cur_width;
                }
                $height = $cur_height * $ratio;
            }
        }
        $this->width = $width;
        $this->height = $height;
        $resized_img = imagecreatetruecolor($width, $height);

        /** Preserve transperency */
        if (strtolower($this->extension) == 'png' || strtolower($this->extension == 'gif')) {
            imagecolortransparent($resized_img, imagecolorallocatealpha($resized_img, 0, 0, 0, 127));
            imagealphablending($resized_img, false);
            imagesavealpha($resized_img, true);
        }
        /**         * *** */
        imagecopyresampled($resized_img, $resource, 0, 0, 0, 0, $width, $height, $cur_width, $cur_height);
        $this->copy = $resized_img;

        return $this;
    }

    /**
     *  @name 			save_copy()
     *  				Echoes the copy resource.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param          $path
     */
    public function save_copy($path) {
        $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $extension = $this->get_extension();
        $resource = $this->get_copy();
        $quality = $this->quality;
        if ($path == '') {
            return FALSE;
        }
        if (!in_array($extension, $allowed_extensions)) {
            return FALSE;
        }
        if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
            imagejpeg($resource, $path, $quality);
        } else if (strtolower($extension) == 'png') {
            if ($this->quality > 9) {
                $quality = (1 - ($quality / 100)) * 9;
            }
            imagepng($resource, $path, $quality);
        } else if (strtolower($extension) == 'gif') {
            imagegif($resource, $path);
        }
    }

    /**
     *  @name 			show_copy()
     *  				Echoes the copy resource.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     */
    public function show_copy() {
        $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $extension = $this->get_extension();
        $resource = $this->get_copy();
        $quality = $this->quality;
        if (!in_array($extension, $allowed_extensions)) {
            return FALSE;
        }
        if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
            header('Content-type: image/jpeg');
            imagejpeg($resource, null, $quality);
        } else if (strtolower($extension) == 'png') {
            if ($this->quality > 9) {
                $quality = (1 - ($quality / 100)) * 9;
            }
            header('Content-type: image/png');
            imagepng($resource, null, $quality);
        } else if (strtolower($extension) == 'gif') {
            header('Content-type: image/gif');
            imagegif($resource);
        }
    }

    /**
     *  @name 			get_string()
     *  				Returns the string of an image.
     *  @author         Can Berkol
     * 	@since			1.2.2
     * 
     *  @return         string
     */
    public function get_string() {
        $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $extension = $this->get_extension();
        $resource = $this->get_copy();
        $quality = $this->quality;
        $img_str = '';
        ob_start();
        if (!in_array($extension, $allowed_extensions)) {
            return FALSE;
        }
        if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
            imagejpeg($resource, null, $quality);
        } else if (strtolower($extension) == 'png') {
            if ($this->quality > 9) {
                $quality = (1 - ($quality / 100)) * 9;
            }
            imagepng($resource, null, $quality);
        } else if (strtolower($extension) == 'gif') {
            imagegif($resource);
        }
        $img_str = ob_get_contents();
        ob_end_clean();

        return $img_str;
    }

    /**
     *  @name 			set_dpi()
     *  				Sets the dpi of a jpeg image.
     * 
     *  @author         Can Berkol
     * 	@since			1.2.2
     * 
     *  @param          int        $x
     *  @param          int        $y
     * 
     *  @return         object
     */
    public function set_dpi($x, $y = null) {
        if ($y == null) {
            $y = $x;
        }
        $img_str = $this->get_string();

        $img_str = substr_replace($img_str, pack('cnn', 1, $x, $y), 13, 5);

        $this->set_copy(imagecreatefromstring($img_str));

        return $this;
    }

    /**
     *  @name 			create_copy()
     *  				Creates a copy of the file as an image resource. Only jpeg, jpeg, gif, and png
     * 					extensions are allowed.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     *  @param 			string 		$source 	Absolute or relative file path.
     * 	@return			object 					The current itereation of the object.
     */
    protected function create_copy($source) {
        $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
        $extension = $this->get_extension();
        if (!in_array($extension, $allowed_extensions)) {
            return FALSE;
        }
        if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
            $copy = imagecreatefromjpeg($source);
        } else if (strtolower($extension) == 'png') {
            $dstimage = imagecreatetruecolor($this->get_width(), $this->get_height());
            $srcimage = imagecreatefrompng($source);
            imagecopyresampled($dstimage, $srcimage, 0, 0, 0, 0, $this->get_width(), $this->get_height(), $this->get_width(), $this->get_height());
            $copy = imagecreatefrompng($source);
        } else if (strtolower($extension) == 'gif') {
            $copy = imagecreatefromgif($source);
        }
        return $copy;
    }

    /**
     *  @name 			set_source()
     *  				Sets member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			string 	$filepath 	Absolute or relative file path.
     * 		
     * 	@return			object	$this		Current object iteration.
     */
    public function set_source($filepath) {
        $this->file_path = $filepath;
        if ($this->get_ratio() == NULL && $this->get_width() == NULL && $this->get_height() && NULL) {
            $this->path = $filepath;
        }
        return $this;
    }

    /**
     *  @name 			set_quality()
     *  				Sets the image quality.
     *  @author         Can Berkol
     * 	@since			1.2.0
     * 
     *  @param 			integer              A number between 0 and 100
     * 		
     * 	@return			object	$this		Current object iteration.
     */
    public function set_quality($quality) {
        $quality = floor($quality);
        if ($quality > 100) {
            $quality = 100;
        }
        if ($quality < 0) {
            $quality = 0;
        }
        $this->quality = $quality;
        return $this;
    }

    /**
     *  @name 			get_quality()
     *  				Sets the image quality.
     *  @author         Can Berkol
     * 	@since			1.2.0
     * 		
     * 	@return			integer
     */
    public function get_quality() {
        return $this->quality;
    }

    /**
     *  @name 			calculate_ratio()
     *  				Calculates the width:height ratio.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			integer		$width		 	Image width.
     *  @param 			integer		$height		 	Image height.
     * 		
     * 	@return			decimal		$ratio			Image width:height ratio.
     */
    protected function calculate_ratio($width, $height) {
        if (!is_integer($width) || $width < 0) {
            return FALSE;
        }
        if (!is_integer($height) || $height < 0) {
            return FALSE;
        }
        $ratio = $width / $height;
        return $ratio;
    }

    /**
     *  @name 			get_ratio()
     *  				Gets member value.
     *  @author         Can Berkol
     * 	@since			1.0.0.
     * 		
     * 	@return			string 				Member value.
     */
    public function get_ratio() {
        return $this->ratio;
    }

    /**
     *  @name 			set_ratio()
     *  				Sets member.
     *  @author         Can Berkol
     * 	@since			1.0.0
     * 
     *  @param 			decimal 		$ratio 		Width:height ratio.
     * 		
     * 	@return			object|bool		$this		Current object iteration.
     */
    protected function set_ratio($ratio) {
        if (!is_float($ratio) || $ratio < 0) {
            return FALSE;
        }
        $this->ratio = $ratio;
        return $this;
    }

    /**
     *  @name 			watermark()
     *  				Adds watermark to the image.
     *  @author         Can Berkol
     * 	@since			1.1.0
     *  @version        1.2.4
     *  @param 			BBR_File_Image  $source 	File path of the watermark
     *  @param          array           $watermark_options
     *                                  width, height, font, margin, font-size, opacity
     *  @param          string          $type  text|image
     * 		
     * 	@return			object|bool		$this		Current object iteration.
     */
    function watermark($source, $watermark_options = array(), $type = 'text') {
        $watermark_types = array('text', 'image');
        $options = array('width', 'height', 'font', 'margin', 'font-size', 'opacity');
        if (!in_array($type, $watermark_types)) {
            return FALSE;
        }
        if ($type == 'text' && !is_string($source)) {
            return FALSE;
        } else if ($type == 'image' && get_class($source) != get_class($this)) {
            return FALSE;
        }
        foreach ($options as $option) {
            if (!isset($watermark_options[$option])) {
                switch ($option) {
                    case 'margin':
                        $watermark_options[$option] = 5;
                        break;
                    case 'font':
                        $watermark_options[$option] = 'arial.ttf';
                        break;
                    case 'font-size':
                        $watermark_options[$option] = '12';
                        break;
                    case 'width':
                        $watermark_options[$option] = '100';
                        break;
                    case 'height':
                        $watermark_options[$option] = '25';
                        break;
                    case 'opacity':
                        $watermark_options[$option] = '50';
                        break;
                }
            }
        }
        if ($type == 'image') {
            $extension = $source->get_extension();
            if ($source->get_width() > $this->get_width() || $source->get_height() > $this->get_height()) {
                /** resize watermark to fit the image */
                $source->resize($this->get_width(), $this->get_height(), 'width', false);
            }
            if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg') {
                $watermark_image = $source->get_copy();
            } else if (strtolower($extension) == 'png') {
                $watermark_image = $source->get_copy();
            } else if (strtolower($extension) == 'gif') {
                $watermark_image = $source->get_copy();
            }
            $watermark_width = imagesx($watermark_image);
            $watermark_height = imagesy($watermark_image);
        }
        $image = $this->get_copy();
        $black = imagecolorallocate($image, 0x00, 0x00, 0x00);
        $watermark_x = $this->get_width() - $watermark_width - $watermark_options['margin'];
        $watermark_y = $this->get_height() - $watermark_height - $watermark_options['margin'];
        switch ($type) {
            case 'text':
                imagepstext($image, $watermark_options['font-size'], 0, $watermark_x, $watermark_y, $black, $watermark_options['font'], $source);
                $watermark_width = $watermark_options['width'];
                $watermark_height = $watermark_options['height'];
                break;
            case 'image':
                $image = $this->imagecopymerge_alpha($image, $watermark_image, $watermark_x, $watermark_y, 0, 0, $watermark_width, $watermark_height, $watermark_options['opacity']);
                break;
        }
        $this->set_copy($image);
        return $this;
    }

    /**
     * @name 		    get_dominant_color()
     *  				Grabs and outputs the dominant color.
     * @author          Can Berkol
     * @since			1.0.0
     * 
     * @param 			string 		    $mode 		Color mode: rgb, hex, cmyk, hsv
     * 		
     * @return			mixed           $color       String if mode is hex, array otherwise.
     * 
     * @todo hsv, hex, cmyk
     */
    protected function get_dominant_color($mode = 'rgb') {
        $supported_modes = array('rgb', 'hex', 'cmyk', 'hsv');
        if (!in_array($mode, $supported_modes)) {
            $this->error_log[] = array('class' => get_class($this),
                'method' => 'set_permission',
                'error' => ExF0x001,
                'hint' => 'Check if the given permission is correct.',
                'time' => date('Y-m-d H:i'),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'exception' => ''
            );
            return FALSE;
        }
        if ($mode == 'rgb') {
            $iX = imagesx($this->copy);
            $iY = imagesy($this->copy);
            for ($x = 0; $x < $iX; $x++) {
                for ($y = 0; $iY; $y++) {
                    $rgb = imagecolorat($this->copy, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $rTotal += $r;
                    $gTotal += $g;
                    $bTotal += $b;
                    $total++;
                }
            }
            /**
             *  get the average values
             */
            $rgb['r'] = round($rTotal / $total);
            $rgb['g'] = round($gTotal / $total);
            $rgb['b'] = round($bTotal / $total);

            return $rgb;
        }
        return FALSE;
    }

    /**
     * @name 		    imagecopymerge_alpha()
     *  				A fix to get a function like imagecopymerge WITH ALPHA SUPPORT.
     *
     * @author          aiden, rodrigo
     * @since			1.2.4
     *
     * @param 			resource        $dst_im         Destination image.
     * @param           resource        $src_im         Source image.
     * @param           numeric         $dst_x          X coordinate of destination.
     * @param           numeric         $dst_y          Y coordinate of destination.
     * @param           numeric         $src_x          X coordinate of source.
     * @param           numeric         $src_y          Y coordinate of source.
     * @param           numeric         $src_w          Width of source.
     * @param           numeric         $src_h          Height of source
     * @param           numeric         $pct            Opacity (0 to 100).
     *
     * @return			void
     *
     * @link            http://php.net/manual/en/function.imagecopymerge.php
     */
    protected function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
        if (!isset($pct)) {
            return false;
        }
        $pct /= 100;
        // Get image width and height
        $w = imagesx($src_im);
        $h = imagesy($src_im);
        // Turn alpha blending off
        imagealphablending($src_im, false);
        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++)
            for ($y = 0; $y < $h; $y++) {
                $alpha = ( imagecolorat($src_im, $x, $y) >> 24 ) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        //loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                //get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ( $colorxy >> 24 ) & 0xFF;
                //calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
                } else {
                    $alpha += 127 * $pct;
                }
                //get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha);
                //set pixel with the new color + opacity
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }
        // The image copy
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);

        return $dst_im;
    }

}

/**
 * 
 * Change Log
 * **************************************
 * v1.0.6                     Can Berkol
 * 24.09.2012
 * **************************************
 * - delete_file() added.
 * 
 * **************************************
 * v1.0.5                     Can Berkol
 * 05.09.2012
 * **************************************
 * - Ability to set extension through constructor added.
 * 
 * **************************************
 * v1.0.4                     Can Berkol
 * 10.08.2012
 * **************************************
 * - BBR prefix added.
 * 
 * **************************************
 * v1.0.3                      Can Berkol
 * 10.08.2012
 * **************************************
 * - Error mechanism updated.
 * 
 * **************************************
 * v1.0.2                      Can Berkol
 * **************************************
 * - Added support for virtual files (on-the-fly file ceation).
 * - Added support for vector images.
 * - is_folder, is_directory functions added.
 * - is_file function added
 * - is_link, is_Shortcut functions added.
 * 
 * **************************************
 * v1.0.1                      Can Berkol
 * **************************************
 * - Now extends BBR_Library
 * 
 * **************************************
 * v1.0.0                      Can Berkol
 * **************************************
 * - check()
 * - close()
 * - exists()
 * - get_extension()
 * - get_registered_type()
 * - include_file()
 * - is_readable()
 * - is_writeable()
 * - load()
 * - open()
 * - select_type()
 * - set_permission ()
 * 
 */