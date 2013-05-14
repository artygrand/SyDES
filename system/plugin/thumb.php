<?php
class SimpleImage{
	public $image;
	public $image_type;
 
	public function load($filename) {
		if (!is_file($filename)){
			header("HTTP/1.0 404 Not Found");
			die;
		}
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		if($this->image_type == IMAGETYPE_JPEG ){
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->image_type == IMAGETYPE_GIF ){
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->image_type == IMAGETYPE_PNG ){
			$this->image = imagecreatefrompng($filename);
		}
	}
	public function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=0777) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		}
		if( $permissions != null) {
			chmod($filename,$permissions);
		}
	}
	public function getWidth() {
		return imagesx($this->image);
	}
	public function getHeight() {
		return imagesy($this->image);
	}
	public function needResize($width,$height){
		if($width / $this->getWidth() < $height / $this->getHeight()){
			$this->resizeToWidth($width);
		} else {
			$this->resizeToHeight($height);
		}
	}
	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}
	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height);
	}
	public function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
	}
	public function destroy() {
		imagedestroy($this->image);
	}
}

if (isset($_GET['src']) and $_GET['src']){
	$src_str = str_replace('/', '_', $_GET['src']);
	$cachedImg = 'system/cache/_' . $_GET['width'] . 'x' . $_GET['height'] . '_' . $src_str;
	if (!is_file($cachedImg)){
		$image = new SimpleImage();	
		$image->load($_GET['src']);
		$image->needResize((int)$_GET['width'], (int)$_GET['height']);
		$image->save($cachedImg);
		$image->destroy();
	}
	header('Location: /' . $cachedImg);
	die;
}
?>