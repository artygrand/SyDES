<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ThumbController{
	public $name = 'thumb';
	public static $front = array('index');

	public function index(){
		if (empty($_GET['src']) || empty($_GET['width']) || empty($_GET['height']) || empty($_GET['act'])){
			die;
		}

		$width = (int)$_GET['width'];
		$height = (int)$_GET['height'];
		$act = in_array($_GET['act'], array('r', 'c')) ? $_GET['act'] : 'c';
		$source = DIR_ROOT . $_GET['src'];
		$th_dir = DIR_THUMB . "{$width}_{$height}_{$act}/" . dirname($_GET['src']);
		$thumb = DIR_THUMB . "{$width}_{$height}_{$act}/{$_GET['src']}";

		if (count(glob(DIR_THUMB . '*_*_*/' . $_GET['src'])) > 4 || $height > 500 || $width > 500 || $height < 50 || $width < 50){
			header("HTTP/1.0 404 Not Found");
			die;
		}
		$image = new SimpleImage();
		$image->load($source);

		if ($image->width > $width || $image->height > $height){
			$image->doit($act, $width, $height);
		}

		if (!file_exists($th_dir)){
			mkdir($th_dir, 0777, true);
		}
		
		$image->save($thumb);
		$image->destroy();
		
		header('Location: ' . $_SERVER['REQUEST_URI']);
		die;
	}
}

class SimpleImage{
	public $image;
	public $image_type;
	public $width;
	public $height;

	public function load($filename){
		$image_info = @getimagesize($filename);
		if (!$image_info || !in_array($image_info[2], array(1,2,3)) || strpos($filename, 'upload/images') === false){
			header("HTTP/1.0 404 Not Found");
			die;
		}

		$this->image_type = $image_info[2];
		if ($this->image_type == IMAGETYPE_JPEG){
			$this->image = imagecreatefromjpeg($filename);
		} elseif ($this->image_type == IMAGETYPE_GIF){
			$this->image = imagecreatefromgif ($filename);
		} elseif ($this->image_type == IMAGETYPE_PNG){
			$this->image = imagecreatefrompng($filename);
		}
		
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=0777){
		if ($image_type == IMAGETYPE_JPEG){
			imagejpeg($this->image,$filename,$compression);
		} elseif ($image_type == IMAGETYPE_GIF){
			imagegif ($this->image,$filename);
		} elseif ($image_type == IMAGETYPE_PNG){
			imagepng($this->image,$filename);
		}
		if ($permissions != null){
			chmod($filename,$permissions);
		}
	}

	public function r($thumb_width, $thumb_height){
		if ($thumb_width / $this->width < $thumb_height / $this->height){
			$new_width = $thumb_width;
			$new_height = $this->height * ($thumb_width / $this->width);
		} else {
			$new_width = $this->width * ($thumb_height / $this->height);
			$new_height = $thumb_height;
		}
		$new_image = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
		$this->image = $new_image;
	}

	public function c($thumb_width, $thumb_height){
		if ($this->width / $this->height >= $thumb_width / $thumb_height){
			$new_width = $this->width / ($this->height / $thumb_height);
			$new_height = $thumb_height;
		} else {
			$new_width = $thumb_width;
			$new_height = $this->height / ($this->width / $thumb_width);
		}
		$new_image = imagecreatetruecolor($thumb_width, $thumb_height);
		imagecopyresampled($new_image, $this->image, 0 - ($new_width - $thumb_width) / 2, 0 - ($new_height - $thumb_height) / 2, 0, 0, $new_width, $new_height, $this->width, $this->height);
		$this->image = $new_image;
	}

	public function doit($what, $width, $height){
		$this -> $what($width, $height);
	}

	public function destroy(){
		imagedestroy($this->image);
	}
}